<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SupplierPayment extends Model
{
    use HasUuids;

    protected $fillable = [
        'supplier_id',
        'branch_id',
        'cash_bank_account_id',
        'payment_method_id',
        'payment_number',
        'payment_date',
        'cash_amount',
        'trade_in_amount',
        'total_amount',
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
            'cash_amount' => 'decimal:2',
            'trade_in_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'posted_at' => 'datetime',
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
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
        return $this->hasMany(SupplierPaymentAllocation::class);
    }

    public function tradeIns()
    {
        return $this->hasMany(SupplierPaymentTradeIn::class);
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
