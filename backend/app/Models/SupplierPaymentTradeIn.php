<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SupplierPaymentTradeIn extends Model
{
    use HasUuids;

    protected $fillable = [
        'supplier_payment_id',
        'product_id',
        'warehouse_id',
        'unit_id',
        'item_type',
        'description',
        'quantity',
        'conversion_factor',
        'base_quantity',
        'book_unit_cost',
        'agreed_unit_value',
        'total_book_value',
        'total_agreed_value',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:8',
            'conversion_factor' => 'decimal:8',
            'base_quantity' => 'decimal:8',
            'book_unit_cost' => 'decimal:2',
            'agreed_unit_value' => 'decimal:2',
            'total_book_value' => 'decimal:2',
            'total_agreed_value' => 'decimal:2',
        ];
    }

    public function supplierPayment()
    {
        return $this->belongsTo(SupplierPayment::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
