<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryStatusHistory;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\StockReservation;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()
            ->branches()
            ->value('company_id');

        $deliveries = Delivery::query()
            ->with([
                'salesOrder.customer',
                'salesOrder.branch',
                'warehouse',
                'creator',
                'items.product',
                'items.unit',
            ])
            ->whereHas('salesOrder.branch', function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                    ->whereIn(
                        'branches.id',
                        auth()->user()->branches()
                            ->select('branches.id')
                    );
            })

            // Branch filter
            ->when($request->filled('branch_id'), function ($query) use ($request) {
                $query->whereHas('salesOrder', function ($salesQuery) use ($request) {
                    $salesQuery->where('branch_id', $request->branch_id);
                });
            })

            // Warehouse filter
            ->when($request->filled('warehouse_id'), function ($query) use ($request) {
                $query->where('warehouse_id', $request->warehouse_id);
            })

            // Sales Order filter
            ->when($request->filled('sales_order_id'), function ($query) use ($request) {
                $query->where('sales_order_id', $request->sales_order_id);
            })

            // Status filter
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })

            // Date range filter
            ->when($request->filled('from_date'), function ($query) use ($request) {
                $query->whereDate('delivery_date', '>=', $request->from_date);
            })

            ->when($request->filled('to_date'), function ($query) use ($request) {
                $query->whereDate('delivery_date', '<=', $request->to_date);
            })

            // Search
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('delivery_number', 'like', "%{$search}%")
                        ->orWhereHas('salesOrder', function ($salesQuery) use ($search) {
                            $salesQuery->where('order_number', 'like', "%{$search}%");
                        });
                });
            })

            ->latest()
            ->paginate(15)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $deliveries,
        ]);
    }

    public function store(Request $request, AuditService $auditService)
    {
        $validated = $request->validate([
            'sales_order_id' => ['required', 'uuid', 'exists:sales_orders,id'],
            'warehouse_id' => ['required', 'uuid', 'exists:warehouses,id'],
            'delivery_number' => ['required', 'string', 'max:255'],
            'delivery_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.sales_order_item_id' => [
                'required',
                'uuid',
                'distinct',
            ],
            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        $companyId = auth()->user()->branches()->value('company_id');

        return DB::transaction(function () use (
            $validated,
            $auditService,
            $companyId
        ) {

            $salesOrder = SalesOrder::query()
                ->whereKey($validated['sales_order_id'])
                ->whereHas('branch', function ($query) use ($companyId) {
                    $query->where('company_id', $companyId)
                        ->whereIn(
                            'branches.id',
                            auth()->user()->branches()->select('branches.id')
                        );
                })
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($salesOrder->status, [
                'approved',
                'partially_delivered',
            ])) {
                throw ValidationException::withMessages([
                    'sales_order_id' => 'Only approved or partially delivered sales orders can create deliveries.',
                ]);
            }

            if ($validated['warehouse_id'] !== $salesOrder->warehouse_id) {
                throw ValidationException::withMessages([
                    'warehouse_id' => 'Delivery warehouse must match the sales order warehouse.',
                ]);
            }

            // Check duplicate delivery number.
            $numberExists = Delivery::query()
                ->where('warehouse_id', $validated['warehouse_id'])
                ->where('delivery_number', $validated['delivery_number'])
                ->exists();

            if ($numberExists) {
                throw ValidationException::withMessages([
                    'delivery_number' => 'This delivery number already exists in the selected warehouse.',
                ]);
            }

            $orderItems = $salesOrder->items()
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($orderItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Sales order has no items.',
                ]);
            }

            // Calculate quantities already included in other active deliveries.
            // Cancelled deliveries are excluded.
            $alreadyDelivered = DB::table('delivery_items')
                ->join('deliveries', 'deliveries.id', '=', 'delivery_items.delivery_id')
                ->where('deliveries.sales_order_id', $salesOrder->id)
                ->where('deliveries.status', '!=', 'cancelled')
                ->select(
                    'delivery_items.sales_order_item_id',
                    DB::raw('SUM(delivery_items.base_quantity) as total_quantity')
                )
                ->groupBy('delivery_items.sales_order_item_id')
                ->pluck('total_quantity', 'sales_order_item_id');

            $preparedItems = [];

            foreach ($validated['items'] as $index => $item) {

                $orderItem = $orderItems->get($item['sales_order_item_id']);

                if (!$orderItem) {
                    throw ValidationException::withMessages([
                        "items.{$index}.sales_order_item_id" =>
                            'Selected item does not belong to this sales order.',
                    ]);
                }

                $quantity = (float) $item['quantity'];

                $conversionFactor = (float) $orderItem->conversion_factor;

                $baseQuantity = round(
                    $quantity * $conversionFactor,
                    8
                );

                $alreadyAllocated = (float) (
                    $alreadyDelivered[$orderItem->id] ?? 0
                );

                $remainingQuantity = round(
                    (float) $orderItem->base_quantity - $alreadyAllocated,
                    8
                );

                if ($baseQuantity > $remainingQuantity) {
                    throw ValidationException::withMessages([
                        "items.{$index}.quantity" =>
                            "Delivery quantity exceeds remaining order quantity. Remaining base quantity: {$remainingQuantity}.",
                    ]);
                }

                $preparedItems[] = [
                    'sales_order_item_id' => $orderItem->id,
                    'product_id' => $orderItem->product_id,
                    'unit_id' => $orderItem->unit_id,
                    'quantity' => $quantity,
                    'conversion_factor' => $conversionFactor,
                    'base_quantity' => $baseQuantity,
                    'notes' => $item['notes'] ?? null,
                ];

                // Include this delivery's quantity when processing subsequent items.
                $alreadyDelivered[$orderItem->id] =
                    $alreadyAllocated + $baseQuantity;
            }

            $delivery = Delivery::create([
                'sales_order_id' => $salesOrder->id,
                'warehouse_id' => $salesOrder->warehouse_id,
                'delivery_number' => $validated['delivery_number'],
                'delivery_date' => $validated['delivery_date'],
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($preparedItems as $item) {

                $deliveryItem = $delivery->items()->create($item);

                $auditService->log(
                    $deliveryItem,
                    'created',
                    null,
                    $deliveryItem->getAttributes(),
                    'Delivery item created.',
                    [
                        'delivery_id' => $delivery->id,
                        'sales_order_id' => $salesOrder->id,
                    ],
                    null,
                    $companyId,
                    $salesOrder->branch_id
                );
            }

            DeliveryStatusHistory::create([
                'delivery_id' => $delivery->id,
                'old_status' => null,
                'new_status' => 'draft',
                'changed_by' => auth()->id(),
                'notes' => 'Delivery created.',
            ]);

            $auditService->log(
                $delivery,
                'created',
                null,
                $delivery->getAttributes(),
                'Draft delivery created.',
                [
                    'sales_order_id' => $salesOrder->id,
                    'delivery_number' => $delivery->delivery_number,
                ],
                null,
                $companyId,
                $salesOrder->branch_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Draft delivery created successfully.',
                'data' => $delivery->load([
                    'salesOrder.customer',
                    'warehouse',
                    'items.product',
                    'items.unit',
                ]),
            ], 201);
        });
    }

    public function show(string $id)
    {
        $companyId = auth()->user()
            ->branches()
            ->value('company_id');

        $delivery = Delivery::query()
            ->with([
                'salesOrder.customer',
                'salesOrder.branch',
                'salesOrder.warehouse',
                'salesOrder.items.product',
                'salesOrder.items.unit',
                'warehouse',
                'creator',
                'items.product',
                'items.unit',
                'items.salesOrderItem',
                'statusHistories.changedBy',
            ])
            ->whereHas('salesOrder.branch', function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                    ->whereIn(
                        'branches.id',
                        auth()->user()->branches()
                            ->select('branches.id')
                    );
            })
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $delivery,
        ]);
    }
    
    public function update(Request $request, string $id, AuditService $auditService)
    {
        $validated = $request->validate([
            'delivery_date' => ['sometimes', 'required', 'date'],
            'notes' => ['nullable', 'string'],

            'items' => ['sometimes', 'required', 'array', 'min:1'],

            'items.*.sales_order_item_id' => [
                'required',
                'uuid',
                'distinct',
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'items.*.notes' => ['nullable', 'string'],
        ]);

        $companyId = auth()->user()
            ->branches()
            ->value('company_id');

        $delivery = DB::transaction(function () use (
            $id,
            $validated,
            $companyId,
            $auditService
        ) {
            $delivery = Delivery::query()
                ->whereHas('salesOrder.branch', function ($query) use ($companyId) {
                    $query->where('company_id', $companyId)
                        ->whereIn(
                            'branches.id',
                            auth()->user()->branches()->select('branches.id')
                        );
                })
                ->lockForUpdate()
                ->findOrFail($id);

            if ($delivery->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Only draft deliveries can be updated.',
                ]);
            }

            $salesOrder = SalesOrder::query()
                ->with('items')
                ->findOrFail($delivery->sales_order_id);

            $existingItems = $delivery->items()
                ->lockForUpdate()
                ->get();

            $oldDeliveryValues = $delivery->getOriginal();

            if (array_key_exists('delivery_date', $validated)) {
                $delivery->delivery_date = $validated['delivery_date'];
            }

            if (array_key_exists('notes', $validated)) {
                $delivery->notes = $validated['notes'];
            }

            if (isset($validated['items'])) {

                $orderItems = $salesOrder->items->keyBy('id');

                $alreadyDelivered = DB::table('delivery_items')
                    ->join('deliveries', 'deliveries.id', '=', 'delivery_items.delivery_id')
                    ->where('deliveries.sales_order_id', $salesOrder->id)
                    ->where('deliveries.status', '!=', 'cancelled')
                    ->where('deliveries.id', '!=', $delivery->id)
                    ->select(
                        'delivery_items.sales_order_item_id',
                        DB::raw('SUM(delivery_items.base_quantity) as total_base_quantity')
                    )
                    ->groupBy('delivery_items.sales_order_item_id')
                    ->pluck('total_base_quantity', 'sales_order_item_id');

                $preparedItems = [];

                foreach ($validated['items'] as $item) {

                    $orderItem = $orderItems->get($item['sales_order_item_id']);

                    if (!$orderItem) {
                        throw ValidationException::withMessages([
                            'items' => 'Selected item does not belong to this sales order.',
                        ]);
                    }

                    $baseQuantity = round(
                        $item['quantity'] * (float) $orderItem->conversion_factor,
                        8
                    );

                    $previouslyAllocated = (float) ($alreadyDelivered[$orderItem->id] ?? 0);

                    $remainingQuantity = round(
                        (float) $orderItem->base_quantity - $previouslyAllocated,
                        8
                    );

                    if ($baseQuantity > $remainingQuantity) {
                        throw ValidationException::withMessages([
                            'items' => "Delivery quantity exceeds remaining quantity for product {$orderItem->product_id}.",
                        ]);
                    }

                    $preparedItems[] = [
                        'sales_order_item_id' => $orderItem->id,
                        'product_id' => $orderItem->product_id,
                        'unit_id' => $orderItem->unit_id,
                        'quantity' => $item['quantity'],
                        'conversion_factor' => $orderItem->conversion_factor,
                        'base_quantity' => $baseQuantity,
                        'notes' => $item['notes'] ?? null,
                    ];
                }

                foreach ($existingItems as $existingItem) {

                    $oldValues = $existingItem->getAttributes();

                    $existingItem->delete();

                    $auditService->log(
                        $existingItem,
                        'deleted',
                        $oldValues,
                        null,
                        'Delivery item removed during draft delivery update.',
                        [],
                        null,
                        $companyId,
                        $salesOrder->branch_id
                    );
                }

                foreach ($preparedItems as $itemData) {

                    $deliveryItem = $delivery->items()->create($itemData);

                    $auditService->log(
                        $deliveryItem,
                        'created',
                        null,
                        $deliveryItem->getAttributes(),
                        'Delivery item added during draft delivery update.',
                        [],
                        null,
                        $companyId,
                        $salesOrder->branch_id
                    );
                }
            }

            $delivery->save();

            $auditService->log(
                $delivery,
                'updated',
                $oldDeliveryValues,
                $delivery->getChanges(),
                'Draft delivery updated.',
                [],
                null,
                $companyId,
                $salesOrder->branch_id
            );

            return $delivery->load([
                'salesOrder.customer',
                'warehouse',
                'creator',
                'items.product',
                'items.unit',
                'items.salesOrderItem',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Delivery updated successfully.',
            'data' => $delivery,
        ]);
    }

    public function dispatch(string $id, AuditService $auditService)
    {
        $companyId = auth()->user()
            ->branches()
            ->value('company_id');

        $delivery = DB::transaction(function () use (
            $id,
            $companyId,
            $auditService
        ) {
            $delivery = Delivery::query()
                ->whereHas('salesOrder.branch', function ($query) use ($companyId) {
                    $query->where('company_id', $companyId)
                        ->whereIn(
                            'branches.id',
                            auth()->user()->branches()->select('branches.id')
                        );
                })
                ->lockForUpdate()
                ->findOrFail($id);

            if ($delivery->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Only draft deliveries can be dispatched.',
                ]);
            }

            $salesOrder = SalesOrder::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($delivery->sales_order_id);

            if (!in_array($salesOrder->status, ['approved', 'partially_delivered'])) {
                throw ValidationException::withMessages([
                    'sales_order' => 'Sales order must be approved or partially delivered.',
                ]);
            }

            $deliveryItems = $delivery->items()
                ->lockForUpdate()
                ->get();

            if ($deliveryItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Delivery has no items to dispatch.',
                ]);
            }

            foreach ($deliveryItems as $deliveryItem) {

                $baseQuantity = (float) $deliveryItem->base_quantity;

                // Lock stock balance
                $stockBalance = StockBalance::query()
                    ->where('warehouse_id', $delivery->warehouse_id)
                    ->where('product_id', $deliveryItem->product_id)
                    ->lockForUpdate()
                    ->first();

                if (!$stockBalance) {
                    throw ValidationException::withMessages([
                        'stock' => "Stock balance not found for product {$deliveryItem->product_id}.",
                    ]);
                }

                if (
                    (float) $stockBalance->on_hand_quantity < $baseQuantity ||
                    (float) $stockBalance->reserved_quantity < $baseQuantity
                ) {
                    throw ValidationException::withMessages([
                        'stock' => "Insufficient reserved or physical stock for product {$deliveryItem->product_id}.",
                    ]);
                }

                // Fetch active sales order reservations
                $reservations = StockReservation::query()
                    ->where('warehouse_id', $delivery->warehouse_id)
                    ->where('product_id', $deliveryItem->product_id)
                    ->where('reference_type', $salesOrder->getMorphClass())
                    ->where('reference_id', $salesOrder->id)
                    ->where('status', 'active')
                    ->orderBy('created_at')
                    ->lockForUpdate()
                    ->get();

                $totalReserved = (float) $reservations->sum('quantity');

                if ($totalReserved < $baseQuantity) {
                    throw ValidationException::withMessages([
                        'reservation' => "Insufficient active reservation for product {$deliveryItem->product_id}.",
                    ]);
                }

                // Fulfill reservation
                $remainingToFulfill = $baseQuantity;

                foreach ($reservations as $reservation) {

                    if ($remainingToFulfill <= 0) {
                        break;
                    }

                    $oldReservationValues = $reservation->getAttributes();

                    $reservationQuantity = (float) $reservation->quantity;

                    $fulfilledQuantity = min(
                        $reservationQuantity,
                        $remainingToFulfill
                    );

                    $remainingReservation = round(
                        $reservationQuantity - $fulfilledQuantity,
                        8
                    );

                    if ($remainingReservation <= 0) {
                        $reservation->quantity = 0;
                        $reservation->status = 'fulfilled';
                    } else {
                        $reservation->quantity = $remainingReservation;
                    }

                    $reservation->save();

                    $auditService->log(
                        $reservation,
                        'fulfilled',
                        $oldReservationValues,
                        $reservation->getChanges(),
                        'Stock reservation fulfilled through delivery dispatch.',
                        [],
                        null,
                        $companyId,
                        $salesOrder->branch_id
                    );

                    $remainingToFulfill = round(
                        $remainingToFulfill - $fulfilledQuantity,
                        8
                    );
                }

                // Deduct physical and reserved stock
                $oldBalanceValues = $stockBalance->getAttributes();

                $stockBalance->on_hand_quantity = round(
                    (float) $stockBalance->on_hand_quantity - $baseQuantity,
                    8
                );

                $stockBalance->reserved_quantity = round(
                    (float) $stockBalance->reserved_quantity - $baseQuantity,
                    8
                );

                $stockBalance->save();

                // Create stock movement
                $stockMovement = StockMovement::create([
                    'warehouse_id' => $delivery->warehouse_id,
                    'product_id' => $deliveryItem->product_id,
                    'unit_id' => $deliveryItem->unit_id,
                    'movement_type' => 'sale',
                    'direction' => 'out',
                    'quantity' => $deliveryItem->quantity,
                    'conversion_factor' => $deliveryItem->conversion_factor,
                    'base_quantity' => $baseQuantity,
                    'balance_after' => $stockBalance->on_hand_quantity,
                    'reference_type' => $delivery->getMorphClass(),
                    'reference_id' => $delivery->id,
                    'notes' => "Stock dispatched against delivery {$delivery->delivery_number}.",
                    'created_by' => auth()->id(),
                ]);

                // Audit stock balance
                $auditService->log(
                    $stockBalance,
                    'updated',
                    $oldBalanceValues,
                    $stockBalance->getChanges(),
                    'Stock deducted through delivery dispatch.',
                    [],
                    null,
                    $companyId,
                    $salesOrder->branch_id
                );

                // Audit stock movement
                $auditService->log(
                    $stockMovement,
                    'created',
                    null,
                    $stockMovement->getAttributes(),
                    'Stock movement created through delivery dispatch.',
                    [],
                    null,
                    $companyId,
                    $salesOrder->branch_id
                );

                // Update sales order item delivered quantity
                $orderItem = SalesOrderItem::query()
                    ->where('id', $deliveryItem->sales_order_item_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $oldOrderItemValues = $orderItem->getAttributes();

                $orderItem->delivered_quantity = round(
                    (float) $orderItem->delivered_quantity + $baseQuantity,
                    8
                );

                $orderItem->save();

                $auditService->log(
                    $orderItem,
                    'updated',
                    $oldOrderItemValues,
                    $orderItem->getChanges(),
                    'Delivered quantity updated through delivery dispatch.',
                    [],
                    null,
                    $companyId,
                    $salesOrder->branch_id
                );
            }

            // Update Sales Order status
            $salesOrder->load('items');

            $allDelivered = $salesOrder->items->every(function ($item) {
                return (float) $item->delivered_quantity >=
                    (float) $item->base_quantity;
            });

            $anyDelivered = $salesOrder->items->contains(function ($item) {
                return (float) $item->delivered_quantity > 0;
            });

            $oldOrderStatus = $salesOrder->status;

            $newOrderStatus = $allDelivered
                ? 'delivered'
                : ($anyDelivered ? 'partially_delivered' : 'approved');

            if ($oldOrderStatus !== $newOrderStatus) {

                $salesOrder->status = $newOrderStatus;
                $salesOrder->save();

                $salesOrder->statusHistories()->create([
                    'old_status' => $oldOrderStatus,
                    'new_status' => $newOrderStatus,
                    'changed_by' => auth()->id(),
                    'notes' => "Status updated after delivery {$delivery->delivery_number} dispatch.",
                ]);

                $auditService->log(
                    $salesOrder,
                    'status_changed',
                    ['status' => $oldOrderStatus],
                    ['status' => $newOrderStatus],
                    'Sales order status updated through delivery dispatch.',
                    [],
                    null,
                    $companyId,
                    $salesOrder->branch_id
                );
            }

            // Update Delivery status
            $oldDeliveryValues = $delivery->getAttributes();

            $delivery->status = 'dispatched';
            $delivery->dispatched_at = now();
            $delivery->save();

            $delivery->statusHistories()->create([
                'old_status' => 'draft',
                'new_status' => 'dispatched',
                'changed_by' => auth()->id(),
                'notes' => 'Delivery dispatched successfully.',
            ]);

            $auditService->log(
                $delivery,
                'dispatched',
                $oldDeliveryValues,
                $delivery->getChanges(),
                'Delivery dispatched successfully.',
                [],
                null,
                $companyId,
                $salesOrder->branch_id
            );

            return $delivery->load([
                'salesOrder.customer',
                'warehouse',
                'creator',
                'items.product',
                'items.unit',
                'statusHistories.changedBy',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Delivery dispatched successfully.',
            'data' => $delivery,
        ]);
    }

    public function deliver(
        Request $request,
        string $id,
        AuditService $auditService
    ) {
        $validated = $request->validate([
            'received_by_name' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $companyId = auth()->user()
            ->branches()
            ->value('company_id');

        $delivery = DB::transaction(function () use (
            $id,
            $validated,
            $companyId,
            $auditService
        ) {
            $delivery = Delivery::query()
                ->whereHas('salesOrder.branch', function ($query) use ($companyId) {
                    $query->where('company_id', $companyId)
                        ->whereIn(
                            'branches.id',
                            auth()->user()->branches()->select('branches.id')
                        );
                })
                ->lockForUpdate()
                ->findOrFail($id);

            if ($delivery->status !== 'dispatched') {
                throw ValidationException::withMessages([
                    'status' => 'Only dispatched deliveries can be marked as delivered.',
                ]);
            }

            $salesOrder = $delivery->salesOrder;

            $oldValues = $delivery->getAttributes();

            $delivery->received_by_name = $validated['received_by_name'];
            $delivery->status = 'delivered';
            $delivery->delivered_at = now();
            $delivery->save();

            $delivery->statusHistories()->create([
                'old_status' => 'dispatched',
                'new_status' => 'delivered',
                'changed_by' => auth()->id(),
                'notes' => 'Delivery received by ' . $validated['received_by_name'],
            ]);

            $auditService->log(
                $delivery,
                'delivered',
                $oldValues,
                $delivery->getChanges(),
                'Delivery marked as delivered.',
                [],
                null,
                $companyId,
                $salesOrder->branch_id
            );

            return $delivery->load([
                'salesOrder.customer',
                'warehouse',
                'creator',
                'items.product',
                'items.unit',
                'statusHistories.changedBy',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Delivery marked as delivered successfully.',
            'data' => $delivery,
        ]);
    }

    public function cancel(string $id, AuditService $auditService)
    {
        $companyId = auth()->user()
            ->branches()
            ->value('company_id');

        $delivery = DB::transaction(function () use (
            $id,
            $companyId,
            $auditService
        ) {
            $delivery = Delivery::query()
                ->whereHas('salesOrder.branch', function ($query) use ($companyId) {
                    $query->where('company_id', $companyId)
                        ->whereIn(
                            'branches.id',
                            auth()->user()->branches()->select('branches.id')
                        );
                })
                ->lockForUpdate()
                ->findOrFail($id);

            if ($delivery->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Only draft deliveries can be cancelled.',
                ]);
            }

            $salesOrder = $delivery->salesOrder;

            $oldValues = $delivery->getAttributes();

            $delivery->status = 'cancelled';
            $delivery->save();

            $delivery->statusHistories()->create([
                'old_status' => 'draft',
                'new_status' => 'cancelled',
                'changed_by' => auth()->id(),
                'notes' => 'Draft delivery cancelled.',
            ]);

            $auditService->log(
                $delivery,
                'cancelled',
                $oldValues,
                $delivery->getChanges(),
                'Draft delivery cancelled.',
                [],
                null,
                $companyId,
                $salesOrder->branch_id
            );

            return $delivery->load([
                'salesOrder.customer',
                'warehouse',
                'items.product',
                'items.unit',
                'statusHistories.changedBy',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Delivery cancelled successfully.',
            'data' => $delivery,
        ]);
    }
}
