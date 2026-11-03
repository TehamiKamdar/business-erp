<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    use HasUuids;

    protected $fillable = [
        'product_unit_id',
        'price_type',
        'price',
        'minimum_quantity',
        'effective_from',
        'status',
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
        'minimum_quantity' => 'decimal:2',
        'effective_from' => 'date',
    ];

    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class);
    }
}
