<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferReceiptItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'stock_transfer_receipt_id',
        'stock_transfer_item_id',
        'product_id',
        'unit_id',
        'received_quantity',
        'conversion_factor',
        'base_quantity',
        'notes',
    ];

    protected $casts = [
        'received_quantity' => 'decimal:8',
        'conversion_factor' => 'decimal:8',
        'base_quantity' => 'decimal:8',
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(StockTransferReceipt::class, 'stock_transfer_receipt_id');
    }

    public function stockTransferItem(): BelongsTo
    {
        return $this->belongsTo(StockTransferItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}