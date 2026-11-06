<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockAdjustmentController extends Controller
{
    private function companyId()
    {
        return auth()->user()->branches()->value('company_id');
    }

    public function index(Request $request)
    {
        $query = StockAdjustment::with([
            'warehouse',
            'creator',
            'approver',
        ])
            ->whereHas('warehouse.branch', function ($q) {
                $q->where('company_id', $this->companyId())
                    ->whereIn(
                        'branches.id',
                        auth()->user()->branches()->select('branches.id')
                    );
            })
            ->withCount('items');

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(
                'adjustment_number',
                'like',
                '%' . $request->search . '%'
            );
        }

        $adjustments = $query
            ->latest()
            ->paginate(15);

        return response()->json($adjustments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => ['required', 'uuid'],
            'adjustment_number' => ['required', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'distinct'],
            'items.*.counted_quantity' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($validated) {

            $warehouse = Warehouse::whereHas('branch', function ($query) {
                $query->where('company_id', $this->companyId());
            })
                ->whereIn(
                    'branch_id',
                    auth()->user()->branches()->select('branches.id')
                )
                ->lockForUpdate()
                ->findOrFail($validated['warehouse_id']);

            $exists = StockAdjustment::where('warehouse_id', $warehouse->id)
                ->where('adjustment_number', $validated['adjustment_number'])
                ->exists();

            if ($exists) {
                abort(422, 'Adjustment number already exists for this warehouse.');
            }

            $adjustment = StockAdjustment::create([
                'warehouse_id' => $warehouse->id,
                'adjustment_number' => $validated['adjustment_number'],
                'reason' => $validated['reason'] ?? null,
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {

                $product = Product::where('company_id', $this->companyId())
                    ->where('status', 'active')
                    ->findOrFail($item['product_id']);

                $baseUnit = DB::table('product_units')
                    ->where('product_id', $product->id)
                    ->where('is_base_unit', true)
                    ->where('status', 'active')
                    ->first();

                if (!$baseUnit) {
                    abort(422, "Base unit not found for product: {$product->name}");
                }

                $stockBalance = StockBalance::where('warehouse_id', $warehouse->id)
                    ->where('product_id', $product->id)
                    ->first();

                $systemQuantity = (float) ($stockBalance?->on_hand_quantity ?? 0);

                $countedQuantity = (float) $item['counted_quantity'];

                $adjustment->items()->create([
                    'product_id' => $product->id,
                    'unit_id' => $baseUnit->unit_id,
                    'system_quantity' => $systemQuantity,
                    'counted_quantity' => $countedQuantity,
                    'difference_quantity' => $countedQuantity - $systemQuantity,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return response()->json([
                'message' => 'Stock adjustment created successfully.',
                'data' => $adjustment->load([
                    'warehouse',
                    'creator',
                    'items.product',
                    'items.unit',
                ]),
            ], 201);
        });
    }

    public function show($id)
    {
        $adjustment = StockAdjustment::with([
            'warehouse',
            'creator',
            'approver',
            'items.product',
            'items.unit',
        ])
            ->whereHas('warehouse.branch', function ($query) {
                $query->where('company_id', $this->companyId())
                    ->whereIn(
                        'branches.id',
                        auth()->user()->branches()->select('branches.id')
                    );
            })
            ->findOrFail($id);

        return response()->json([
            'data' => $adjustment,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'adjustment_number' => ['sometimes', 'required', 'string', 'max:255'],
            'reason' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string'],

            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'distinct'],
            'items.*.counted_quantity' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['sometimes', 'nullable', 'string'],
        ]);

        return DB::transaction(function () use ($validated, $id) {

            $adjustment = StockAdjustment::whereHas(
                'warehouse.branch',
                function ($query) {
                    $query->where('company_id', $this->companyId())
                        ->whereIn(
                            'branches.id',
                            auth()->user()->branches()->select('branches.id')
                        );
                }
            )
                ->lockForUpdate()
                ->findOrFail($id);

            if ($adjustment->status !== 'draft') {
                abort(422, 'Only draft stock adjustments can be updated.');
            }

            $warehouse = $adjustment->warehouse()->firstOrFail();

            $audit = app(AuditService::class);

            $companyId = $this->companyId();
            $branchId = $warehouse->branch_id;

            // Update adjustment header

            $oldHeader = [
                'adjustment_number' => $adjustment->adjustment_number,
                'reason' => $adjustment->reason,
                'notes' => $adjustment->notes,
            ];

            if (isset($validated['adjustment_number'])) {

                $exists = StockAdjustment::where('warehouse_id', $warehouse->id)
                    ->where('adjustment_number', $validated['adjustment_number'])
                    ->where('id', '!=', $adjustment->id)
                    ->exists();

                if ($exists) {
                    abort(422, 'Adjustment number already exists for this warehouse.');
                }

                $adjustment->adjustment_number = $validated['adjustment_number'];
            }

            if (array_key_exists('reason', $validated)) {
                $adjustment->reason = $validated['reason'];
            }

            if (array_key_exists('notes', $validated)) {
                $adjustment->notes = $validated['notes'];
            }

            $newHeader = [
                'adjustment_number' => $adjustment->adjustment_number,
                'reason' => $adjustment->reason,
                'notes' => $adjustment->notes,
            ];

            if ($oldHeader !== $newHeader) {

                $adjustment->save();

                $audit->log(
                    $adjustment,
                    'updated',
                    $oldHeader,
                    $newHeader,
                    'Stock adjustment header updated.',
                    [],
                    auth()->user(),
                    $companyId,
                    $branchId
                );
            }

            // Update or add individual items

            if (isset($validated['items'])) {

                foreach ($validated['items'] as $item) {

                    $product = Product::where('company_id', $companyId)
                        ->where('status', 'active')
                        ->findOrFail($item['product_id']);

                    $existingItem = $adjustment->items()
                        ->where('product_id', $product->id)
                        ->lockForUpdate()
                        ->first();

                    if ($existingItem) {

                        $oldValues = [
                            'counted_quantity' => $existingItem->counted_quantity,
                            'difference_quantity' => $existingItem->difference_quantity,
                            'notes' => $existingItem->notes,
                        ];

                        $existingItem->counted_quantity = $item['counted_quantity'];

                        $existingItem->difference_quantity =
                            (float) $item['counted_quantity']
                            - (float) $existingItem->system_quantity;

                        if (array_key_exists('notes', $item)) {
                            $existingItem->notes = $item['notes'];
                        }

                        $existingItem->save();

                        $newValues = [
                            'counted_quantity' => $existingItem->counted_quantity,
                            'difference_quantity' => $existingItem->difference_quantity,
                            'notes' => $existingItem->notes,
                        ];

                        if ($oldValues !== $newValues) {
                            $audit->log(
                                $existingItem,
                                'updated',
                                $oldValues,
                                $newValues,
                                'Stock adjustment item updated.',
                                ['stock_adjustment_id' => $adjustment->id],
                                auth()->user(),
                                $companyId,
                                $branchId
                            );
                        }

                    } else {

                        $baseUnit = DB::table('product_units')
                            ->where('product_id', $product->id)
                            ->where('is_base_unit', true)
                            ->where('status', 'active')
                            ->first();

                        if (!$baseUnit) {
                            abort(422, "Base unit not found for product: {$product->name}");
                        }

                        $stockBalance = StockBalance::where(
                            'warehouse_id',
                            $warehouse->id
                        )
                            ->where('product_id', $product->id)
                            ->first();

                        $systemQuantity = (float) (
                            $stockBalance?->on_hand_quantity ?? 0
                        );

                        $countedQuantity = (float) $item['counted_quantity'];

                        $newItem = $adjustment->items()->create([
                            'product_id' => $product->id,
                            'unit_id' => $baseUnit->unit_id,
                            'system_quantity' => $systemQuantity,
                            'counted_quantity' => $countedQuantity,
                            'difference_quantity' => $countedQuantity - $systemQuantity,
                            'notes' => $item['notes'] ?? null,
                        ]);

                        $audit->log(
                            $newItem,
                            'created',
                            null,
                            $newItem->only([
                                'product_id',
                                'unit_id',
                                'system_quantity',
                                'counted_quantity',
                                'difference_quantity',
                                'notes',
                            ]),
                            'New product added to stock adjustment.',
                            ['stock_adjustment_id' => $adjustment->id],
                            auth()->user(),
                            $companyId,
                            $branchId
                        );
                    }
                }
            }

            return response()->json([
                'message' => 'Stock adjustment updated successfully.',
                'data' => $adjustment->fresh()->load([
                    'warehouse',
                    'creator',
                    'approver',
                    'items.product',
                    'items.unit',
                ]),
            ]);
        });
    }

    public function approve($id)
    {
        return DB::transaction(function () use ($id) {

            $adjustment = StockAdjustment::whereHas(
                'warehouse.branch',
                function ($query) {
                    $query->where('company_id', $this->companyId())
                        ->whereIn(
                            'branches.id',
                            auth()->user()->branches()->select('branches.id')
                        );
                }
            )
                ->lockForUpdate()
                ->findOrFail($id);

            if ($adjustment->status !== 'draft') {
                abort(422, 'Only draft stock adjustments can be approved.');
            }

            if (!$adjustment->items()->exists()) {
                abort(422, 'Cannot approve an adjustment without items.');
            }

            $oldValues = [
                'status' => $adjustment->status,
                'approved_by' => $adjustment->approved_by,
            ];

            $adjustment->status = 'approved';
            $adjustment->approved_by = auth()->id();
            $adjustment->save();

            app(AuditService::class)->log(
                $adjustment,
                'approved',
                $oldValues,
                [
                    'status' => $adjustment->status,
                    'approved_by' => $adjustment->approved_by,
                ],
                'Stock adjustment approved.',
                [],
                auth()->user(),
                $this->companyId(),
                $adjustment->warehouse->branch_id
            );

            return response()->json([
                'message' => 'Stock adjustment approved successfully.',
                'data' => $adjustment->fresh()->load([
                    'warehouse',
                    'creator',
                    'approver',
                    'items.product',
                    'items.unit',
                ]),
            ]);
        });
    }

    public function post($id)
    {
        return DB::transaction(function () use ($id) {

            $adjustment = StockAdjustment::whereHas(
                'warehouse.branch',
                function ($query) {
                    $query->where('company_id', $this->companyId())
                        ->whereIn(
                            'branches.id',
                            auth()->user()->branches()->select('branches.id')
                        );
                }
            )
                ->lockForUpdate()
                ->findOrFail($id);

            if ($adjustment->status !== 'approved') {
                abort(422, 'Only approved stock adjustments can be posted.');
            }

            $adjustment->load([
                'warehouse',
                'items.product',
                'items.unit',
            ]);

            if ($adjustment->items->isEmpty()) {
                abort(422, 'Cannot post an adjustment without items.');
            }

            $companyId = $this->companyId();
            $branchId = $adjustment->warehouse->branch_id;

            foreach ($adjustment->items as $item) {

                /*
                |--------------------------------------------------------------------------
                | Ensure Stock Balance Row Exists
                |--------------------------------------------------------------------------
                */

                DB::table('stock_balances')->insertOrIgnore([
                    'id' => (string) Str::uuid7(),
                    'warehouse_id' => $adjustment->warehouse_id,
                    'product_id' => $item->product_id,
                    'on_hand_quantity' => 0,
                    'reserved_quantity' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Lock Current Stock Balance
                |--------------------------------------------------------------------------
                */

                $stockBalance = StockBalance::where(
                    'warehouse_id',
                    $adjustment->warehouse_id
                )
                    ->where('product_id', $item->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $currentQuantity = (float) $stockBalance->on_hand_quantity;
                $systemQuantity = (float) $item->system_quantity;
                $countedQuantity = (float) $item->counted_quantity;

                /*
                |--------------------------------------------------------------------------
                | Validate Original System Quantity
                |--------------------------------------------------------------------------
                */

                if (
                    round($currentQuantity, 8) !==
                    round($systemQuantity, 8)
                ) {
                    abort(
                        422,
                        "Stock has changed for product: {$item->product->name}. Please review and create a new adjustment."
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Calculate Difference
                |--------------------------------------------------------------------------
                */

                $difference = round(
                    $countedQuantity - $systemQuantity,
                    8
                );

                if (
                    round((float) $item->difference_quantity, 8) !== $difference
                ) {
                    abort(
                        422,
                        "Adjustment difference is invalid for product: {$item->product->name}."
                    );
                }

                // No stock change required.
                if ($difference == 0) {
                    continue;
                }

                $oldBalance = [
                    'on_hand_quantity' => $stockBalance->on_hand_quantity,
                    'reserved_quantity' => $stockBalance->reserved_quantity,
                ];

                /*
                |--------------------------------------------------------------------------
                | Update Stock Balance
                |--------------------------------------------------------------------------
                */

                $stockBalance->on_hand_quantity = $countedQuantity;
                $stockBalance->save();

                /*
                |--------------------------------------------------------------------------
                | Create Stock Movement
                |--------------------------------------------------------------------------
                */

                $movement = new StockMovement();

                $movement->forceFill([
                    'warehouse_id' => $adjustment->warehouse_id,
                    'product_id' => $item->product_id,
                    'unit_id' => $item->unit_id,
                    'movement_type' => 'adjustment',
                    'direction' => $difference > 0 ? 'in' : 'out',
                    'quantity' => abs($difference),
                    'conversion_factor' => 1,
                    'base_quantity' => abs($difference),
                    'balance_after' => $countedQuantity,
                    'reference_type' => $adjustment->getMorphClass(),
                    'reference_id' => $adjustment->id,
                    'notes' => $item->notes ?? $adjustment->reason,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                ]);

                $movement->save();

                /*
                |--------------------------------------------------------------------------
                | Audit Stock Balance
                |--------------------------------------------------------------------------
                */

                app(AuditService::class)->log(
                    $stockBalance,
                    'updated',
                    $oldBalance,
                    [
                        'on_hand_quantity' => $stockBalance->on_hand_quantity,
                        'reserved_quantity' => $stockBalance->reserved_quantity,
                    ],
                    'Stock balance updated through stock adjustment.',
                    [
                        'adjustment_id' => $adjustment->id,
                        'difference_quantity' => $difference,
                    ],
                    auth()->user(),
                    $companyId,
                    $branchId
                );

                /*
                |--------------------------------------------------------------------------
                | Audit Stock Movement
                |--------------------------------------------------------------------------
                */

                app(AuditService::class)->log(
                    $movement,
                    'created',
                    null,
                    [
                        'movement_type' => $movement->movement_type,
                        'direction' => $movement->direction,
                        'quantity' => $movement->quantity,
                        'base_quantity' => $movement->base_quantity,
                        'balance_after' => $movement->balance_after,
                        'reference_type' => $movement->reference_type,
                        'reference_id' => $movement->reference_id,
                    ],
                    'Stock adjustment movement created.',
                    [
                        'adjustment_id' => $adjustment->id,
                    ],
                    auth()->user(),
                    $companyId,
                    $branchId
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Mark Adjustment as Posted
            |--------------------------------------------------------------------------
            */

            $oldValues = [
                'status' => $adjustment->status,
                'posted_at' => $adjustment->posted_at,
            ];

            $adjustment->status = 'posted';
            $adjustment->posted_at = now();
            $adjustment->save();

            /*
            |--------------------------------------------------------------------------
            | Audit Stock Adjustment
            |--------------------------------------------------------------------------
            */

            app(AuditService::class)->log(
                $adjustment,
                'posted',
                $oldValues,
                [
                    'status' => $adjustment->status,
                    'posted_at' => $adjustment->posted_at,
                ],
                'Stock adjustment posted successfully.',
                [
                    'items_count' => $adjustment->items->count(),
                ],
                auth()->user(),
                $companyId,
                $branchId
            );

            return response()->json([
                'message' => 'Stock adjustment posted successfully.',
                'data' => $adjustment->fresh()->load([
                    'warehouse',
                    'creator',
                    'approver',
                    'items.product',
                    'items.unit',
                ]),
            ]);
        });
    }

    public function cancel($id)
    {
        return DB::transaction(function () use ($id) {

            $adjustment = StockAdjustment::whereHas(
                'warehouse.branch',
                function ($query) {
                    $query->where('company_id', $this->companyId())
                        ->whereIn(
                            'branches.id',
                            auth()->user()->branches()->select('branches.id')
                        );
                }
            )
                ->lockForUpdate()
                ->findOrFail($id);

            if (!in_array($adjustment->status, ['draft', 'approved'])) {
                abort(422, 'Only draft or approved stock adjustments can be cancelled.');
            }

            $oldValues = [
                'status' => $adjustment->status,
            ];

            $adjustment->status = 'cancelled';
            $adjustment->save();

            app(AuditService::class)->log(
                $adjustment,
                'cancelled',
                $oldValues,
                [
                    'status' => $adjustment->status,
                ],
                'Stock adjustment cancelled.',
                [],
                auth()->user(),
                $this->companyId(),
                $adjustment->warehouse->branch_id
            );

            return response()->json([
                'message' => 'Stock adjustment cancelled successfully.',
                'data' => $adjustment->fresh()->load([
                    'warehouse',
                    'creator',
                    'approver',
                    'items.product',
                    'items.unit',
                ]),
            ]);
        });
    }
}
