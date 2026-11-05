<?php

namespace App\Http\Controllers\Api\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\AuditService;

class PurchaseOrderController extends Controller
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

    private function getPurchaseUnitDetails($productId, $unitId)
    {
        return DB::table('product_units')
            ->where('product_id', $productId)
            ->where('unit_id', $unitId)
            ->where('is_purchase_unit', true)
            ->where('status', 'active')
            ->first();
    }

    private function validateItems(array $items)
    {
        foreach ($items as $item) {
            $product = Product::where('company_id', $this->companyId())
                ->where('status', 'active')
                ->findOrFail($item['product_id']);

            $unit = Unit::where('status', 'active')
                ->findOrFail($item['unit_id']);

            $unitDetails = $this->getPurchaseUnitDetails(
                $product->id,
                $unit->id
            );

            if (!$unitDetails) {
                abort(
                    422,
                    "Selected unit is not an active purchase unit for product: {$product->name}"
                );
            }
        }
    }

    public function index(Request $request)
    {
        $query = PurchaseOrder::whereHas('branch', function ($q) {
            $q->where('company_id', $this->companyId());
        })
            ->with([
                'supplier',
                'branch',
                'warehouse',
                'purchaseRequest',
                'creator',
                'approver',
                'items.product',
                'items.unit',
                'items.purchaseRequestItem',
            ])
            ->latest();

        // Branch filter
        if ($request->filled('branch_id')) {
            if (!$this->accessibleBranch($request->branch_id)) {
                abort(403, 'You do not have access to this branch.');
            }

            $query->where('branch_id', $request->branch_id);
        }

        // Supplier filter
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by PO number
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where('order_number', 'like', "%{$search}%");
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(15),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => [
                'required',
                'uuid',
                Rule::exists('suppliers', 'id')
                    ->where('company_id', $this->companyId())
                    ->where('status', 'active'),
            ],

            'branch_id' => ['required', 'uuid'],
            'warehouse_id' => ['required', 'uuid'],

            'purchase_request_id' => [
                'nullable',
                'uuid',
                'exists:purchase_requests,id',
            ],

            'order_number' => [
                'required',
                'string',
                'max:255',
            ],

            'order_date' => ['required', 'date'],
            'expected_date' => [
                'nullable',
                'date',
                'after_or_equal:order_date',
            ],

            'currency' => ['nullable', 'string', 'size:3'],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_request_item_id' => [
                'nullable',
                'uuid',
            ],
            'items.*.product_id' => ['required', 'uuid'],
            'items.*.unit_id' => ['required', 'uuid'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        if (!$this->accessibleBranch($validated['branch_id'])) {
            abort(403, 'You do not have access to this branch.');
        }

        $warehouseExists = DB::table('warehouses')
            ->where('id', $validated['warehouse_id'])
            ->where('branch_id', $validated['branch_id'])
            ->where('status', 'active')
            ->exists();

        if (!$warehouseExists) {
            abort(422, 'Selected warehouse does not belong to this branch.');
        }

        $this->validateItems($validated['items']);

        $purchaseOrder = DB::transaction(function () use ($validated) {

            $purchaseRequest = null;

            if (!empty($validated['purchase_request_id'])) {

                $purchaseRequest = PurchaseRequest::whereHas('branch', function ($query) {
                    $query->where('company_id', $this->companyId());
                })
                    ->lockForUpdate()
                    ->findOrFail($validated['purchase_request_id']);

                if (!$this->accessibleBranch($purchaseRequest->branch_id)) {
                    abort(403, 'You do not have access to this purchase request.');
                }

                if ($purchaseRequest->status !== 'approved') {
                    abort(422, 'Only approved purchase requests can be converted.');
                }

                if ($purchaseRequest->branch_id !== $validated['branch_id']) {
                    abort(422, 'Purchase request and purchase order must belong to the same branch.');
                }

                if (
                    $purchaseRequest->warehouse_id &&
                    $purchaseRequest->warehouse_id !== $validated['warehouse_id']
                ) {
                    abort(422, 'Purchase order warehouse must match the purchase request warehouse.');
                }
            }

            $preparedItems = [];

            $subtotal = 0;
            $totalDiscount = 0;
            $totalTax = 0;

            foreach ($validated['items'] as $item) {

                $unitDetails = $this->getPurchaseUnitDetails(
                    $item['product_id'],
                    $item['unit_id']
                );

                if (!$unitDetails) {
                    abort(422, 'Invalid purchase unit selected.');
                }

                $quantity = (float) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];

                $conversionFactor = (float) $unitDetails->conversion_factor;

                if ($conversionFactor <= 0) {
                    abort(422, 'Invalid unit conversion factor.');
                }

                $baseQuantity = $quantity * $conversionFactor;

                $grossAmount = $quantity * $unitCost;

                $discount = (float) ($item['discount_amount'] ?? 0);
                $tax = (float) ($item['tax_amount'] ?? 0);

                if ($discount > $grossAmount) {
                    abort(422, 'Discount cannot exceed item gross amount.');
                }

                $itemTotal = $grossAmount - $discount + $tax;

                $subtotal += $grossAmount;
                $totalDiscount += $discount;
                $totalTax += $tax;

                $preparedItems[] = [
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'quantity' => $quantity,
                    'conversion_factor' => $conversionFactor,
                    'base_quantity' => $baseQuantity,
                    'received_quantity' => 0,
                    'unit_cost' => $unitCost,
                    'discount_amount' => $discount,
                    'tax_amount' => $tax,
                    'total_amount' => $itemTotal,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            if ($purchaseRequest) {

                $requestedItems = $purchaseRequest->items()
                    ->lockForUpdate()
                    ->get();

                $allocations = [];

                foreach ($preparedItems as $index => $preparedItem) {

                    $requestItemId = $validated['items'][$index]['purchase_request_item_id'] ?? null;

                    if (!$requestItemId) {
                        abort(422, 'Purchase request item reference is required.');
                    }

                    $requestItem = $requestedItems->firstWhere('id', $requestItemId);

                    if (!$requestItem) {
                        abort(422, 'Selected item does not belong to this purchase request.');
                    }

                    if (
                        $requestItem->product_id !== $preparedItem['product_id']
                    ) {
                        abort(422, 'Purchase request product does not match.');
                    }

                    $requestedUnit = DB::table('product_units')
                        ->where('product_id', $requestItem->product_id)
                        ->where('unit_id', $requestItem->unit_id)
                        ->first();

                    if (!$requestedUnit || (float) $requestedUnit->conversion_factor <= 0) {
                        abort(422, 'Invalid purchase request unit conversion.');
                    }

                    $requestedBaseQuantity =
                        (float) $requestItem->quantity *
                        (float) $requestedUnit->conversion_factor;

                    $allocatedBaseQuantity = PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)
                        ->whereHas('items', function ($query) use ($requestItemId) {
                            $query->where('purchase_request_item_id', $requestItemId);
                        })
                        ->with(['items' => function ($query) use ($requestItemId) {
                            $query->where('purchase_request_item_id', $requestItemId);
                        }])
                        ->get()
                        ->flatMap->items
                        ->sum('base_quantity');

                    $remainingBaseQuantity =
                        $requestedBaseQuantity - (float) $allocatedBaseQuantity;

                    if ($preparedItem['base_quantity'] > $remainingBaseQuantity) {
                        abort(422, 'Ordered quantity exceeds remaining purchase request quantity.');
                    }

                    $allocations[$index] = $requestItemId;
                }

                foreach ($preparedItems as $index => &$preparedItem) {
                    $preparedItem['purchase_request_item_id'] = $allocations[$index];
                }

                unset($preparedItem);
            }

            $order = PurchaseOrder::create([
                'supplier_id' => $validated['supplier_id'],
                'branch_id' => $validated['branch_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'purchase_request_id' => $purchaseRequest?->id,

                'order_number' => $validated['order_number'],
                'order_date' => $validated['order_date'],
                'expected_date' => $validated['expected_date'] ?? null,

                'status' => 'draft',
                'currency' => $validated['currency'] ?? 'PKR',

                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $totalTax,
                'total_amount' => $subtotal - $totalDiscount + $totalTax,

                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $order->items()->createMany($preparedItems);

            return $order;
        });

        return response()->json([
            'message' => 'Purchase order created successfully.',
            'data' => $purchaseOrder->load([
                'supplier',
                'branch',
                'warehouse',
                'purchaseRequest',
                'items.product',
                'items.unit',
            ]),
        ], 201);
    }

    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::whereHas('branch', function ($q) {
            $q->where('company_id', $this->companyId());
        })
            ->with([
                'supplier.addresses',
                'branch',
                'warehouse',
                'purchaseRequest',
                'creator',
                'approver',
                'items.product',
                'items.unit',
                'items.purchaseRequestItem',
            ])
            ->findOrFail($id);

        if (!$this->accessibleBranch($purchaseOrder->branch_id)) {
            abort(403, 'You do not have access to this branch.');
        }

        return response()->json([
            'success' => true,
            'data' => $purchaseOrder,
        ]);
    }

    public function update(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::whereHas('branch', function ($q) {
            $q->where('company_id', $this->companyId());
        })->findOrFail($id);

        if (!$this->accessibleBranch($purchaseOrder->branch_id)) {
            abort(403, 'You do not have access to this branch.');
        }

        if ($purchaseOrder->status !== 'draft') {
            abort(422, 'Only draft purchase orders can be updated.');
        }

        $validated = $request->validate([
            'supplier_id' => [
                'sometimes',
                'required',
                'uuid',
                'exists:suppliers,id',
            ],

            'branch_id' => [
                'sometimes',
                'required',
                'uuid',
                'exists:branches,id',
            ],

            'warehouse_id' => [
                'sometimes',
                'required',
                'uuid',
                'exists:warehouses,id',
            ],

            'purchase_request_id' => [
                'sometimes',
                'nullable',
                'uuid',
                'exists:purchase_requests,id',
            ],

            'order_number' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('purchase_orders', 'order_number')
                    ->where('branch_id', $request->input('branch_id', $purchaseOrder->branch_id))
                    ->ignore($purchaseOrder->id),
            ],

            'order_date' => ['sometimes', 'required', 'date'],
            'expected_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'currency' => ['sometimes', 'required', 'string', 'size:3'],
            'notes' => ['nullable', 'string'],

            'items' => ['sometimes', 'required', 'array', 'min:1'],

            'items.*.purchase_request_item_id' => [
                'nullable',
                'uuid',
                'distinct',
            ],

            'items.*.product_id' => [
                'required_with:items',
                'uuid',
                'exists:products,id',
            ],

            'items.*.unit_id' => [
                'required_with:items',
                'uuid',
                'exists:units,id',
            ],

            'items.*.quantity' => [
                'required_with:items',
                'numeric',
                'gt:0',
            ],

            'items.*.unit_cost' => [
                'required_with:items',
                'numeric',
                'min:0',
            ],

            'items.*.discount_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'items.*.tax_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'items.*.notes' => ['nullable', 'string'],
        ]);

        $supplierId = $validated['supplier_id'] ?? $purchaseOrder->supplier_id;
        $branchId = $validated['branch_id'] ?? $purchaseOrder->branch_id;
        $warehouseId = $validated['warehouse_id'] ?? $purchaseOrder->warehouse_id;

        if (!$this->accessibleBranch($branchId)) {
            abort(403, 'You do not have access to this branch.');
        }

        $supplier = \App\Models\Supplier::where('company_id', $this->companyId())
            ->where('status', 'active')
            ->findOrFail($supplierId);

        $warehouse = \App\Models\Warehouse::where('branch_id', $branchId)
            ->where('status', 'active')
            ->findOrFail($warehouseId);

        $purchaseRequestId = array_key_exists('purchase_request_id', $validated)
            ? $validated['purchase_request_id']
            : $purchaseOrder->purchase_request_id;

        $purchaseRequest = null;

        if ($purchaseRequestId) {
            $purchaseRequest = PurchaseRequest::whereHas('branch', function ($q) {
                $q->where('company_id', $this->companyId());
            })->findOrFail($purchaseRequestId);

            if ($purchaseRequest->branch_id !== $branchId) {
                abort(422, 'Purchase request and purchase order must belong to the same branch.');
            }

            if ($purchaseRequest->status !== 'approved') {
                abort(422, 'Only approved purchase requests can be used.');
            }

            if (
                $purchaseRequest->warehouse_id &&
                $purchaseRequest->warehouse_id !== $warehouseId
            ) {
                abort(422, 'Purchase order warehouse must match purchase request warehouse.');
            }
        }

        if (isset($validated['items'])) {
            $this->validateItems($validated['items']);
        }

        return DB::transaction(function () use (
            $validated,
            $purchaseOrder,
            $supplier,
            $branchId,
            $warehouseId,
            $purchaseRequest,
            $purchaseRequestId
        ) {
            $items = $validated['items'] ?? null;

            $purchaseOrder->update([
                'supplier_id' => $supplier->id,
                'branch_id' => $branchId,
                'warehouse_id' => $warehouseId,
                'purchase_request_id' => $purchaseRequestId,
                'order_number' => $validated['order_number'] ?? $purchaseOrder->order_number,
                'order_date' => $validated['order_date'] ?? $purchaseOrder->order_date,
                'expected_date' => $validated['expected_date'] ?? $purchaseOrder->expected_date,
                'currency' => $validated['currency'] ?? $purchaseOrder->currency,
                'notes' => $validated['notes'] ?? $purchaseOrder->notes,
            ]);

            if ($items !== null) {
                $preparedItems = [];
                $subtotal = 0;
                $discountTotal = 0;
                $taxTotal = 0;
                $totalAmount = 0;

                foreach ($items as $item) {
                    $unitDetails = $this->getPurchaseUnitDetails(
                        $item['product_id'],
                        $item['unit_id']
                    );

                    if (!$unitDetails || $unitDetails->conversion_factor <= 0) {
                        abort(422, 'Invalid purchase unit selected.');
                    }

                    $conversionFactor = (float) $unitDetails->conversion_factor;
                    $quantity = (float) $item['quantity'];
                    $baseQuantity = $quantity * $conversionFactor;

                    $gross = $quantity * (float) $item['unit_cost'];
                    $discount = (float) ($item['discount_amount'] ?? 0);
                    $tax = (float) ($item['tax_amount'] ?? 0);

                    if ($discount > $gross) {
                        abort(422, 'Discount cannot exceed item gross amount.');
                    }

                    $lineTotal = $gross - $discount + $tax;

                    if ($purchaseRequest) {
                        $requestItemId = $item['purchase_request_item_id'] ?? null;

                        if (!$requestItemId) {
                            abort(422, 'Purchase request item ID is required for PR-based purchase orders.');
                        }

                        $requestItem = $purchaseRequest->items()
                            ->whereKey($requestItemId)
                            ->firstOrFail();

                        if ($requestItem->product_id !== $item['product_id']) {
                            abort(422, 'Selected product does not match purchase request item.');
                        }

                        $requestUnit = DB::table('product_units')
                            ->where('product_id', $requestItem->product_id)
                            ->where('unit_id', $requestItem->unit_id)
                            ->where('status', 'active')
                            ->first();

                        if (!$requestUnit || $requestUnit->conversion_factor <= 0) {
                            abort(422, 'Purchase request unit conversion is invalid.');
                        }

                        $requestedBaseQuantity =
                            (float) $requestItem->quantity *
                            (float) $requestUnit->conversion_factor;

                        $alreadyAllocated = DB::table('purchase_order_items')
                            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
                            ->where('purchase_order_items.purchase_request_item_id', $requestItemId)
                            ->where('purchase_orders.id', '!=', $purchaseOrder->id)
                            ->whereNotIn('purchase_orders.status', ['cancelled'])
                            ->sum('purchase_order_items.base_quantity');

                        $currentAllocated = collect($items)
                            ->where('purchase_request_item_id', $requestItemId)
                            ->sum(function ($allocatedItem) {
                                $details = $this->getPurchaseUnitDetails(
                                    $allocatedItem['product_id'],
                                    $allocatedItem['unit_id']
                                );

                                return (float) $allocatedItem['quantity'] *
                                    (float) ($details->conversion_factor ?? 0);
                            });

                        if (
                            $alreadyAllocated + $currentAllocated >
                            $requestedBaseQuantity + 0.00000001
                        ) {
                            abort(422, 'Purchase order quantity exceeds remaining purchase request quantity.');
                        }
                    } elseif (!empty($item['purchase_request_item_id'])) {
                        abort(422, 'Purchase request item cannot be selected for a direct purchase order.');
                    }

                    $preparedItems[] = [
                        'purchase_request_item_id' => $purchaseRequest
                            ? $item['purchase_request_item_id']
                            : null,
                        'product_id' => $item['product_id'],
                        'unit_id' => $item['unit_id'],
                        'quantity' => $quantity,
                        'conversion_factor' => $conversionFactor,
                        'base_quantity' => $baseQuantity,
                        'received_quantity' => 0,
                        'unit_cost' => $item['unit_cost'],
                        'discount_amount' => $discount,
                        'tax_amount' => $tax,
                        'total_amount' => $lineTotal,
                        'notes' => $item['notes'] ?? null,
                    ];

                    $subtotal += $gross;
                    $discountTotal += $discount;
                    $taxTotal += $tax;
                    $totalAmount += $lineTotal;
                }

                // Replace existing items only after all new items are validated.
                $purchaseOrder->items()->delete();
                $purchaseOrder->items()->createMany($preparedItems);

                $purchaseOrder->update([
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountTotal,
                    'tax_amount' => $taxTotal,
                    'total_amount' => $totalAmount,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchase order updated successfully.',
                'data' => $purchaseOrder->fresh()->load([
                    'supplier',
                    'branch',
                    'warehouse',
                    'purchaseRequest',
                    'items.product',
                    'items.unit',
                    'items.purchaseRequestItem',
                ]),
            ]);
        });
    }

    public function submit($id)
    {
        $purchaseOrder = PurchaseOrder::whereHas('branch', function ($q) {
            $q->where('company_id', $this->companyId());
        })->findOrFail($id);

        if (!$this->accessibleBranch($purchaseOrder->branch_id)) {
            abort(403, 'You do not have access to this branch.');
        }

        if ($purchaseOrder->status !== 'draft') {
            abort(422, 'Only draft purchase orders can be submitted.');
        }

        if (!$purchaseOrder->items()->exists()) {
            abort(422, 'Purchase order must have at least one item.');
        }

        $purchaseOrder->update([
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Purchase order submitted successfully.',
            'data' => $purchaseOrder->fresh()->load([
                'supplier',
                'branch',
                'warehouse',
                'items.product',
                'items.unit',
            ]),
        ]);
    }

    public function approve($id)
    {
        $purchaseOrder = PurchaseOrder::whereHas('branch', function ($q) {
            $q->where('company_id', $this->companyId());
        })->findOrFail($id);

        if (!$this->accessibleBranch($purchaseOrder->branch_id)) {
            abort(403, 'You do not have access to this branch.');
        }

        if ($purchaseOrder->status !== 'pending') {
            abort(422, 'Only pending purchase orders can be approved.');
        }

        $purchaseOrder->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Purchase order approved successfully.',
            'data' => $purchaseOrder->fresh()->load([
                'supplier',
                'branch',
                'warehouse',
                'creator',
                'approver',
                'items.product',
                'items.unit',
            ]),
        ]);
    }

    public function cancel($id)
    {
        $purchaseOrder = PurchaseOrder::whereHas('branch', function ($q) {
            $q->where('company_id', $this->companyId());
        })->findOrFail($id);

        if (!$this->accessibleBranch($purchaseOrder->branch_id)) {
            abort(403, 'You do not have access to this branch.');
        }

        if (!in_array($purchaseOrder->status, ['draft', 'pending', 'approved'])) {
            abort(422, 'This purchase order cannot be cancelled.');
        }

        return DB::transaction(function () use ($purchaseOrder) {

            $oldStatus = $purchaseOrder->status;

            $purchaseOrder->update([
                'status' => 'cancelled',
            ]);

            app(AuditService::class)->log(
                auditable: $purchaseOrder,
                action: 'cancelled',
                oldValues: [
                    'status' => $oldStatus,
                ],
                newValues: [
                    'status' => 'cancelled',
                ],
                description: 'Purchase order cancelled.'
            );

            return response()->json([
                'success' => true,
                'message' => 'Purchase order cancelled successfully.',
                'data' => $purchaseOrder->fresh()->load([
                    'supplier',
                    'branch',
                    'warehouse',
                    'items.product',
                    'items.unit',
                ]),
            ]);
        });
    }
}
