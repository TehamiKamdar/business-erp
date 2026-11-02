<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CustomerPaymentAllocation extends Model
{
    use HasUuids;

    protected $fillable = [
        'customer_payment_id',
        'sales_invoice_id',
        'allocated_amount',
    ];

    protected function casts(): array
    {
        return [
            'allocated_amount' => 'decimal:2',
        ];
    }

    public function customerPayment()
    {
        return $this->belongsTo(CustomerPayment::class);
    }

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }
}
