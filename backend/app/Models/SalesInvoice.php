<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SalesInvoice extends Model
{
    use HasUuids;

    protected $fillable = [
        'customer_id',
        'branch_id',
        'sales_order_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'status',
        'currency',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function items()
    {
        return $this->hasMany(SalesInvoiceItem::class);
    }

    public function deliveries()
    {
        return $this->belongsToMany(
            Delivery::class,
            'delivery_invoice'
        );
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function salesReturns()
    {
        return $this->hasMany(SalesReturn::class);
    }

    public function paymentAllocations()
    {
        return $this->hasMany(CustomerPaymentAllocation::class);
    }
}
