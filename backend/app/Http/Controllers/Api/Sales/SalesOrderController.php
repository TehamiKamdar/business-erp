<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\ProductPrice;
use App\Models\ProductUnit;
use App\Models\SalesOrder;
use App\Models\SalesOrderStatusHistory;
use App\Models\Warehouse;
use App\Services\AuditService;
use App\Services\StockReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesOrderController extends Controller
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
        $companyId = $this->companyId();

        $query = SalesOrder::query()
            ->with([
                'customer',
                'branch',
                'warehouse',
                'creator',
                'approver',
                'items.product',
                'items.unit',
            ])
            ->whereHas('branch', function ($q) use ($companyId) {
                $q->where('company_id', $companyId)
                    ->whereIn(
                        'branches.id',
                        auth()->user()->branches()
                            ->select('branches.id')
                    );
            });

        // Branch filter
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Warehouse filter
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Customer filter
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Order number search
        if ($request->filled('search')) {
            $query->where(
                'order_number',
                'like',
                '%' . $request->search . '%'
            );
        }

        $orders = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    public function store(Request $request, AuditService $auditService)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'uuid', 'exists:customers,id'],
            'branch_id' => ['required', 'uuid', 'exists:branches,id'],
            'warehouse_id' => ['required', 'uuid', 'exists:warehouses,id'],

            'quotation_id' => ['nullable', 'uuid', 'exists:quotations,id'],

            'order_number' => ['required', 'string', 'max:255'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => [
                'nullable',
                'date',
                'after_or_equal:order_date',
            ],

            'currency' => ['nullable', 'string', 'size:3'],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],

            'items.*.product_id' => [
                'required',
                'uuid',
                'exists:products,id',
            ],

            'items.*.unit_id' => [
                'required',
                'uuid',
                'exists:units,id',
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
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

            'items.*.notes' => [
                'nullable',
                'string',
            ],
        ]);

        $companyId = $this->companyId();

        // Company validation
        if (!$companyId) {
            throw ValidationException::withMessages([
                'company' => 'No company is associated with this user.',
            ]);
        }

        // Branch access validation
        if (!$this->accessibleBranch($validated['branch_id'])) {
            throw ValidationException::withMessages([
                'branch_id' => 'You do not have access to this branch.',
            ]);
        }

        // Customer company validation
        $customerExists = Customer::query()
            ->where('id', $validated['customer_id'])
            ->where('company_id', $companyId)
            ->exists();

        if (!$customerExists) {
            throw ValidationException::withMessages([
                'customer_id' => 'Invalid customer for this company.',
            ]);
        }

        // Warehouse branch validation
        $warehouseExists = Warehouse::query()
            ->where('id', $validated['warehouse_id'])
            ->where('branch_id', $validated['branch_id'])
            ->exists();

        if (!$warehouseExists) {
            throw ValidationException::withMessages([
                'warehouse_id' => 'Warehouse does not belong to the selected branch.',
            ]);
        }

        // Order number uniqueness
        $orderExists = SalesOrder::query()
            ->where('branch_id', $validated['branch_id'])
            ->where('order_number', $validated['order_number'])
            ->exists();

        if ($orderExists) {
            throw ValidationException::withMessages([
                'order_number' => 'This order number already exists in this branch.',
            ]);
        }

        $order = DB::transaction(function () use (
            $validated,
            $companyId,
            $auditService
        ) {

            $subtotal = 0;
            $totalDiscount = 0;
            $totalTax = 0;
            $totalAmount = 0;

            $preparedItems = [];

            /*
            |--------------------------------------------------------------------------
            | Validate Products, Units and Fetch Prices
            |--------------------------------------------------------------------------
            */

            foreach ($validated['items'] as $index => $item) {

                $productUnit = ProductUnit::query()
                    ->where('product_id', $item['product_id'])
                    ->where('unit_id', $item['unit_id'])
                    ->where('is_sale_unit', true)
                    ->where('status', 'active')
                    ->first();

                if (!$productUnit) {
                    throw ValidationException::withMessages([
                        "items.$index.unit_id" =>
                            'Selected unit is not an active sale unit for this product.',
                    ]);
                }

                $quantity = (float) $item['quantity'];

                // Fetch applicable price based on quantity and price type
                $productPrice = ProductPrice::query()
                    ->where('product_unit_id', $productUnit->id)
                    ->where('minimum_quantity', '<=', $quantity)
                    ->where('effective_from', '<=', $validated['order_date'])
                    ->where('status', 'active')
                    ->orderByDesc('minimum_quantity')
                    ->orderByDesc('effective_from')
                    ->first();

                if (!$productPrice) {
                    throw ValidationException::withMessages([
                        "items.$index.price_type" =>
                            'No applicable product price found for the selected quantity and price type.',
                    ]);
                }

                $conversionFactor = (float) $productUnit->conversion_factor;

                $baseQuantity = round(
                    $quantity * $conversionFactor,
                    8
                );

                $unitPrice = (float) $productPrice->price;
                $priceType = $productPrice->price_type;

                $grossAmount = round(
                    $quantity * $unitPrice,
                    2
                );

                $discountAmount = round(
                    (float) ($item['discount_amount'] ?? 0),
                    2
                );

                $taxAmount = round(
                    (float) ($item['tax_amount'] ?? 0),
                    2
                );

                if ($discountAmount > $grossAmount) {
                    throw ValidationException::withMessages([
                        "items.$index.discount_amount" =>
                            'Discount cannot exceed the gross amount.',
                    ]);
                }

                $lineTotal = round(
                    $grossAmount - $discountAmount + $taxAmount,
                    2
                );

                $subtotal += $grossAmount;
                $totalDiscount += $discountAmount;
                $totalTax += $taxAmount;
                $totalAmount += $lineTotal;

                $preparedItems[] = [
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'quantity' => $quantity,
                    'conversion_factor' => $conversionFactor,
                    'base_quantity' => $baseQuantity,
                    'delivered_quantity' => 0,
                    'unit_price' => $unitPrice,
                    'price_type' => $priceType,
                    'discount_amount' => $discountAmount,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $lineTotal,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Create Sales Order
            |--------------------------------------------------------------------------
            */

            $salesOrder = SalesOrder::create([
                'customer_id' => $validated['customer_id'],
                'branch_id' => $validated['branch_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'quotation_id' => $validated['quotation_id'] ?? null,

                'order_number' => $validated['order_number'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' =>
                    $validated['expected_delivery_date'] ?? null,

                'status' => 'draft',

                'currency' => $validated['currency'] ?? 'PKR',

                'subtotal' => round($subtotal, 2),
                'discount_amount' => round($totalDiscount, 2),
                'tax_amount' => round($totalTax, 2),
                'total_amount' => round($totalAmount, 2),

                'notes' => $validated['notes'] ?? null,

                'created_by' => auth()->id(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create Sales Order Items
            |--------------------------------------------------------------------------
            */

            foreach ($preparedItems as $item) {

                $orderItem = $salesOrder->items()->create($item);

                // Audit individual item
                $auditService->log(
                    auditable: $orderItem,
                    action: 'created',
                    newValues: $orderItem->getAttributes(),
                    description: 'Sales order item created.',
                    metadata: [
                        'sales_order_id' => $salesOrder->id,
                        'order_number' => $salesOrder->order_number,
                    ],
                    companyId: $companyId,
                    branchId: $salesOrder->branch_id
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Initial Status History
            |--------------------------------------------------------------------------
            */

            SalesOrderStatusHistory::create([
                'sales_order_id' => $salesOrder->id,
                'old_status' => null,
                'new_status' => 'draft',
                'changed_by' => auth()->id(),
                'notes' => 'Sales order created.',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Audit Sales Order
            |--------------------------------------------------------------------------
            */

            $auditService->log(
                auditable: $salesOrder,
                action: 'created',
                newValues: $salesOrder->getAttributes(),
                description: 'Sales order created successfully.',
                metadata: [
                    'items_count' => count($preparedItems),
                    'total_amount' => round($totalAmount, 2),
                    'currency' => $salesOrder->currency,
                ],
                companyId: $companyId,
                branchId: $salesOrder->branch_id
            );

            return $salesOrder;
        });

        /*
        |--------------------------------------------------------------------------
        | Return Created Sales Order
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Sales order created successfully.',
            'data' => $order->load([
                'customer',
                'branch',
                'warehouse',
                'creator',
                'items.product',
                'items.unit',
            ]),
        ], 201);
    }

    public function show(string $salesOrderId)
    {
        $companyId = $this->companyId();

        $salesOrder = SalesOrder::query()
            ->with([
                'customer',
                'branch',
                'warehouse',
                'creator',
                'approver',
                'items.product',
                'items.unit',
                'statusHistories.changedBy',
            ])
            ->whereKey($salesOrderId)
            ->whereHas('branch', function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                    ->whereIn(
                        'branches.id',
                        auth()->user()->branches()
                            ->select('branches.id')
                    );
            })
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $salesOrder,
        ]);
    }

    public function approve(
        string $salesOrderId,
        AuditService $auditService,
        StockReservationService $stockReservationService
    ) {
        $companyId = $this->companyId();

        if (!$companyId) {
            throw ValidationException::withMessages([
                'company' => 'No company is associated with this user.',
            ]);
        }

        $salesOrder = DB::transaction(function () use (
            $salesOrderId,
            $companyId,
            $auditService,
            $stockReservationService
        ) {

            // Lock Sales Order
            $salesOrder = SalesOrder::query()
                ->whereKey($salesOrderId)
                ->lockForUpdate()
                ->firstOrFail();

            // Branch access validation
            if (!$this->accessibleBranch($salesOrder->branch_id)) {
                throw ValidationException::withMessages([
                    'branch_id' => 'You do not have access to this branch.',
                ]);
            }

            // Only draft or pending orders can be approved
            if (!in_array($salesOrder->status, ['draft', 'pending'])) {
                throw ValidationException::withMessages([
                    'status' => 'Only draft or pending sales orders can be approved.',
                ]);
            }

            $oldStatus = $salesOrder->status;

            // Update approval details
            $salesOrder->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Refresh model before passing to reservation service
            $salesOrder->refresh();

            // Reserve stock using existing service
            $stockReservationService->reserveForSalesOrder($salesOrder);

            // Record status history
            SalesOrderStatusHistory::create([
                'sales_order_id' => $salesOrder->id,
                'old_status' => $oldStatus,
                'new_status' => 'approved',
                'changed_by' => auth()->id(),
                'notes' => 'Sales order approved and stock reserved.',
            ]);

            // Audit Sales Order approval
            $auditService->log(
                auditable: $salesOrder,
                action: 'approved',
                oldValues: [
                    'status' => $oldStatus,
                    'approved_by' => null,
                    'approved_at' => null,
                ],
                newValues: [
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => $salesOrder->approved_at,
                ],
                description: 'Sales order approved and stock reserved.',
                metadata: [
                    'order_number' => $salesOrder->order_number,
                ],
                companyId: $companyId,
                branchId: $salesOrder->branch_id
            );

            return $salesOrder;
        });

        return response()->json([
            'success' => true,
            'message' => 'Sales order approved and stock reserved successfully.',
            'data' => $salesOrder->load([
                'customer',
                'branch',
                'warehouse',
                'creator',
                'approver',
                'items.product',
                'items.unit',
            ]),
        ]);
    }
    
    public function reject(
        Request $request,
        string $salesOrderId,
        AuditService $auditService
    ) {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $companyId = $this->companyId();

        return DB::transaction(function () use (
            $salesOrderId,
            $validated,
            $auditService,
            $companyId
        ) {
            $salesOrder = SalesOrder::query()
                ->whereKey($salesOrderId)
                ->whereHas('branch', function ($query) use ($companyId) {
                    $query->where('company_id', $companyId)
                        ->whereIn(
                            'branches.id',
                            auth()->user()->branches()
                                ->select('branches.id')
                        );
                })
                ->lockForUpdate()
                ->firstOrFail();

            if ($salesOrder->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Only draft sales orders can be rejected.',
                ]);
            }

            $oldValues = [
                'status' => $salesOrder->status,
            ];

            $salesOrder->update([
                'status' => 'rejected',
            ]);

            SalesOrderStatusHistory::create([
                'sales_order_id' => $salesOrder->id,
                'old_status' => 'draft',
                'new_status' => 'rejected',
                'changed_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            $auditService->log(
                $salesOrder,
                'rejected',
                $oldValues,
                ['status' => 'rejected'],
                'Sales order rejected.',
                [
                    'notes' => $validated['notes'] ?? null,
                ],
                null,
                $companyId,
                $salesOrder->branch_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Sales order rejected successfully.',
                'data' => $salesOrder->fresh(),
            ]);
        });
    }

}
