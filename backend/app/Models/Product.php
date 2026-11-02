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

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
