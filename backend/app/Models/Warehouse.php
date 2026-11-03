<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasUuids;

    protected $fillable = [
        'branch_id',
        'name',
        'code',
        'address',
        'status',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function purchaseReturns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function salesReturns()
    {
        return $this->hasMany(SalesReturn::class);
    }

    public function supplierPaymentTradeIns()
    {
        return $this->hasMany(SupplierPaymentTradeIn::class);
    }

    public function stockBalances(): HasMany
    {
        return $this->hasMany(StockBalance::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function stockTransfersFrom(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'from_warehouse_id');
    }

    public function stockTransfersTo(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'to_warehouse_id');
    }

    public function stockReservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }
}
