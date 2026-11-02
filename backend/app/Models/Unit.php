<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasUuids;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'description',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
