<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CustomerPayment extends Model
{
    use HasUuids;

    protected $fillable = [
        'customer_id',
        'branch_id',
        'cash_bank_account_id',
        'payment_method_id',
        'receipt_number',
        'payment_date',
        'amount',
        'currency',
        'reference_number',
        'status',
        'notes',
        'created_by',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'posted_at' => 'datetime',
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

    public function cashBankAccount()
    {
        return $this->belongsTo(CashBankAccount::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function allocations()
    {
        return $this->hasMany(CustomerPaymentAllocation::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cashBankTransaction()
    {
        return $this->morphOne(
            CashBankTransaction::class,
            'reference'
        );
    }
}
