<?php

namespace App\Http\Controllers\Api\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GoodsReceiptController extends Controller
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
        $query = GoodsReceipt::whereHas('purchaseOrder.branch', function ($q) {
            $q->where('company_id', $this->companyId());
        })
            ->whereHas('purchaseOrder', function ($q) {
                $q->whereIn('branch_id', auth()->user()->branches()->select('branches.id'));
            })
            ->with([
                'purchaseOrder:id,order_number,supplier_id,branch_id',
                'purchaseOrder.supplier:id,name',
                'warehouse:id,name',
                'receiver:id,name',
                'items',
            ]);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('purchase_order_id')) {
            $query->where('purchase_order_id', $request->purchase_order_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('receipt_number', 'like', '%' . $request->search . '%');
        }

        $goodsReceipts = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return response()->json([
            'message' => 'Goods receipts retrieved successfully.',
            'data' => $goodsReceipts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => [
                'required',
                'uuid',
                'exists:purchase_orders,id',
            ],

            'warehouse_id' => [
                'required',
                'uuid',
                'exists:warehouses,id',
            ],

            'receipt_number' => [
                'required',
                'string',
                'max:100',
            ],

            'receipt_date' => [
                'required',
                'date',
            ],

            'supplier_delivery_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.purchase_order_item_id' => [
                'required',
                'uuid',
                'distinct',
                'exists:purchase_order_items,id',
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

        return DB::transaction(function () use ($validated) {

            $purchaseOrder = PurchaseOrder::whereHas('branch', function ($query) {
                $query->where('company_id', $this->companyId());
            })
                ->lockForUpdate()
                ->findOrFail($validated['purchase_order_id']);

            if (!$this->accessibleBranch($purchaseOrder->branch_id)) {
                abort(403, 'You do not have access to this purchase order.');
            }

            if (!in_array($purchaseOrder->status, [
                'approved',
                'partially_received',
            ])) {
                abort(422, 'Goods receipt can only be created for approved or partially received purchase orders.');
            }

            if ($purchaseOrder->warehouse_id !== $validated['warehouse_id']) {
                abort(422, 'Selected warehouse does not match the purchase order warehouse.');
            }

            $receiptNumberExists = GoodsReceipt::where(
                'warehouse_id',
                $validated['warehouse_id']
            )
                ->where('receipt_number', $validated['receipt_number'])
                ->exists();

            if ($receiptNumberExists) {
                abort(422, 'This receipt number already exists for the selected warehouse.');
            }

            $receiptItems = [];

            foreach ($validated['items'] as $item) {

                $purchaseOrderItem = PurchaseOrderItem::where(
                    'purchase_order_id',
                    $purchaseOrder->id
                )
                    ->lockForUpdate()
                    ->find($item['purchase_order_item_id']);

                if (!$purchaseOrderItem) {
                    abort(422, 'One or more selected items do not belong to this purchase order.');
                }

                $postedQuantity = (float) $purchaseOrderItem->received_quantity;

                $draftReservedQuantity = (float) GoodsReceiptItem::whereHas(
                    'goodsReceipt',
                    function ($query) {
                        $query->where('status', 'draft');
                    }
                )
                    ->where('purchase_order_item_id', $purchaseOrderItem->id)
                    ->where('is_removed', false)
                    ->sum('received_quantity');

                $remainingQuantity = (float) $purchaseOrderItem->quantity
                    - $postedQuantity
                    - $draftReservedQuantity;

                if ((float) $item['received_quantity'] > $remainingQuantity) {
                    abort(
                        422,
                        "Received quantity exceeds remaining quantity for product: {$purchaseOrderItem->product->name}. Remaining quantity: {$remainingQuantity}"
                    );
                }

                $conversionFactor = (float) $purchaseOrderItem->conversion_factor;

                $receiptItems[] = [
                    'purchase_order_item_id' => $purchaseOrderItem->id,
                    'product_id' => $purchaseOrderItem->product_id,
                    'unit_id' => $purchaseOrderItem->unit_id,
                    'received_quantity' => $item['received_quantity'],
                    'conversion_factor' => $conversionFactor,
                    'base_quantity' => (float) $item['received_quantity'] * $conversionFactor,
                    'unit_cost' => $purchaseOrderItem->unit_cost,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            $goodsReceipt = GoodsReceipt::create([
                'purchase_order_id' => $purchaseOrder->id,
                'warehouse_id' => $purchaseOrder->warehouse_id,
                'receipt_number' => $validated['receipt_number'],
                'receipt_date' => $validated['receipt_date'],
                'supplier_delivery_number' => $validated['supplier_delivery_number'] ?? null,
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'received_by' => auth()->id(),
            ]);

            foreach ($receiptItems as $receiptItem) {

                $goodsReceiptItem = $goodsReceipt->items()->create($receiptItem);

                app(AuditService::class)->log(
                    $goodsReceiptItem,
                    'created',
                    null,
                    $receiptItem,
                    'Goods receipt item created.',
                    [],
                    auth()->user(),
                    $this->companyId(),
                    $purchaseOrder->branch_id
                );
            }

            app(AuditService::class)->log(
                $goodsReceipt,
                'created',
                null,
                [
                    'purchase_order_id' => $purchaseOrder->id,
                    'warehouse_id' => $goodsReceipt->warehouse_id,
                    'receipt_number' => $goodsReceipt->receipt_number,
                    'receipt_date' => $goodsReceipt->receipt_date,
                    'status' => $goodsReceipt->status,
                    'items_count' => count($receiptItems),
                ],
                'Goods receipt draft created.',
                [],
                auth()->user(),
                $this->companyId(),
                $purchaseOrder->branch_id
            );

            return response()->json([
                'message' => 'Goods receipt draft created successfully.',
                'data' => $goodsReceipt->load([
                    'purchaseOrder',
                    'warehouse',
                    'items.product',
                    'items.unit',
                    'items.purchaseOrderItem',
                    'receiver',
                ]),
            ], 201);
        });
    }

    public function show($id)
    {
        $goodsReceipt = GoodsReceipt::whereHas('purchaseOrder.branch', function ($query) {
            $query->where('company_id', $this->companyId());
        })
            ->whereHas('purchaseOrder', function ($query) {
                $query->whereIn('branch_id', auth()->user()->branches()->select('branches.id'));
            })
            ->with([
                'purchaseOrder.supplier',
                'purchaseOrder.branch',
                'purchaseOrder.warehouse',
                'warehouse',
                'receiver',
                'items.product',
                'items.unit',
                'items.purchaseOrderItem',
            ])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Goods receipt retrieved successfully.',
            'data' => $goodsReceipt,
        ]);
    }

    
    public function update(Request $request, $id)
    {
        $goodsReceipt = GoodsReceipt::whereHas('purchaseOrder.branch', function ($query) {
            $query->where('company_id', $this->companyId());
        })->findOrFail($id);

        if (!$this->accessibleBranch($goodsReceipt->purchaseOrder->branch_id)) {
            abort(403, 'You do not have access to this goods receipt.');
        }

        if ($goodsReceipt->status !== 'draft') {
            abort(422, 'Only draft goods receipts can be updated.');
        }

        $validated = $request->validate([
            'receipt_number' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('goods_receipts', 'receipt_number')
                    ->where('warehouse_id', $goodsReceipt->warehouse_id)
                    ->ignore($goodsReceipt->id),
            ],

            'receipt_date' => [
                'sometimes',
                'required',
                'date',
            ],

            'supplier_delivery_number' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'items' => [
                'sometimes',
                'required',
                'array',
                'min:1',
            ],

            'items.*.id' => [
                'sometimes',
                'nullable',
                'uuid',
                'exists:goods_receipt_items,id',
            ],

            'items.*.purchase_order_item_id' => [
                'required_with:items',
                'uuid',
                'distinct',
                'exists:purchase_order_items,id',
            ],

            'items.*.received_quantity' => [
                'required_with:items',
                'numeric',
                'gt:0',
            ],

            'items.*.notes' => [
                'nullable',
                'string',
            ],
        ]);

        return DB::transaction(function () use ($validated, $goodsReceipt) {

            $goodsReceipt = GoodsReceipt::whereKey($goodsReceipt->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($goodsReceipt->status !== 'draft') {
                abort(422, 'Only draft goods receipts can be updated.');
            }

            $purchaseOrder = PurchaseOrder::whereKey($goodsReceipt->purchase_order_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$this->accessibleBranch($purchaseOrder->branch_id)) {
                abort(403, 'You do not have access to this purchase order.');
            }

            if (!in_array($purchaseOrder->status, [
                'approved',
                'partially_received',
            ])) {
                abort(422, 'The linked purchase order is no longer available for goods receipt updates.');
            }

            $audit = app(AuditService::class);

            /*
            |--------------------------------------------------------------------------
            | Update GRN Header
            |--------------------------------------------------------------------------
            */

            $headerFields = [
                'receipt_number',
                'receipt_date',
                'supplier_delivery_number',
                'notes',
            ];

            $headerData = array_intersect_key(
                $validated,
                array_flip($headerFields)
            );

            if (!empty($headerData)) {

                $goodsReceipt->fill($headerData);

                $dirty = $goodsReceipt->getDirty();

                if (!empty($dirty)) {

                    $oldValues = [];

                    foreach (array_keys($dirty) as $field) {
                        $oldValues[$field] = $goodsReceipt->getRawOriginal($field);
                    }

                    $newValues = $dirty;

                    $goodsReceipt->save();

                    $audit->log(
                        $goodsReceipt,
                        'updated',
                        $oldValues,
                        $newValues,
                        'Goods receipt header updated.',
                        [],
                        auth()->user(),
                        $this->companyId(),
                        $purchaseOrder->branch_id
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Update GRN Items
            |--------------------------------------------------------------------------
            */

            if (array_key_exists('items', $validated)) {

                $processedItemIds = [];

                foreach ($validated['items'] as $item) {

                    $purchaseOrderItem = PurchaseOrderItem::where(
                        'purchase_order_id',
                        $purchaseOrder->id
                    )
                        ->lockForUpdate()
                        ->find($item['purchase_order_item_id']);

                    if (!$purchaseOrderItem) {
                        abort(422, 'Selected item does not belong to this purchase order.');
                    }

                    /*
                    * Posted quantity already exists in PO item.
                    */

                    $postedQuantity = (float) $purchaseOrderItem->received_quantity;

                    /*
                    * Other draft GRNs reserve their quantities.
                    * Current GRN is excluded because its quantity
                    * is being replaced.
                    */

                    $otherDraftReservedQuantity = (float) GoodsReceiptItem::whereHas(
                        'goodsReceipt',
                        function ($query) use ($goodsReceipt) {
                            $query->where('status', 'draft')
                                ->where('id', '!=', $goodsReceipt->id);
                        }
                    )
                        ->where('purchase_order_item_id', $purchaseOrderItem->id)
                        ->where('is_removed', false)
                        ->sum('received_quantity');

                    $remainingQuantity = (float) $purchaseOrderItem->quantity
                        - $postedQuantity
                        - $otherDraftReservedQuantity;

                    if ((float) $item['received_quantity'] > $remainingQuantity) {
                        abort(
                            422,
                            "Received quantity exceeds remaining quantity for product ID: {$purchaseOrderItem->product_id}. Remaining quantity: {$remainingQuantity}"
                        );
                    }

                    /*
                    * Always derive product details from PO item.
                    */

                    $itemData = [
                        'purchase_order_item_id' => $purchaseOrderItem->id,
                        'product_id' => $purchaseOrderItem->product_id,
                        'unit_id' => $purchaseOrderItem->unit_id,
                        'received_quantity' => $item['received_quantity'],
                        'conversion_factor' => $purchaseOrderItem->conversion_factor,
                        'base_quantity' => (float) $item['received_quantity'] * (float) $purchaseOrderItem->conversion_factor,
                        'unit_cost' => $purchaseOrderItem->unit_cost,
                        'notes' => $item['notes'] ?? null,
                        'is_removed' => false,
                    ];

                    /*
                    * Find existing item by ID.
                    */

                    if (!empty($item['id'])) {

                        $receiptItem = $goodsReceipt->items()
                            ->lockForUpdate()
                            ->find($item['id']);

                        if (!$receiptItem) {
                            abort(422, 'Selected GRN item does not belong to this goods receipt.');
                        }

                        if ($receiptItem->purchase_order_item_id !== $purchaseOrderItem->id) {
                            abort(422, 'GRN item cannot be linked to a different purchase order item.');
                        }

                    } else {

                        $receiptItem = $goodsReceipt->items()
                            ->where('purchase_order_item_id', $purchaseOrderItem->id)
                            ->lockForUpdate()
                            ->first();
                    }

                    /*
                    * Update existing item or create new item.
                    */

                    if ($receiptItem) {

                        $oldValues = $receiptItem->only([
                            'purchase_order_item_id',
                            'product_id',
                            'unit_id',
                            'received_quantity',
                            'conversion_factor',
                            'base_quantity',
                            'unit_cost',
                            'notes',
                            'is_removed',
                        ]);

                        $wasRemoved = (bool) $receiptItem->is_removed;

                        $receiptItem->fill($itemData);

                        $dirty = $receiptItem->getDirty();

                        if (!empty($dirty)) {

                            $newValues = [];

                            foreach (array_keys($dirty) as $field) {
                                $newValues[$field] = $receiptItem->getAttribute($field);
                            }

                            $receiptItem->save();

                            $audit->log(
                                $receiptItem,
                                $wasRemoved ? 'restored' : 'updated',
                                $oldValues,
                                $newValues,
                                $wasRemoved
                                    ? 'Goods receipt item restored.'
                                    : 'Goods receipt item updated.',
                                [],
                                auth()->user(),
                                $this->companyId(),
                                $purchaseOrder->branch_id
                            );
                        }

                    } else {

                        $receiptItem = $goodsReceipt->items()->create($itemData);

                        $audit->log(
                            $receiptItem,
                            'created',
                            null,
                            $itemData,
                            'New goods receipt item added.',
                            [],
                            auth()->user(),
                            $this->companyId(),
                            $purchaseOrder->branch_id
                        );
                    }

                    $processedItemIds[] = $receiptItem->id;
                }

                /*
                * Items missing from submitted array
                * will be marked as removed.
                */

                $removedItems = $goodsReceipt->items()
                    ->where('is_removed', false)
                    ->whereNotIn('id', $processedItemIds)
                    ->lockForUpdate()
                    ->get();

                foreach ($removedItems as $removedItem) {

                    $oldValues = $removedItem->only([
                        'purchase_order_item_id',
                        'product_id',
                        'unit_id',
                        'received_quantity',
                        'conversion_factor',
                        'base_quantity',
                        'unit_cost',
                        'notes',
                        'is_removed',
                    ]);

                    $removedItem->update([
                        'is_removed' => true,
                    ]);

                    $audit->log(
                        $removedItem,
                        'removed',
                        $oldValues,
                        ['is_removed' => true],
                        'Goods receipt item removed from draft.',
                        [],
                        auth()->user(),
                        $this->companyId(),
                        $purchaseOrder->branch_id
                    );
                }
            }

            return response()->json([
                'message' => 'Goods receipt updated successfully.',
                'data' => $goodsReceipt->fresh()->load([
                    'purchaseOrder.supplier',
                    'warehouse',
                    'receiver',
                    'items.product',
                    'items.unit',
                    'items.purchaseOrderItem',
                ]),
            ]);
        });
    }

    public function post($id)
    {
        return DB::transaction(function () use ($id) {

            // 1. Find and lock GRN within user's company and accessible branches.
            $goodsReceipt = GoodsReceipt::whereHas(
                'purchaseOrder.branch',
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

            if ($goodsReceipt->status !== 'draft') {
                abort(422, 'Only draft goods receipts can be posted.');
            }

            // 2. Lock linked purchase order.
            $purchaseOrder = PurchaseOrder::whereHas('branch', function ($query) {
                $query->where('company_id', $this->companyId());
            })
                ->lockForUpdate()
                ->findOrFail($goodsReceipt->purchase_order_id);

            if (!$this->accessibleBranch($purchaseOrder->branch_id)) {
                abort(403, 'You do not have access to this purchase order.');
            }

            if (!in_array($purchaseOrder->status, [
                'approved',
                'partially_received',
            ])) {
                abort(422, 'This purchase order is not available for receiving.');
            }

            if ($goodsReceipt->warehouse_id !== $purchaseOrder->warehouse_id) {
                abort(422, 'Goods receipt warehouse does not match purchase order warehouse.');
            }

            // 3. Get active GRN items.
            $receiptItems = $goodsReceipt->items()
                ->where('is_removed', false)
                ->lockForUpdate()
                ->get();

            if ($receiptItems->isEmpty()) {
                abort(422, 'Cannot post a goods receipt without active items.');
            }

            // 4. Lock all related PO items.
            $poItemIds = $receiptItems
                ->pluck('purchase_order_item_id')
                ->unique()
                ->values();

            $purchaseOrderItems = PurchaseOrderItem::where(
                'purchase_order_id',
                $purchaseOrder->id
            )
                ->whereIn('id', $poItemIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($purchaseOrderItems->count() !== $poItemIds->count()) {
                abort(422, 'One or more GRN items do not belong to this purchase order.');
            }

            $audit = app(AuditService::class);
            $companyId = $this->companyId();
            $branchId = $purchaseOrder->branch_id;

            // 5. Process each GRN item.
            foreach ($receiptItems as $receiptItem) {

                $purchaseOrderItem = $purchaseOrderItems->get(
                    $receiptItem->purchase_order_item_id
                );

                if (!$purchaseOrderItem) {
                    abort(422, 'Invalid purchase order item.');
                }

                if (
                    $receiptItem->product_id !== $purchaseOrderItem->product_id ||
                    $receiptItem->unit_id !== $purchaseOrderItem->unit_id
                ) {
                    abort(422, 'GRN item product or unit does not match purchase order item.');
                }

                $receivedQuantity = (float) $receiptItem->received_quantity;
                $baseQuantity = (float) $receiptItem->base_quantity;
                $grnConversionFactor = (float) $receiptItem->conversion_factor;
                $poConversionFactor = (float) $purchaseOrderItem->conversion_factor;

                if (
                    $receivedQuantity <= 0 ||
                    $baseQuantity <= 0 ||
                    $grnConversionFactor <= 0 ||
                    $poConversionFactor <= 0
                ) {
                    abort(422, 'Invalid quantity or conversion factor found in GRN.');
                }

                // 6. Check remaining PO quantity.
                // Exclude current GRN because it is being posted now.
                $otherDraftReservedQuantity = (float) GoodsReceiptItem::whereHas(
                    'goodsReceipt',
                    function ($query) use ($goodsReceipt) {
                        $query->where('status', 'draft')
                            ->where('id', '!=', $goodsReceipt->id);
                    }
                )
                    ->where('purchase_order_item_id', $purchaseOrderItem->id)
                    ->where('is_removed', false)
                    ->sum('received_quantity');

                $postedQuantity = (float) $purchaseOrderItem->received_quantity;

                $remainingQuantity =
                    (float) $purchaseOrderItem->quantity
                    - $postedQuantity
                    - $otherDraftReservedQuantity;

                if ($receivedQuantity > $remainingQuantity + 0.00000001) {
                    abort(
                        422,
                        "Received quantity exceeds remaining quantity for product ID: {$purchaseOrderItem->product_id}. Remaining quantity: {$remainingQuantity}"
                    );
                }

                // 7. Get or create stock balance safely using unique constraint.
                $now = now();

                DB::table('stock_balances')->insertOrIgnore([
                    'id' => (string) Str::uuid(),
                    'warehouse_id' => $goodsReceipt->warehouse_id,
                    'product_id' => $receiptItem->product_id,
                    'on_hand_quantity' => 0,
                    'reserved_quantity' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $stockBalance = StockBalance::where(
                    'warehouse_id',
                    $goodsReceipt->warehouse_id
                )
                    ->where('product_id', $receiptItem->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $oldBalance = (float) $stockBalance->on_hand_quantity;
                $newBalance = $oldBalance + $baseQuantity;

                // 8. Update stock balance.
                $oldBalanceValues = [
                    'on_hand_quantity' => $stockBalance->on_hand_quantity,
                    'reserved_quantity' => $stockBalance->reserved_quantity,
                ];

                $stockBalance->on_hand_quantity = $newBalance;
                $stockBalance->save();

                $audit->log(
                    $stockBalance,
                    'updated',
                    $oldBalanceValues,
                    [
                        'on_hand_quantity' => $stockBalance->on_hand_quantity,
                        'reserved_quantity' => $stockBalance->reserved_quantity,
                    ],
                    'Stock balance updated through goods receipt posting.',
                    ['goods_receipt_id' => $goodsReceipt->id],
                    auth()->user(),
                    $companyId,
                    $branchId
                );

                // 9. Create stock movement.
                $movement = new StockMovement();

                $movement->forceFill([
                    'warehouse_id' => $goodsReceipt->warehouse_id,
                    'product_id' => $receiptItem->product_id,
                    'unit_id' => $receiptItem->unit_id,
                    'movement_type' => 'purchase',
                    'direction' => 'in',
                    'quantity' => $receivedQuantity,
                    'conversion_factor' => $grnConversionFactor,
                    'base_quantity' => $baseQuantity,
                    'balance_after' => $newBalance,
                    'reference_type' => $goodsReceipt->getMorphClass(),
                    'reference_id' => $goodsReceipt->id,
                    'notes' => $receiptItem->notes,
                    'created_by' => auth()->id(),
                    'created_at' => $now,
                ]);

                $movement->save();

                $audit->log(
                    $movement,
                    'created',
                    null,
                    [
                        'movement_type' => 'purchase',
                        'direction' => 'in',
                        'quantity' => $receivedQuantity,
                        'base_quantity' => $baseQuantity,
                        'balance_after' => $newBalance,
                        'reference_id' => $goodsReceipt->id,
                    ],
                    'Purchase stock movement created through goods receipt.',
                    [],
                    auth()->user(),
                    $companyId,
                    $branchId
                );

                // 10. Update PO received quantity in PO unit.
                $receivedPoQuantity = $baseQuantity / $poConversionFactor;

                $oldPoReceivedQuantity = $purchaseOrderItem->received_quantity;

                $purchaseOrderItem->received_quantity =
                    $postedQuantity + $receivedPoQuantity;

                $purchaseOrderItem->save();

                $audit->log(
                    $purchaseOrderItem,
                    'updated',
                    [
                        'received_quantity' => $oldPoReceivedQuantity,
                    ],
                    [
                        'received_quantity' => $purchaseOrderItem->received_quantity,
                    ],
                    'Purchase order received quantity updated through goods receipt.',
                    ['goods_receipt_id' => $goodsReceipt->id],
                    auth()->user(),
                    $companyId,
                    $branchId
                );
            }

            // 11. Update purchase order status.
            $hasPendingItems = PurchaseOrderItem::where(
                'purchase_order_id',
                $purchaseOrder->id
            )
                ->whereRaw('received_quantity < quantity')
                ->exists();

            $oldPoStatus = $purchaseOrder->status;

            $purchaseOrder->status = $hasPendingItems
                ? 'partially_received'
                : 'received';

            if ($oldPoStatus !== $purchaseOrder->status) {
                $purchaseOrder->save();

                $audit->log(
                    $purchaseOrder,
                    'updated',
                    ['status' => $oldPoStatus],
                    ['status' => $purchaseOrder->status],
                    'Purchase order status updated through goods receipt posting.',
                    ['goods_receipt_id' => $goodsReceipt->id],
                    auth()->user(),
                    $companyId,
                    $branchId
                );
            }

            // 12. Mark GRN as posted.
            $oldGrnStatus = $goodsReceipt->status;

            $goodsReceipt->status = 'posted';
            $goodsReceipt->posted_at = now();
            $goodsReceipt->save();

            $audit->log(
                $goodsReceipt,
                'posted',
                [
                    'status' => $oldGrnStatus,
                    'posted_at' => null,
                ],
                [
                    'status' => $goodsReceipt->status,
                    'posted_at' => $goodsReceipt->posted_at,
                ],
                'Goods receipt posted and stock updated.',
                [
                    'purchase_order_id' => $purchaseOrder->id,
                    'items_count' => $receiptItems->count(),
                ],
                auth()->user(),
                $companyId,
                $branchId
            );

            return response()->json([
                'message' => 'Goods receipt posted successfully. Stock has been updated.',
                'data' => $goodsReceipt->fresh()->load([
                    'purchaseOrder.supplier',
                    'purchaseOrder.branch',
                    'warehouse',
                    'receiver',
                    'items.product',
                    'items.unit',
                    'items.purchaseOrderItem',
                ]),
            ]);
        });
    }

    public function cancel($id)
    {
        return DB::transaction(function () use ($id) {

            $goodsReceipt = GoodsReceipt::whereHas(
                'purchaseOrder.branch',
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

            if ($goodsReceipt->status !== 'draft') {
                abort(422, 'Only draft goods receipts can be cancelled.');
            }

            $purchaseOrder = PurchaseOrder::findOrFail(
                $goodsReceipt->purchase_order_id
            );

            if (!$this->accessibleBranch($purchaseOrder->branch_id)) {
                abort(403, 'You do not have access to this purchase order.');
            }

            $oldStatus = $goodsReceipt->status;

            $goodsReceipt->status = 'cancelled';
            $goodsReceipt->save();

            app(AuditService::class)->log(
                $goodsReceipt,
                'cancelled',
                ['status' => $oldStatus],
                ['status' => $goodsReceipt->status],
                'Goods receipt cancelled.',
                [
                    'purchase_order_id' => $purchaseOrder->id,
                ],
                auth()->user(),
                $this->companyId(),
                $purchaseOrder->branch_id
            );

            return response()->json([
                'message' => 'Goods receipt cancelled successfully.',
                'data' => $goodsReceipt->fresh()->load([
                    'purchaseOrder.supplier',
                    'purchaseOrder.branch',
                    'warehouse',
                    'receiver',
                    'items.product',
                    'items.unit',
                    'items.purchaseOrderItem',
                ]),
            ]);
        });
    }
}
