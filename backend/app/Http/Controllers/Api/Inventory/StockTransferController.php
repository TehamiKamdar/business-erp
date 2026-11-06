<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\StockTransferReceipt;
use App\Models\StockTransferReceiptItem;
use App\Models\Warehouse;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockTransferController extends Controller
{
    private function companyId()
    {
        return auth()->user()->branches()->value('company_id');
    }

    private function accessibleBranch($branchId)
    {
        return auth()->user()->branches()
            ->where('branches.id', $branchId)
            ->where('branches.company_id', $this->companyId())
            ->exists();
    }

    public function index(Request $request)
    {
        $query = StockTransfer::with([
            'sourceWarehouse',
            'destinationWarehouse',
            'creator',
            'dispatcher',
            'receiver',
            'items.product',
            'items.unit',
        ])
            ->whereHas('sourceWarehouse.branch', function ($q) {
                $q->where('company_id', $this->companyId())
                    ->whereIn(
                        'branches.id',
                        auth()->user()->branches()->select('branches.id')
                    );
            })
            ->whereHas('destinationWarehouse.branch', function ($q) {
                $q->where('company_id', $this->companyId())
                    ->whereIn(
                        'branches.id',
                        auth()->user()->branches()->select('branches.id')
                    );
            });

        // Warehouse filter
        if ($request->filled('warehouse_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('source_warehouse_id', $request->warehouse_id)
                    ->orWhere('destination_warehouse_id', $request->warehouse_id);
            });
        }

        // Source warehouse filter
        if ($request->filled('source_warehouse_id')) {
            $query->where('source_warehouse_id', $request->source_warehouse_id);
        }

        // Destination warehouse filter
        if ($request->filled('destination_warehouse_id')) {
            $query->where('destination_warehouse_id', $request->destination_warehouse_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Transfer number search
        if ($request->filled('search')) {
            $query->where(
                'transfer_number',
                'like',
                '%' . $request->search . '%'
            );
        }

        $transfers = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return response()->json([
            'message' => 'Stock transfers retrieved successfully.',
            'data' => $transfers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_warehouse_id' => ['required', 'uuid'],
            'destination_warehouse_id' => ['required', 'uuid', 'different:source_warehouse_id'],
            'transfer_number' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'distinct'],
            'items.*.unit_id' => ['required', 'uuid'],
            'items.*.requested_quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($validated) {

            /*
            |--------------------------------------------------------------------------
            | Validate Source Warehouse
            |--------------------------------------------------------------------------
            */

            $sourceWarehouse = Warehouse::whereHas(
                'branch',
                function ($query) {
                    $query->where('company_id', $this->companyId())
                        ->whereIn(
                            'branches.id',
                            auth()->user()->branches()->select('branches.id')
                        );
                }
            )
                ->where('status', 'active')
                ->findOrFail($validated['source_warehouse_id']);

            /*
            |--------------------------------------------------------------------------
            | Validate Destination Warehouse
            |--------------------------------------------------------------------------
            */

            $destinationWarehouse = Warehouse::whereHas(
                'branch',
                function ($query) {
                    $query->where('company_id', $this->companyId())
                        ->whereIn(
                            'branches.id',
                            auth()->user()->branches()->select('branches.id')
                        );
                }
            )
                ->where('status', 'active')
                ->findOrFail($validated['destination_warehouse_id']);

            if ($sourceWarehouse->id === $destinationWarehouse->id) {
                abort(422, 'Source and destination warehouses must be different.');
            }

            /*
            |--------------------------------------------------------------------------
            | Check Transfer Number
            |--------------------------------------------------------------------------
            */

            $exists = StockTransfer::where(
                'transfer_number',
                $validated['transfer_number']
            )->exists();

            if ($exists) {
                abort(422, 'Transfer number already exists.');
            }

            /*
            |--------------------------------------------------------------------------
            | Create Stock Transfer
            |--------------------------------------------------------------------------
            */

            $transfer = StockTransfer::create([
                'source_warehouse_id' => $sourceWarehouse->id,
                'destination_warehouse_id' => $destinationWarehouse->id,
                'transfer_number' => $validated['transfer_number'],
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create Transfer Items
            |--------------------------------------------------------------------------
            */

            foreach ($validated['items'] as $item) {

                $product = Product::where('company_id', $this->companyId())
                    ->where('status', 'active')
                    ->findOrFail($item['product_id']);

                $productUnit = DB::table('product_units')
                    ->where('product_id', $product->id)
                    ->where('unit_id', $item['unit_id'])
                    ->where('status', 'active')
                    ->first();

                if (!$productUnit) {
                    abort(422, "Selected unit is not available for product: {$product->name}.");
                }

                $conversionFactor = (float) $productUnit->conversion_factor;

                if ($conversionFactor <= 0) {
                    abort(422, "Invalid conversion factor for product: {$product->name}.");
                }

                $requestedQuantity = (float) $item['requested_quantity'];

                $baseQuantity = round(
                    $requestedQuantity * $conversionFactor,
                    8
                );

                $transferItem = StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $product->id,
                    'unit_id' => $productUnit->unit_id,
                    'requested_quantity' => $requestedQuantity,
                    'dispatched_quantity' => 0,
                    'received_quantity' => 0,
                    'conversion_factor' => $conversionFactor,
                    'base_quantity' => $baseQuantity,
                    'notes' => $item['notes'] ?? null,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Audit Transfer Item
                |--------------------------------------------------------------------------
                */

                app(AuditService::class)->log(
                    $transferItem,
                    'created',
                    null,
                    $transferItem->toArray(),
                    'Stock transfer item created.',
                    [
                        'transfer_id' => $transfer->id,
                    ],
                    auth()->user(),
                    $this->companyId(),
                    $sourceWarehouse->branch_id
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Audit Stock Transfer
            |--------------------------------------------------------------------------
            */

            app(AuditService::class)->log(
                $transfer,
                'created',
                null,
                $transfer->toArray(),
                'Stock transfer created.',
                [
                    'source_warehouse_id' => $sourceWarehouse->id,
                    'destination_warehouse_id' => $destinationWarehouse->id,
                    'items_count' => count($validated['items']),
                ],
                auth()->user(),
                $this->companyId(),
                $sourceWarehouse->branch_id
            );

            return response()->json([
                'message' => 'Stock transfer created successfully.',
                'data' => $transfer->fresh()->load([
                    'sourceWarehouse',
                    'destinationWarehouse',
                    'creator',
                    'items.product',
                    'items.unit',
                ]),
            ], 201);
        });
    }

    public function dispatch(string $id, AuditService $auditService)
    {
        $companyId = $this->companyId();

        $transfer = DB::transaction(function () use (
            $id,
            $companyId,
            $auditService
        ) {

            $transfer = StockTransfer::whereKey($id)
                ->whereHas('sourceWarehouse', function ($query) use ($companyId) {
                    $query->where('status', 'active')
                        ->whereHas('branch', function ($branchQuery) use ($companyId) {
                            $branchQuery
                                ->where('company_id', $companyId)
                                ->whereIn(
                                    'branches.id',
                                    auth()->user()->branches()->select('branches.id')
                                );
                        });
                })
                ->whereHas('destinationWarehouse', function ($query) use ($companyId) {
                    $query->where('status', 'active')
                        ->whereHas('branch', function ($branchQuery) use ($companyId) {
                            $branchQuery
                                ->where('company_id', $companyId)
                                ->whereIn(
                                    'branches.id',
                                    auth()->user()->branches()->select('branches.id')
                                );
                        });
                })
                ->lockForUpdate()
                ->firstOrFail();

            if ($transfer->status !== 'draft') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'status' => 'Only draft transfers can be dispatched.',
                ]);
            }

            $items = $transfer->items;

            if ($items->isEmpty()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'items' => 'Transfer must contain at least one item.',
                ]);
            }

            $sourceWarehouse = $transfer->sourceWarehouse()->firstOrFail();

            $sourceBranchId = $sourceWarehouse->branch_id;

            foreach ($items as $item) {

                $requiredQuantity = round(
                    (float) $item->base_quantity,
                    8
                );

                if ($requiredQuantity <= 0) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'items' => "Invalid base quantity for product {$item->product_id}.",
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Create Stock Balance If Missing
                |--------------------------------------------------------------------------
                */

                DB::table('stock_balances')->insertOrIgnore([
                    'id' => (string) Str::uuid7(),
                    'warehouse_id' => $transfer->source_warehouse_id,
                    'product_id' => $item->product_id,
                    'on_hand_quantity' => 0,
                    'reserved_quantity' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Lock Stock Balance
                |--------------------------------------------------------------------------
                */

                $balance = StockBalance::where(
                    'warehouse_id',
                    $transfer->source_warehouse_id
                )
                    ->where('product_id', $item->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $onHand = round(
                    (float) $balance->on_hand_quantity,
                    8
                );

                $reserved = round(
                    (float) $balance->reserved_quantity,
                    8
                );

                $available = round($onHand - $reserved, 8);

                /*
                |--------------------------------------------------------------------------
                | Check Available Stock
                |--------------------------------------------------------------------------
                */

                if ($available < $requiredQuantity) {

                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'stock' => [
                            "Insufficient stock for product {$item->product_id}. "
                            . "Available: {$available}, Required: {$requiredQuantity}."
                        ],
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Deduct Stock
                |--------------------------------------------------------------------------
                */

                $newBalance = round(
                    $onHand - $requiredQuantity,
                    8
                );

                $balance->update([
                    'on_hand_quantity' => $newBalance,
                ]);

                $oldBalanceValues = [
                    'on_hand_quantity' => $onHand,
                    'reserved_quantity' => $reserved,
                ];

                $newBalanceValues = [
                    'on_hand_quantity' => $newBalance,
                    'reserved_quantity' => $reserved,
                ];

                /*
                |--------------------------------------------------------------------------
                | Create Stock Movement
                |--------------------------------------------------------------------------
                */

                $movement = StockMovement::create([
                    'warehouse_id' => $transfer->source_warehouse_id,
                    'product_id' => $item->product_id,
                    'unit_id' => $item->unit_id,

                    'movement_type' => 'transfer',
                    'direction' => 'out',

                    'quantity' => $item->requested_quantity,
                    'conversion_factor' => $item->conversion_factor,
                    'base_quantity' => $requiredQuantity,

                    'balance_after' => $newBalance,

                    'reference_type' => $transfer->getMorphClass(),
                    'reference_id' => $transfer->id,

                    'notes' => $item->notes,

                    'created_by' => auth()->id(),
                    'created_at' => now(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Update Transfer Item
                |--------------------------------------------------------------------------
                */

                $item->update([
                    'dispatched_quantity' => $item->requested_quantity,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Audit Logs
                |--------------------------------------------------------------------------
                */

                $auditService->log(
                    $balance,
                    'updated',
                    $oldBalanceValues,
                    $newBalanceValues,
                    'Stock deducted during transfer dispatch.',
                    ['stock_transfer_id' => $transfer->id],
                    null,
                    $companyId,
                    $sourceBranchId
                );

                $auditService->log(
                    $movement,
                    'created',
                    null,
                    $movement->getAttributes(),
                    'Stock transfer dispatch movement created.',
                    ['stock_transfer_id' => $transfer->id],
                    null,
                    $companyId,
                    $sourceBranchId
                );

                $auditService->log(
                    $item,
                    'dispatched',
                    ['dispatched_quantity' => 0],
                    ['dispatched_quantity' => $item->dispatched_quantity],
                    'Stock transfer item dispatched.',
                    ['stock_transfer_id' => $transfer->id],
                    null,
                    $companyId,
                    $sourceBranchId
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Update Transfer Status
            |--------------------------------------------------------------------------
            */

            $oldTransferValues = [
                'status' => $transfer->status,
            ];

            $transfer->update([
                'status' => 'dispatched',
                'dispatched_by' => auth()->id(),
                'dispatched_at' => now(),
            ]);

            $auditService->log(
                $transfer,
                'dispatched',
                $oldTransferValues,
                [
                    'status' => 'dispatched',
                    'dispatched_by' => auth()->id(),
                    'dispatched_at' => $transfer->dispatched_at,
                ],
                'Stock transfer dispatched successfully.',
                [],
                null,
                $companyId,
                $sourceBranchId
            );

            return $transfer;
        });

        return response()->json([
            'message' => 'Stock transfer dispatched successfully.',
            'data' => $transfer->load([
                'sourceWarehouse',
                'destinationWarehouse',
                'items.product',
                'items.unit',
                'dispatcher',
            ]),
        ]);
    }

    public function receive(string $id, Request $request, AuditService $auditService)
    {
        $validated = $request->validate([
            'receipt_number' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],

            'items.*.stock_transfer_item_id' => [
                'required',
                'uuid',
                'distinct',
            ],

            'items.*.received_quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'items.*.notes' => [
                'nullable',
                'string',
            ],
        ]);

        $companyId = $this->companyId();

        $receipt = DB::transaction(function () use (
            $id,
            $validated,
            $companyId,
            $auditService
        ) {

            /*
            |--------------------------------------------------------------------------
            | Find And Lock Transfer
            |--------------------------------------------------------------------------
            */

            $transfer = StockTransfer::whereKey($id)
                ->whereHas('sourceWarehouse', function ($query) use ($companyId) {
                    $query->where('status', 'active')
                        ->whereHas('branch', function ($branchQuery) use ($companyId) {
                            $branchQuery
                                ->where('company_id', $companyId)
                                ->whereIn(
                                    'branches.id',
                                    auth()->user()->branches()->select('branches.id')
                                );
                        });
                })
                ->whereHas('destinationWarehouse', function ($query) use ($companyId) {
                    $query->where('status', 'active')
                        ->whereHas('branch', function ($branchQuery) use ($companyId) {
                            $branchQuery
                                ->where('company_id', $companyId)
                                ->whereIn(
                                    'branches.id',
                                    auth()->user()->branches()->select('branches.id')
                                );
                        });
                })
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($transfer->status, ['dispatched', 'partially_received'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'status' => 'Only dispatched or partially received transfers can be received.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Check Duplicate Receipt Number
            |--------------------------------------------------------------------------
            */

            $receiptExists = $transfer->receipts()
                ->where('receipt_number', $validated['receipt_number'])
                ->exists();

            if ($receiptExists) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'receipt_number' => 'This receipt number already exists for this transfer.',
                ]);
            }

            $items = $transfer->items()->get();

            if ($items->isEmpty()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'items' => 'This transfer has no items.',
                ]);
            }

            $destinationWarehouse = $transfer->destinationWarehouse;
            $destinationBranchId = $destinationWarehouse->branch_id;

            /*
            |--------------------------------------------------------------------------
            | Create Receipt Header
            |--------------------------------------------------------------------------
            */

            $receipt = StockTransferReceipt::create([
                'stock_transfer_id' => $transfer->id,
                'receipt_number' => $validated['receipt_number'],
                'received_at' => now(),
                'received_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            $auditService->log(
                $receipt,
                'created',
                null,
                $receipt->getAttributes(),
                'Stock transfer receipt created.',
                ['stock_transfer_id' => $transfer->id],
                null,
                $companyId,
                $destinationBranchId
            );

            /*
            |--------------------------------------------------------------------------
            | Process Receipt Items
            |--------------------------------------------------------------------------
            */

            foreach ($validated['items'] as $inputItem) {

                $transferItem = StockTransferItem::where(
                    'stock_transfer_id',
                    $transfer->id
                )
                    ->whereKey($inputItem['stock_transfer_item_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$transferItem) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'items' => 'One or more transfer items are invalid.',
                    ]);
                }

                $receivedQuantity = round(
                    (float) $inputItem['received_quantity'],
                    8
                );

                $dispatchedQuantity = round(
                    (float) $transferItem->dispatched_quantity,
                    8
                );

                $alreadyReceived = round(
                    (float) $transferItem->received_quantity,
                    8
                );

                $remainingQuantity = round(
                    $dispatchedQuantity - $alreadyReceived,
                    8
                );

                /*
                |--------------------------------------------------------------------------
                | Validate Remaining Quantity
                |--------------------------------------------------------------------------
                */

                if ($receivedQuantity > $remainingQuantity) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'items' => [
                            "Product {$transferItem->product_id}: "
                            . "Remaining quantity is {$remainingQuantity}, "
                            . "but you entered {$receivedQuantity}."
                        ],
                    ]);
                }

                $conversionFactor = round(
                    (float) $transferItem->conversion_factor,
                    8
                );

                if ($conversionFactor <= 0) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'items' => "Invalid conversion factor for product {$transferItem->product_id}.",
                    ]);
                }

                $baseQuantity = round(
                    $receivedQuantity * $conversionFactor,
                    8
                );

                /*
                |--------------------------------------------------------------------------
                | Create Destination Stock Balance If Missing
                |--------------------------------------------------------------------------
                */

                DB::table('stock_balances')->insertOrIgnore([
                    'id' => (string) Str::uuid(),
                    'warehouse_id' => $transfer->destination_warehouse_id,
                    'product_id' => $transferItem->product_id,
                    'on_hand_quantity' => 0,
                    'reserved_quantity' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Lock Destination Stock Balance
                |--------------------------------------------------------------------------
                */

                $balance = StockBalance::where(
                    'warehouse_id',
                    $transfer->destination_warehouse_id
                )
                    ->where('product_id', $transferItem->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $oldOnHand = round(
                    (float) $balance->on_hand_quantity,
                    8
                );

                $reserved = round(
                    (float) $balance->reserved_quantity,
                    8
                );

                $newOnHand = round(
                    $oldOnHand + $baseQuantity,
                    8
                );

                /*
                |--------------------------------------------------------------------------
                | Update Destination Stock
                |--------------------------------------------------------------------------
                */

                $balance->update([
                    'on_hand_quantity' => $newOnHand,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Create Receipt Item History
                |--------------------------------------------------------------------------
                */

                $receiptItem = StockTransferReceiptItem::create([
                    'stock_transfer_receipt_id' => $receipt->id,
                    'stock_transfer_item_id' => $transferItem->id,
                    'product_id' => $transferItem->product_id,
                    'unit_id' => $transferItem->unit_id,
                    'received_quantity' => $receivedQuantity,
                    'conversion_factor' => $conversionFactor,
                    'base_quantity' => $baseQuantity,
                    'notes' => $inputItem['notes'] ?? null,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Update Cumulative Received Quantity
                |--------------------------------------------------------------------------
                */

                $newReceivedQuantity = round(
                    $alreadyReceived + $receivedQuantity,
                    8
                );

                $transferItem->update([
                    'received_quantity' => $newReceivedQuantity,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Create Stock Movement
                |--------------------------------------------------------------------------
                */

                $movement = StockMovement::create([
                    'warehouse_id' => $transfer->destination_warehouse_id,
                    'product_id' => $transferItem->product_id,
                    'unit_id' => $transferItem->unit_id,

                    'movement_type' => 'transfer',
                    'direction' => 'in',

                    'quantity' => $receivedQuantity,
                    'conversion_factor' => $conversionFactor,
                    'base_quantity' => $baseQuantity,

                    'balance_after' => $newOnHand,

                    'reference_type' => $receipt->getMorphClass(),
                    'reference_id' => $receipt->id,

                    'notes' => $inputItem['notes'] ?? $validated['notes'] ?? null,

                    'created_by' => auth()->id(),
                    'created_at' => now(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Audit Stock Balance
                |--------------------------------------------------------------------------
                */

                $auditService->log(
                    $balance,
                    'updated',
                    [
                        'on_hand_quantity' => $oldOnHand,
                        'reserved_quantity' => $reserved,
                    ],
                    [
                        'on_hand_quantity' => $newOnHand,
                        'reserved_quantity' => $reserved,
                    ],
                    'Destination stock increased through transfer receipt.',
                    ['stock_transfer_id' => $transfer->id],
                    null,
                    $companyId,
                    $destinationBranchId
                );

                /*
                |--------------------------------------------------------------------------
                | Audit Receipt Item
                |--------------------------------------------------------------------------
                */

                $auditService->log(
                    $receiptItem,
                    'created',
                    null,
                    $receiptItem->getAttributes(),
                    'Stock transfer receipt item created.',
                    ['stock_transfer_id' => $transfer->id],
                    null,
                    $companyId,
                    $destinationBranchId
                );

                /*
                |--------------------------------------------------------------------------
                | Audit Transfer Item
                |--------------------------------------------------------------------------
                */

                $auditService->log(
                    $transferItem,
                    'received',
                    [
                        'received_quantity' => $alreadyReceived,
                    ],
                    [
                        'received_quantity' => $newReceivedQuantity,
                    ],
                    'Stock transfer item received.',
                    [
                        'stock_transfer_id' => $transfer->id,
                        'receipt_id' => $receipt->id,
                    ],
                    null,
                    $companyId,
                    $destinationBranchId
                );

                /*
                |--------------------------------------------------------------------------
                | Audit Stock Movement
                |--------------------------------------------------------------------------
                */

                $auditService->log(
                    $movement,
                    'created',
                    null,
                    $movement->getAttributes(),
                    'Destination stock transfer movement created.',
                    [
                        'stock_transfer_id' => $transfer->id,
                        'receipt_id' => $receipt->id,
                    ],
                    null,
                    $companyId,
                    $destinationBranchId
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Determine Transfer Status
            |--------------------------------------------------------------------------
            */

            $allItems = $transfer->items()->get();

            $allReceived = $allItems->every(function ($item) {
                return round((float) $item->received_quantity, 8)
                    >= round((float) $item->dispatched_quantity, 8);
            });

            $oldStatus = $transfer->status;

            $newStatus = $allReceived
                ? 'received'
                : 'partially_received';

            $transfer->update([
                'status' => $newStatus,
                'received_by' => $allReceived ? auth()->id() : $transfer->received_by,
                'received_at' => $allReceived ? now() : $transfer->received_at,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Audit Transfer Status
            |--------------------------------------------------------------------------
            */

            $auditService->log(
                $transfer,
                'received',
                [
                    'status' => $oldStatus,
                ],
                [
                    'status' => $newStatus,
                    'received_by' => $transfer->received_by,
                    'received_at' => $transfer->received_at,
                ],
                $allReceived
                    ? 'Stock transfer fully received.'
                    : 'Stock transfer partially received.',
                ['receipt_id' => $receipt->id],
                null,
                $companyId,
                $destinationBranchId
            );

            return $receipt;
        });

        return response()->json([
            'message' => 'Stock transfer receipt recorded successfully.',
            'data' => $receipt->load([
                'receiver',
                'items.product',
                'items.unit',
                'items.stockTransferItem',
                'stockTransfer',
            ]),
        ]);
    }

    public function cancel(string $id, Request $request, AuditService $auditService)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $companyId = $this->companyId();

        $transfer = DB::transaction(function () use (
            $id,
            $validated,
            $companyId,
            $auditService
        ) {

            $transfer = StockTransfer::whereKey($id)
                ->whereHas('sourceWarehouse', function ($query) use ($companyId) {
                    $query->whereHas('branch', function ($branchQuery) use ($companyId) {
                        $branchQuery
                            ->where('company_id', $companyId)
                            ->whereIn(
                                'branches.id',
                                auth()->user()->branches()->select('branches.id')
                            );
                    });
                })
                ->whereHas('destinationWarehouse', function ($query) use ($companyId) {
                    $query->whereHas('branch', function ($branchQuery) use ($companyId) {
                        $branchQuery
                            ->where('company_id', $companyId)
                            ->whereIn(
                                'branches.id',
                                auth()->user()->branches()->select('branches.id')
                            );
                    });
                })
                ->lockForUpdate()
                ->firstOrFail();

            if ($transfer->status !== 'draft') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'status' => 'Only draft stock transfers can be cancelled.',
                ]);
            }

            $oldValues = [
                'status' => $transfer->status,
                'notes' => $transfer->notes,
            ];

            $transfer->update([
                'status' => 'cancelled',
                'notes' => trim(
                    ($transfer->notes ? $transfer->notes . "\n\n" : '')
                    . 'Cancellation reason: '
                    . $validated['reason']
                ),
            ]);

            $auditService->log(
                $transfer,
                'cancelled',
                $oldValues,
                [
                    'status' => $transfer->status,
                    'notes' => $transfer->notes,
                ],
                'Stock transfer cancelled. Reason: ' . $validated['reason'],
                [],
                null,
                $companyId,
                $transfer->sourceWarehouse->branch_id
            );

            return $transfer;
        });

        return response()->json([
            'message' => 'Stock transfer cancelled successfully.',
            'data' => $transfer->load([
                'sourceWarehouse',
                'destinationWarehouse',
                'items.product',
                'items.unit',
            ]),
        ]);
    }

    public function show(string $id)
    {
        $companyId = $this->companyId();

        $transfer = StockTransfer::whereKey($id)

            ->whereHas('sourceWarehouse', function ($query) use ($companyId) {
                $query->whereHas('branch', function ($branchQuery) use ($companyId) {
                    $branchQuery
                        ->where('company_id', $companyId)
                        ->whereIn(
                            'branches.id',
                            auth()->user()->branches()->select('branches.id')
                        );
                });
            })

            ->whereHas('destinationWarehouse', function ($query) use ($companyId) {
                $query->whereHas('branch', function ($branchQuery) use ($companyId) {
                    $branchQuery
                        ->where('company_id', $companyId)
                        ->whereIn(
                            'branches.id',
                            auth()->user()->branches()->select('branches.id')
                        );
                });
            })

            ->with([
                'sourceWarehouse',
                'destinationWarehouse',

                'creator',
                'dispatcher',
                'receiver',

                'items.product',
                'items.unit',

                'receipts' => function ($query) {
                    $query->orderBy('received_at');
                },

                'receipts.receiver',
                'receipts.items.product',
                'receipts.items.unit',
                'receipts.items.stockTransferItem',
            ])

            ->firstOrFail();

        return response()->json([
            'message' => 'Stock transfer details retrieved successfully.',
            'data' => $transfer,
        ]);
    }
}
