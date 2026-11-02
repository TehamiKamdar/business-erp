<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

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
}
