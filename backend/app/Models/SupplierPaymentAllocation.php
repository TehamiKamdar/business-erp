<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SupplierPaymentAllocation extends Model
{
    use HasUuids;

    protected $fillable = [
        'supplier_payment_id',
        'supplier_invoice_id',
        'allocated_amount',
    ];

    protected function casts(): array
    {
        return [
            'allocated_amount' => 'decimal:2',
        ];
    }

    public function supplierPayment()
    {
        return $this->belongsTo(SupplierPayment::class);
    }

    public function supplierInvoice()
    {
        return $this->belongsTo(SupplierInvoice::class);
    }
}
