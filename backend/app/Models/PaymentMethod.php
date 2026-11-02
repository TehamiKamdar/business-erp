<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PaymentMethod extends Model
{
    use HasUuids;

    protected $fillable = [
        'company_id',
        'name',
        'type',
        'requires_reference',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'requires_reference' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
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
