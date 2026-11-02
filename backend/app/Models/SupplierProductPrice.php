<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SupplierProductPrice extends Model
{
    use HasUuids;

    protected $fillable = [
        'supplier_id',
        'product_unit_id',
        'purchase_price',
        'currency',
        'min_quantity',
        'effective_from',
        'effective_until',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'min_quantity' => 'decimal:8',
            'effective_from' => 'date',
            'effective_until' => 'date',
            'status' => 'boolean',
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class);
    }
}