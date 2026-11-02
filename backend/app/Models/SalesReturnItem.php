<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SalesReturnItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'sales_return_id',
        'sales_invoice_item_id',
        'product_id',
        'unit_id',
        'quantity',
        'conversion_factor',
        'base_quantity',
        'unit_price',
        'total_amount',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:8',
            'conversion_factor' => 'decimal:8',
            'base_quantity' => 'decimal:8',
            'unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class);
    }

    public function salesInvoiceItem()
    {
        return $this->belongsTo(SalesInvoiceItem::class);
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
