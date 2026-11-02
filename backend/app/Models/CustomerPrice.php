<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CustomerPrice extends Model
{
    use HasUuids;

    protected $fillable = [
        'customer_id',
        'product_unit_id',
        'price',
        'min_quantity',
        'currency',
        'effective_from',
        'effective_until',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'min_quantity' => 'decimal:8',
            'effective_from' => 'date',
            'effective_until' => 'date',
            'status' => 'boolean',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class);
    }
}
