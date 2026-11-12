<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\SalesInvoice;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()
            ->branches()
            ->value('company_id');

        $invoices = SalesInvoice::query()
            ->with([
                'customer',
                'branch',
                'salesOrder',
                'creator',
                'items.product',
                'items.unit',
                'deliveries',
            ])
            ->whereHas('branch', function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                    ->whereIn(
                        'branches.id',
                        auth()->user()->branches()
                            ->select('branches.id')
                    );
            })
            ->when($request->filled('branch_id'), function ($query) use ($request) {
                $query->where('branch_id', $request->branch_id);
            })
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                $query->where('customer_id', $request->customer_id);
            })
            ->when($request->filled('sales_order_id'), function ($query) use ($request) {
                $query->where('sales_order_id', $request->sales_order_id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('from_date'), function ($query) use ($request) {
                $query->whereDate('invoice_date', '>=', $request->from_date);
            })
            ->when($request->filled('to_date'), function ($query) use ($request) {
                $query->whereDate('invoice_date', '<=', $request->to_date);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('salesOrder', function ($orderQuery) use ($search) {
                            $orderQuery->where('order_number', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    public function store(Request $request, AuditService $auditService)
    {
        $validated = $request->validate([
            'invoice_number' => ['required', 'string', 'max:255'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'notes' => ['nullable', 'string'],

            'deliveries' => ['required', 'array', 'min:1'],
            'deliveries.*' => ['required', 'uuid', 'distinct', 'exists:deliveries,id'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.delivery_item_id' => [
                'required',
                'uuid',
                'distinct',
                'exists:delivery_items,id',
            ],
        ]);

        $companyId = auth()->user()
            ->branches()
            ->value('company_id');

        $invoice = DB::transaction(function () use (
            $validated,
            $companyId,
            $auditService
        ) {
            // Lock and authorize selected deliveries
            $deliveries = Delivery::query()
                ->whereIn('id', $validated['deliveries'])
                ->whereHas('salesOrder.branch', function ($query) use ($companyId) {
                    $query->where('company_id', $companyId)
                        ->whereIn(
                            'branches.id',
                            auth()->user()->branches()->select('branches.id')
                        );
                })
                ->with(['salesOrder'])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($deliveries->count() !== count($validated['deliveries'])) {
                throw ValidationException::withMessages([
                    'deliveries' => 'One or more deliveries are invalid or inaccessible.',
                ]);
            }

            $firstDelivery = $deliveries->first();
            $firstOrder = $firstDelivery->salesOrder;

            foreach ($deliveries as $delivery) {

                if (!in_array($delivery->status, ['dispatched', 'delivered'])) {
                    throw ValidationException::withMessages([
                        'deliveries' => "Delivery {$delivery->delivery_number} must be dispatched or delivered before invoicing.",
                    ]);
                }

                $order = $delivery->salesOrder;

                if (
                    $order->customer_id !== $firstOrder->customer_id ||
                    $order->branch_id !== $firstOrder->branch_id ||
                    $order->currency !== $firstOrder->currency
                ) {
                    throw ValidationException::withMessages([
                        'deliveries' => 'All deliveries must belong to the same customer, branch and currency.',
                    ]);
                }
            }

            // Check invoice number uniqueness
            $invoiceExists = SalesInvoice::query()
                ->where('branch_id', $firstOrder->branch_id)
                ->where('invoice_number', $validated['invoice_number'])
                ->exists();

            if ($invoiceExists) {
                throw ValidationException::withMessages([
                    'invoice_number' => 'This invoice number already exists in the selected branch.',
                ]);
            }

            $deliveryItemIds = collect($validated['items'])
                ->pluck('delivery_item_id');

            // Lock selected delivery items and load their original order prices
            $deliveryItems = DeliveryItem::query()
                ->whereIn('id', $deliveryItemIds)
                ->with([
                    'delivery.salesOrder',
                    'salesOrderItem',
                ])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $preparedItems = [];

            $subtotal = 0;
            $totalDiscount = 0;
            $totalTax = 0;
            $totalAmount = 0;

            foreach ($validated['items'] as $requestedItem) {

                $deliveryItem = $deliveryItems->get(
                    $requestedItem['delivery_item_id']
                );

                if (!$deliveryItem) {
                    throw ValidationException::withMessages([
                        'items' => 'Selected delivery item was not found.',
                    ]);
                }

                if (!in_array($deliveryItem->delivery_id, $deliveries->pluck('id')->all())) {
                    throw ValidationException::withMessages([
                        'items' => 'A delivery item does not belong to the selected deliveries.',
                    ]);
                }

                $orderItem = $deliveryItem->salesOrderItem;

                if (!$orderItem) {
                    throw ValidationException::withMessages([
                        'items' => 'Original sales order item was not found.',
                    ]);
                }

                // Already invoiced quantity, excluding cancelled invoices
                $alreadyInvoiced = (float) DB::table('sales_invoice_items')
                    ->join(
                        'sales_invoices',
                        'sales_invoices.id',
                        '=',
                        'sales_invoice_items.sales_invoice_id'
                    )
                    ->where('sales_invoice_items.delivery_item_id', $deliveryItem->id)
                    ->where('sales_invoices.status', '!=', 'cancelled')
                    ->sum('sales_invoice_items.quantity');

                $remainingQuantity = round(
                    (float) $deliveryItem->quantity - $alreadyInvoiced,
                    8
                );

                $quantity = (float) $deliveryItem->quantity;

                if ($quantity > $remainingQuantity) {
                    throw ValidationException::withMessages([
                        'items' => "Invoice quantity exceeds the uninvoiced quantity for delivery item {$deliveryItem->id}.",
                    ]);
                }

                $conversionFactor = (float) $deliveryItem->conversion_factor;

                $baseQuantity = round(
                    $quantity * $conversionFactor,
                    8
                );

                $orderBaseQuantity = (float) $orderItem->base_quantity;

                if ($orderBaseQuantity <= 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Invalid sales order item base quantity.',
                    ]);
                }

                // Original agreed sales order price
                $unitPrice = (float) $orderItem->unit_price;

                $lineSubtotal = round($quantity * $unitPrice, 2);

                // Allocate order-level line discount and tax proportionally
                $discountAmount = round(
                    ((float) $orderItem->discount_amount / $orderBaseQuantity)
                    * $baseQuantity,
                    2
                );

                $taxAmount = round(
                    ((float) $orderItem->tax_amount / $orderBaseQuantity)
                    * $baseQuantity,
                    2
                );

                $lineTotal = round(
                    $lineSubtotal - $discountAmount + $taxAmount,
                    2
                );

                if ($lineTotal < 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Invoice item total cannot be negative.',
                    ]);
                }

                $preparedItems[] = [
                    'delivery_item_id' => $deliveryItem->id,
                    'product_id' => $deliveryItem->product_id,
                    'unit_id' => $deliveryItem->unit_id,
                    'quantity' => $quantity,
                    'conversion_factor' => $conversionFactor,
                    'base_quantity' => $baseQuantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $discountAmount,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $lineTotal,
                ];

                $subtotal += $lineSubtotal;
                $totalDiscount += $discountAmount;
                $totalTax += $taxAmount;
                $totalAmount += $lineTotal;
            }

            $subtotal = round($subtotal, 2);
            $totalDiscount = round($totalDiscount, 2);
            $totalTax = round($totalTax, 2);
            $totalAmount = round($totalAmount, 2);

            // Keep sales_order_id only when all selected deliveries belong to one order
            $salesOrderIds = $deliveries
                ->pluck('sales_order_id')
                ->unique()
                ->values();

            $invoice = SalesInvoice::create([
                'customer_id' => $firstOrder->customer_id,
                'branch_id' => $firstOrder->branch_id,
                'sales_order_id' => $salesOrderIds->count() === 1
                    ? $salesOrderIds->first()
                    : null,
                'invoice_number' => $validated['invoice_number'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'status' => 'draft',
                'currency' => $firstOrder->currency,
                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $totalTax,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'balance_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Create invoice items
            foreach ($preparedItems as $itemData) {

                $invoiceItem = $invoice->items()->create($itemData);

                $auditService->log(
                    $invoiceItem,
                    'created',
                    null,
                    $invoiceItem->getAttributes(),
                    'Sales invoice item created.',
                    [],
                    null,
                    $companyId,
                    $firstOrder->branch_id
                );
            }

            // Attach selected deliveries to invoice
            $invoice->deliveries()->attach($deliveries->pluck('id')->all());

            // Initial status history
            $invoice->statusHistories()->create([
                'old_status' => null,
                'new_status' => 'draft',
                'changed_by' => auth()->id(),
                'notes' => 'Sales invoice created.',
            ]);

            // Audit invoice
            $auditService->log(
                $invoice,
                'created',
                null,
                $invoice->getAttributes(),
                'Sales invoice created.',
                [],
                null,
                $companyId,
                $firstOrder->branch_id
            );

            return $invoice->load([
                'customer',
                'branch',
                'salesOrder',
                'creator',
                'items.product',
                'items.unit',
                'items.deliveryItem',
                'deliveries',
                'statusHistories.changedBy',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Sales invoice created successfully.',
            'data' => $invoice,
        ], 201);
    }

    public function show(string $id)
    {
        $user = auth()->user();

        $companyId = $user->branches()->value('company_id');

        $branchIds = $user->branches()
            ->pluck('branches.id');

        $invoice = SalesInvoice::with([
            'customer',
            'branch',
            'salesOrder',
            'creator',
            'items.product',
            'items.unit',
            'items.deliveryItem.delivery',
            'deliveries',
            'statusHistories.changedBy',
        ])
            ->whereKey($id)
            ->whereHas('branch', function ($query) use ($companyId, $branchIds) {
                $query->where('company_id', $companyId)
                    ->whereIn('id', $branchIds);
            })
            ->firstOrFail();

        return response()->json([
            'message' => 'Sales invoice details retrieved successfully.',
            'data' => $invoice,
        ]);
    }

    public function update(Request $request, string $id, AuditService $auditService)
    {
        $validated = $request->validate([
            'invoice_number' => ['required', 'string', 'max:255'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'notes' => ['nullable', 'string'],

            'deliveries' => ['required', 'array', 'min:1'],
            'deliveries.*' => [
                'required',
                'uuid',
                'distinct',
                'exists:deliveries,id',
            ],

            'items' => ['required', 'array', 'min:1'],
            'items.*.delivery_item_id' => [
                'required',
                'uuid',
                'distinct',
                'exists:delivery_items,id',
            ],
        ]);

        $user = auth()->user();

        $companyId = $user->branches()->value('company_id');

        $branchIds = $user->branches()
            ->pluck('branches.id');

        $invoice = DB::transaction(function () use (
            $validated,
            $id,
            $companyId,
            $branchIds,
            $auditService
        ) {

            // Lock invoice
            $invoice = SalesInvoice::query()
                ->whereKey($id)
                ->whereHas('branch', function ($query) use ($companyId, $branchIds) {
                    $query->where('company_id', $companyId)
                        ->whereIn('branches.id', $branchIds);
                })
                ->lockForUpdate()
                ->firstOrFail();

            // Only draft invoices can be updated
            if ($invoice->status !== 'draft') {
                throw ValidationException::withMessages([
                    'invoice' => 'Only draft sales invoices can be updated.',
                ]);
            }

            $oldInvoiceValues = $invoice->getAttributes();

            // Lock and authorize selected deliveries
            $deliveries = Delivery::query()
                ->whereIn('id', $validated['deliveries'])
                ->whereHas('salesOrder.branch', function ($query) use ($companyId, $branchIds) {
                    $query->where('company_id', $companyId)
                        ->whereIn('branches.id', $branchIds);
                })
                ->with('salesOrder')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($deliveries->count() !== count($validated['deliveries'])) {
                throw ValidationException::withMessages([
                    'deliveries' => 'One or more deliveries are invalid or inaccessible.',
                ]);
            }

            $firstDelivery = $deliveries->first();
            $firstOrder = $firstDelivery->salesOrder;

            foreach ($deliveries as $delivery) {

                if (!in_array($delivery->status, ['dispatched', 'delivered'])) {
                    throw ValidationException::withMessages([
                        'deliveries' => "Delivery {$delivery->delivery_number} must be dispatched or delivered before invoicing.",
                    ]);
                }

                $order = $delivery->salesOrder;

                // Keep invoice customer, branch and currency unchanged
                if (
                    $order->customer_id !== $invoice->customer_id ||
                    $order->branch_id !== $invoice->branch_id ||
                    $order->currency !== $invoice->currency
                ) {
                    throw ValidationException::withMessages([
                        'deliveries' => 'All deliveries must belong to the same customer, branch and currency as this invoice.',
                    ]);
                }
            }

            // Check invoice number uniqueness
            $invoiceExists = SalesInvoice::query()
                ->where('branch_id', $invoice->branch_id)
                ->where('invoice_number', $validated['invoice_number'])
                ->where('id', '!=', $invoice->id)
                ->exists();

            if ($invoiceExists) {
                throw ValidationException::withMessages([
                    'invoice_number' => 'This invoice number already exists in the selected branch.',
                ]);
            }

            $deliveryItemIds = collect($validated['items'])
                ->pluck('delivery_item_id');

            // Lock selected delivery items
            $deliveryItems = DeliveryItem::query()
                ->whereIn('id', $deliveryItemIds)
                ->with([
                    'delivery.salesOrder',
                    'salesOrderItem',
                ])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $preparedItems = [];

            $subtotal = 0;
            $totalDiscount = 0;
            $totalTax = 0;
            $totalAmount = 0;

            foreach ($validated['items'] as $requestedItem) {

                $deliveryItem = $deliveryItems->get(
                    $requestedItem['delivery_item_id']
                );

                if (!$deliveryItem) {
                    throw ValidationException::withMessages([
                        'items' => 'Selected delivery item was not found.',
                    ]);
                }

                if (!in_array($deliveryItem->delivery_id, $deliveries->pluck('id')->all())) {
                    throw ValidationException::withMessages([
                        'items' => 'A delivery item does not belong to the selected deliveries.',
                    ]);
                }

                $orderItem = $deliveryItem->salesOrderItem;

                if (!$orderItem) {
                    throw ValidationException::withMessages([
                        'items' => 'Original sales order item was not found.',
                    ]);
                }

                // Already invoiced quantity, excluding cancelled invoices
                // Also exclude current invoice because its items are being replaced
                $alreadyInvoiced = (float) DB::table('sales_invoice_items')
                    ->join(
                        'sales_invoices',
                        'sales_invoices.id',
                        '=',
                        'sales_invoice_items.sales_invoice_id'
                    )
                    ->where('sales_invoice_items.delivery_item_id', $deliveryItem->id)
                    ->where('sales_invoices.status', '!=', 'cancelled')
                    ->where('sales_invoices.id', '!=', $invoice->id)
                    ->sum('sales_invoice_items.quantity');

                $remainingQuantity = round(
                    (float) $deliveryItem->quantity - $alreadyInvoiced,
                    8
                );

                if ($remainingQuantity <= 0) {
                    throw ValidationException::withMessages([
                        'items' => "No uninvoiced quantity remains for delivery item {$deliveryItem->id}.",
                    ]);
                }

                // Automatically invoice the remaining quantity
                $quantity = $remainingQuantity;

                $conversionFactor = (float) $deliveryItem->conversion_factor;

                $baseQuantity = round(
                    $quantity * $conversionFactor,
                    8
                );

                $orderBaseQuantity = (float) $orderItem->base_quantity;

                if ($orderBaseQuantity <= 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Invalid sales order item base quantity.',
                    ]);
                }

                // Original agreed sales order price
                $unitPrice = (float) $orderItem->unit_price;

                $lineSubtotal = round($quantity * $unitPrice, 2);

                // Allocate order item discount and tax proportionally
                $discountAmount = round(
                    ((float) $orderItem->discount_amount / $orderBaseQuantity)
                    * $baseQuantity,
                    2
                );

                $taxAmount = round(
                    ((float) $orderItem->tax_amount / $orderBaseQuantity)
                    * $baseQuantity,
                    2
                );

                $lineTotal = round(
                    $lineSubtotal - $discountAmount + $taxAmount,
                    2
                );

                if ($lineTotal < 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Invoice item total cannot be negative.',
                    ]);
                }

                $preparedItems[] = [
                    'delivery_item_id' => $deliveryItem->id,
                    'product_id' => $deliveryItem->product_id,
                    'unit_id' => $deliveryItem->unit_id,
                    'quantity' => $quantity,
                    'conversion_factor' => $conversionFactor,
                    'base_quantity' => $baseQuantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $discountAmount,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $lineTotal,
                ];

                $subtotal += $lineSubtotal;
                $totalDiscount += $discountAmount;
                $totalTax += $taxAmount;
                $totalAmount += $lineTotal;
            }

            $subtotal = round($subtotal, 2);
            $totalDiscount = round($totalDiscount, 2);
            $totalTax = round($totalTax, 2);
            $totalAmount = round($totalAmount, 2);

            // Preserve existing paid amount and recalculate balance
            $paidAmount = (float) $invoice->paid_amount;
            $balanceAmount = round($totalAmount - $paidAmount, 2);

            if ($balanceAmount < 0) {
                throw ValidationException::withMessages([
                    'items' => 'Updated invoice total cannot be less than the amount already paid.',
                ]);
            }

            // Audit and remove old invoice items
            $invoice->load('items');

            foreach ($invoice->items as $oldItem) {

                $auditService->log(
                    $oldItem,
                    'deleted',
                    $oldItem->getAttributes(),
                    null,
                    'Sales invoice item removed during invoice update.',
                    [],
                    null,
                    $companyId,
                    $invoice->branch_id
                );

                $oldItem->delete();
            }

            // Determine linked sales order
            $salesOrderIds = $deliveries
                ->pluck('sales_order_id')
                ->unique()
                ->values();

            // Update invoice
            $invoice->update([
                'sales_order_id' => $salesOrderIds->count() === 1
                    ? $salesOrderIds->first()
                    : null,

                'invoice_number' => $validated['invoice_number'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $totalTax,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_amount' => $balanceAmount,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create updated invoice items
            foreach ($preparedItems as $itemData) {

                $invoiceItem = $invoice->items()->create($itemData);

                $auditService->log(
                    $invoiceItem,
                    'created',
                    null,
                    $invoiceItem->getAttributes(),
                    'Sales invoice item created during invoice update.',
                    [],
                    null,
                    $companyId,
                    $invoice->branch_id
                );
            }

            // Synchronize invoice deliveries
            $invoice->deliveries()->sync(
                $deliveries->pluck('id')->all()
            );

            // Audit invoice update
            $auditService->log(
                $invoice,
                'updated',
                $oldInvoiceValues,
                $invoice->getAttributes(),
                'Sales invoice updated.',
                [],
                null,
                $companyId,
                $invoice->branch_id
            );

            return $invoice->load([
                'customer',
                'branch',
                'salesOrder',
                'creator',
                'items.product',
                'items.unit',
                'items.deliveryItem.delivery',
                'deliveries',
                'statusHistories.changedBy',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Sales invoice updated successfully.',
            'data' => $invoice,
        ]);
    }

    public function approve(string $id, AuditService $auditService)
    {
        $user = auth()->user();

        $companyId = $user->branches()->value('company_id');

        $branchIds = $user->branches()
            ->pluck('branches.id');

        $invoice = DB::transaction(function () use (
            $id,
            $companyId,
            $branchIds,
            $auditService,
            $user
        ) {

            $invoice = SalesInvoice::query()
                ->whereKey($id)
                ->whereHas('branch', function ($query) use ($companyId, $branchIds) {
                    $query->where('company_id', $companyId)
                        ->whereIn('branches.id', $branchIds);
                })
                ->lockForUpdate()
                ->firstOrFail();

            if ($invoice->status !== 'draft') {
                throw ValidationException::withMessages([
                    'invoice' => 'Only draft sales invoices can be approved.',
                ]);
            }

            if (!$invoice->items()->exists()) {
                throw ValidationException::withMessages([
                    'invoice' => 'An invoice without items cannot be approved.',
                ]);
            }

            $oldValues = $invoice->getAttributes();

            $invoice->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            // Status history
            $invoice->statusHistories()->create([
                'old_status' => 'draft',
                'new_status' => 'approved',
                'changed_by' => $user->id,
                'notes' => 'Sales invoice approved.',
            ]);

            // Audit log
            $auditService->log(
                $invoice,
                'approved',
                $oldValues,
                $invoice->getAttributes(),
                'Sales invoice approved.',
                [],
                null,
                $companyId,
                $invoice->branch_id
            );

            return $invoice->load([
                'customer',
                'branch',
                'salesOrder',
                'creator',
                'approver',
                'items.product',
                'items.unit',
                'deliveries',
                'statusHistories.changedBy',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Sales invoice approved successfully.',
            'data' => $invoice,
        ]);
    }

    public function cancel(string $id, AuditService $auditService)
    {
        $user = auth()->user();

        $companyId = $user->branches()->value('company_id');

        $branchIds = $user->branches()
            ->pluck('branches.id');

        $invoice = DB::transaction(function () use (
            $id,
            $companyId,
            $branchIds,
            $auditService,
            $user
        ) {

            $invoice = SalesInvoice::query()
                ->whereKey($id)
                ->whereHas('branch', function ($query) use ($companyId, $branchIds) {
                    $query->where('company_id', $companyId)
                        ->whereIn('branches.id', $branchIds);
                })
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($invoice->status, ['draft', 'approved'])) {
                throw ValidationException::withMessages([
                    'invoice' => 'Only draft or approved invoices can be cancelled.',
                ]);
            }

            if ((float) $invoice->paid_amount > 0) {
                throw ValidationException::withMessages([
                    'invoice' => 'An invoice with received payments cannot be cancelled.',
                ]);
            }

            if ($invoice->paymentAllocations()->exists()) {
                throw ValidationException::withMessages([
                    'invoice' => 'Remove existing payment allocations before cancelling this invoice.',
                ]);
            }

            $oldValues = $invoice->getAttributes();
            $oldStatus = $invoice->status;

            $invoice->update([
                'status' => 'cancelled',
            ]);

            $invoice->statusHistories()->create([
                'old_status' => $oldStatus,
                'new_status' => 'cancelled',
                'changed_by' => $user->id,
                'notes' => 'Sales invoice cancelled.',
            ]);

            $auditService->log(
                $invoice,
                'cancelled',
                $oldValues,
                $invoice->getAttributes(),
                'Sales invoice cancelled.',
                [],
                null,
                $companyId,
                $invoice->branch_id
            );

            return $invoice->load([
                'customer',
                'branch',
                'salesOrder',
                'creator',
                'approver',
                'items.product',
                'items.unit',
                'deliveries',
                'statusHistories.changedBy',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Sales invoice cancelled successfully.',
            'data' => $invoice,
        ]);
    }

}