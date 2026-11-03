<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Company extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'legal_name',
        'email',
        'phone',
        'address',
        'currency',
        'status',
    ];

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function warehouses(): HasManyThrough
    {
        return $this->hasManyThrough(
            Warehouse::class,
            Branch::class,
            'company_id',
            'branch_id',
            'id',
            'id'
        );
    }

    public function users()
    {
        return User::whereHas('branches', function ($query) {
            $query->where('company_id', $this->id);
        });
    }

    public function cashBankAccounts()
    {
        return $this->hasMany(CashBankAccount::class);
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function expenseCategories()
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    public function fiscalYears()
    {
        return $this->hasMany(FiscalYear::class);
    }

    public function chartOfAccounts()
    {
        return $this->hasMany(ChartOfAccount::class);
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function designations(): HasMany
    {
        return $this->hasMany(Designation::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function employeeInvitations(): HasMany
    {
        return $this->hasMany(EmployeeInvitation::class);
    }
}
