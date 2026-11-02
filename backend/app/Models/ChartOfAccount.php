<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    use HasUuids;

    protected $fillable = [
        'company_id',
        'parent_id',
        'account_code',
        'name',
        'account_type',
        'account_subtype',
        'normal_balance',
        'is_control_account',
        'allow_manual_entries',
        'status',
        'description',
    ];

    protected $casts = [
        'is_control_account' => 'boolean',
        'allow_manual_entries' => 'boolean',
        'status' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }
}
