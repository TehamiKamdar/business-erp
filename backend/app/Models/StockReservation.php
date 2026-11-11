<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StockReservation extends Model
{
    use HasUuids;

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'reference_type',
        'reference_id',
        'quantity',
        'notes',
        'status',
        'expires_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:8',
            'expires_at' => 'datetime',
        ];
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function salesOrder()
    {
        return $this->belongsTo(
            SalesOrder::class,
            'reference_id'
        )->where(
            'reference_type',
            (new SalesOrder())->getMorphClass()
        );
    }
}
