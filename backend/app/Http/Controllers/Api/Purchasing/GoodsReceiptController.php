<?php

namespace App\Http\Controllers\Api\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

                $remainingQuantity = (float) $purchaseOrderItem->quantity
                    - (float) $purchaseOrderItem->received_quantity;

                if ((float) $item['received_quantity'] > $remainingQuantity) {
                    abort(
                        422,
                        "Received quantity exceeds remaining quantity for product ID: {$purchaseOrderItem->product_id}"
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
}
