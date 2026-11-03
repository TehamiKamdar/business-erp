<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasUuids;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'description',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}