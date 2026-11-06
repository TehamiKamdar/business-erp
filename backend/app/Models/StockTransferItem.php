<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StockTransferItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'unit_id',
        'conversion_factor',
        'base_quantity',
        'requested_quantity',
        'dispatched_quantity',
        'received_quantity',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'requested_quantity' => 'decimal:8',
            'dispatched_quantity' => 'decimal:8',
            'received_quantity' => 'decimal:8',
            'conversion_factor' => 'decimal:8',
            'base_quantity' => 'decimal:8',
        ];
    }

    public function transfer()
    {
        return $this->belongsTo(StockTransfer::class, 'stock_transfer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function receiptItems()
    {
        return $this->hasMany(
            StockTransferReceiptItem::class,
            'stock_transfer_item_id'
        );
    }
}
