<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    use HasUuids;

    protected $fillable = [
        'employee_profile_id',
        'document_type',
        'document_name',
        'document_number',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'issued_on',
        'expires_on',
        'notes',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'issued_on' => 'date',
            'expires_on' => 'date',
            'file_size' => 'integer',
        ];
    }

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
