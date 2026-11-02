<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StockBalance extends Model
{
    use HasUuids;

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'on_hand_quantity',
        'reserved_quantity',
    ];

    protected function casts(): array
    {
        return [
            'on_hand_quantity' => 'decimal:8',
            'reserved_quantity' => 'decimal:8',
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
}