<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CashBankTransaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'cash_bank_account_id',
        'branch_id',
        'transaction_date',
        'transaction_type',
        'direction',
        'amount',
        'currency',
        'reference_type',
        'reference_id',
        'reference_number',
        'description',
        'balance_after',
        'created_by',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'posted_at' => 'datetime',
        ];
    }

    public function cashBankAccount()
    {
        return $this->belongsTo(CashBankAccount::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
