<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasUuids;

    protected $fillable = [
        'company_id',
        'category_id',
        'brand_id',
        'base_unit_id',
        'name',
        'sku',
        'barcode',
        'description',
        'reorder_level',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function purchaseRequestItems()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function goodsReceiptItems()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function supplierPrices()
    {
        return $this->hasManyThrough(
            SupplierProductPrice::class,
            ProductUnit::class,
            'product_id',
            'product_unit_id'
        );
    }

    public function quotationItems()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function salesOrderItems()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function deliveryItems()
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function salesInvoiceItems()
    {
        return $this->hasMany(SalesInvoiceItem::class);
    }

    public function salesReturnItems()
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    public function customerPrices()
    {
        return $this->hasManyThrough(
            CustomerPrice::class,
            ProductUnit::class,
            'product_id',
            'product_unit_id',
            'id',
            'id'
        );
    }

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function supplierPaymentTradeIns()
    {
        return $this->hasMany(SupplierPaymentTradeIn::class);
    }


    public function productPrices()
    {
        return $this->hasManyThrough(
            ProductPrice::class,
            ProductUnit::class,
            'product_id',
            'product_unit_id',
            'id',
            'id'
        );
    }
}
