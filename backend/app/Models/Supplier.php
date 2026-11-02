<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Supplier extends Model
{
    use HasUuids;

    protected $fillable = [
        'company_id',
        'supplier_code',
        'name',
        'email',
        'phone',
        'tax_number',
        'website',
        'address',
        'city',
        'country',
        'opening_balance',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function addresses()
    {
        return $this->hasMany(SupplierAddress::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function invoices()
    {
        return $this->hasMany(SupplierInvoice::class);
    }

    public function returns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function productPrices()
    {
        return $this->hasMany(SupplierProductPrice::class);
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }
}
