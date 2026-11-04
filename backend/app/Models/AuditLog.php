<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class AuditLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'company_id',
        'branch_id',
        'actor_type',
        'actor_id',
        'auditable_type',
        'auditable_id',
        'action',
        'old_values',
        'new_values',
        'description',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new LogicException('Audit logs cannot be updated.');
        });

        static::deleting(function () {
            throw new LogicException('Audit logs cannot be deleted.');
        });
    }
}
