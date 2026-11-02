<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Expense extends Model
{
    use HasUuids;

    protected $fillable = [
        'branch_id',
        'expense_category_id',
        'cash_bank_account_id',
        'payment_method_id',
        'expense_number',
        'expense_date',
        'title',
        'amount',
        'currency',
        'reference_number',
        'status',
        'description',
        'created_by',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'amount' => 'decimal:2',
            'posted_at' => 'datetime',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function category()
    {
        return $this->belongsTo(
            ExpenseCategory::class,
            'expense_category_id'
        );
    }

    public function cashBankAccount()
    {
        return $this->belongsTo(CashBankAccount::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
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
