<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeEmergencyContact extends Model
{
    use HasUuids;

    protected $fillable = [
        'employee_profile_id',
        'name',
        'relationship',
        'phone',
        'email',
        'address',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
