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

    public function users()
    {
        return User::whereHas('branches', function ($query) {
            $query->where('company_id', $this->id);
        });
    }
}
