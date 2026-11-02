<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CashBankAccount extends Model
{
    use HasUuids;

    protected $fillable = [
        'company_id',
        'branch_id',
        'account_name',
        'account_type',
        'bank_name',
        'account_number',
        'account_title',
        'iban',
        'currency',
        'opening_balance',
        'current_balance',
        'is_default',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'is_default' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function transactions()
    {
        return $this->hasMany(CashBankTransaction::class);
    }

    public function customerPayments()
    {
        return $this->hasMany(CustomerPayment::class);
    }

    public function supplierPayments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
