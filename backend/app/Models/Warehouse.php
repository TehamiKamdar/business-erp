<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasUuids;

    protected $fillable = [
        'branch_id',
        'name',
        'code',
        'address',
        'status',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
