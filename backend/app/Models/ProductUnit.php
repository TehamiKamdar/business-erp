<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    use HasUuids;

    protected $fillable = [
        'product_id',
        'unit_id',
        'conversion_factor',
        'is_base_unit',
        'is_purchase_unit',
        'is_sale_unit',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'conversion_factor' => 'decimal:4',
            'is_base_unit' => 'boolean',
            'is_purchase_unit' => 'boolean',
            'is_sale_unit' => 'boolean',
        ];
    }

    public function productPrices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
