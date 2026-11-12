<?php

namespace App\Services;

use App\Models\SalesOrder;
use App\Models\StockBalance;
use App\Models\StockReservation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StockReservationService
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    public function reserveForSalesOrder(SalesOrder $salesOrder): void
    {
        DB::transaction(function () use ($salesOrder) {

            $salesOrder = SalesOrder::query()
                ->whereKey($salesOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($salesOrder->status, ['approved'])) {
                throw ValidationException::withMessages([
                    'status' => 'Only approved sales orders can reserve stock.',
                ]);
            }

            $items = $salesOrder->items()
                ->orderBy('product_id')
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Sales order has no items.',
                ]);
            }

            $companyId = $salesOrder->branch()
                ->value('company_id');

            $referenceType = $salesOrder->getMorphClass();

            // Product-wise outstanding quantity aggregate.
            $requiredQuantities = [];

            foreach ($items as $item) {

                $remainingQuantity = round(
                    (float) $item->base_quantity -
                    (float) $item->delivered_quantity,
                    8
                );

                if ($remainingQuantity < 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Delivered quantity cannot exceed ordered quantity.',
                    ]);
                }

                if ($remainingQuantity == 0) {
                    continue;
                }

                $productId = $item->product_id;

                $requiredQuantities[$productId] =
                    ($requiredQuantities[$productId] ?? 0)
                    + $remainingQuantity;
            }

            foreach ($requiredQuantities as $productId => $requiredQuantity) {

                // Existing active reservations for this order/product.
                $alreadyReserved = StockReservation::query()
                    ->where('reference_type', $referenceType)
                    ->where('reference_id', $salesOrder->id)
                    ->where('product_id', $productId)
                    ->where('warehouse_id', $salesOrder->warehouse_id)
                    ->where('status', 'active')
                    ->sum('quantity');

                $quantityToReserve = round(
                    $requiredQuantity - (float) $alreadyReserved,
                    8
                );

                // Already reserved: avoid duplicate reservation.
                if ($quantityToReserve <= 0) {
                    continue;
                }

                // Create stock balance if it does not exist.
                DB::table('stock_balances')->insertOrIgnore([
                    'id' => (string) Str::uuid(),
                    'warehouse_id' => $salesOrder->warehouse_id,
                    'product_id' => $productId,
                    'on_hand_quantity' => 0,
                    'reserved_quantity' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Lock stock balance to prevent concurrent reservations.
                $balance = StockBalance::query()
                    ->where('warehouse_id', $salesOrder->warehouse_id)
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $availableQuantity = round(
                    (float) $balance->on_hand_quantity -
                    (float) $balance->reserved_quantity,
                    8
                );

                if ($availableQuantity < $quantityToReserve) {
                    throw ValidationException::withMessages([
                        'stock' => [
                            "Insufficient stock for product {$productId}. " .
                            "Required: {$quantityToReserve}, " .
                            "Available: {$availableQuantity}."
                        ],
                    ]);
                }

                $oldReservedQuantity = (float) $balance->reserved_quantity;

                $newReservedQuantity = round(
                    $oldReservedQuantity + $quantityToReserve,
                    8
                );

                // Increase reserved stock only.
                $balance->update([
                    'reserved_quantity' => $newReservedQuantity,
                ]);

                // Create reservation record.
                $reservation = StockReservation::create([
                    'warehouse_id' => $salesOrder->warehouse_id,
                    'product_id' => $productId,
                    'reference_type' => $referenceType,
                    'reference_id' => $salesOrder->id,
                    'quantity' => $quantityToReserve,
                    'status' => 'active',
                    'notes' => "Reserved for Sales Order {$salesOrder->order_number}",
                    'created_by' => Auth::id(),
                ]);

                // Audit stock balance change.
                $this->auditService->log(
                    $balance,
                    'stock_reserved',
                    [
                        'reserved_quantity' => $oldReservedQuantity,
                    ],
                    [
                        'reserved_quantity' => $newReservedQuantity,
                    ],
                    'Stock reserved for Sales Order.',
                    [
                        'sales_order_id' => $salesOrder->id,
                        'reservation_id' => $reservation->id,
                    ],
                    null,
                    $companyId,
                    $salesOrder->branch_id
                );

                // Audit reservation creation.
                $this->auditService->log(
                    $reservation,
                    'created',
                    null,
                    $reservation->getAttributes(),
                    'Stock reservation created for Sales Order.',
                    [
                        'sales_order_id' => $salesOrder->id,
                    ],
                    null,
                    $companyId,
                    $salesOrder->branch_id
                );
            }
        });
    }

    public function releaseForSalesOrder(SalesOrder $salesOrder): void
    {
        DB::transaction(function () use ($salesOrder) {

            $salesOrder = SalesOrder::query()
                ->whereKey($salesOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($salesOrder->status !== 'approved') {
                throw ValidationException::withMessages([
                    'status' => 'Only approved sales orders can release stock reservations.',
                ]);
            }

            $companyId = $salesOrder->branch()->value('company_id');

            $referenceType = $salesOrder->getMorphClass();

            $reservations = StockReservation::query()
                ->where('reference_type', $referenceType)
                ->where('reference_id', $salesOrder->id)
                ->where('warehouse_id', $salesOrder->warehouse_id)
                ->where('status', 'active')
                ->orderBy('product_id')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($reservations as $reservation) {

                $balance = StockBalance::query()
                    ->where('warehouse_id', $reservation->warehouse_id)
                    ->where('product_id', $reservation->product_id)
                    ->lockForUpdate()
                    ->first();

                if (!$balance) {
                    throw ValidationException::withMessages([
                        'stock' => 'Stock balance not found for an active reservation.',
                    ]);
                }

                $oldReservedQuantity = (float) $balance->reserved_quantity;

                $releaseQuantity = (float) $reservation->quantity;

                if ($oldReservedQuantity < $releaseQuantity) {
                    throw ValidationException::withMessages([
                        'stock' => 'Reserved stock quantity is inconsistent. Reservation cannot be released.',
                    ]);
                }

                $newReservedQuantity = round(
                    $oldReservedQuantity - $releaseQuantity,
                    8
                );

                $balance->update([
                    'reserved_quantity' => $newReservedQuantity,
                ]);

                $oldReservationValues = $reservation->getAttributes();

                $reservation->update([
                    'status' => 'released',
                ]);

                $this->auditService->log(
                    $balance,
                    'stock_reservation_released',
                    [
                        'reserved_quantity' => $oldReservedQuantity,
                    ],
                    [
                        'reserved_quantity' => $newReservedQuantity,
                    ],
                    'Stock reservation released for Sales Order.',
                    [
                        'sales_order_id' => $salesOrder->id,
                        'reservation_id' => $reservation->id,
                        'released_quantity' => $releaseQuantity,
                    ],
                    null,
                    $companyId,
                    $salesOrder->branch_id
                );

                $this->auditService->log(
                    $reservation,
                    'released',
                    $oldReservationValues,
                    $reservation->getAttributes(),
                    'Stock reservation released.',
                    [
                        'sales_order_id' => $salesOrder->id,
                    ],
                    null,
                    $companyId,
                    $salesOrder->branch_id
                );
            }
        });
    }
}
