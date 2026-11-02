<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasUuids;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'address',
        'phone',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
