<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Delivery extends Model
{
    use HasUuids;

    protected $fillable = [
        'sales_order_id',
        'warehouse_id',
        'delivery_number',
        'delivery_date',
        'status',
        'dispatched_at',
        'delivered_at',
        'received_by_name',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'dispatched_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function invoices()
    {
        return $this->belongsToMany(
            SalesInvoice::class,
            'delivery_invoice'
        );
    }

    public function statusHistories()
    {
        return $this->hasMany(DeliveryStatusHistory::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
