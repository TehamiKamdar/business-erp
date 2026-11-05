<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public function log(
        Model $auditable,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        array $metadata = [],
        ?Model $actor = null,
        ?string $companyId = null,
        ?string $branchId = null
    ): AuditLog {

        $actor = $actor ?? Auth::user();

        // Automatically detect branch context
        $branchId = $branchId
            ?? $auditable->getAttribute('branch_id');

        // Automatically detect company context
        $companyId = $companyId
            ?? $auditable->getAttribute('company_id');

        if ($branchId && !$companyId) {
            $companyId = Branch::whereKey($branchId)
                ->value('company_id');
        }

        return AuditLog::create([
            'company_id' => $companyId,
            'branch_id' => $branchId,

            'actor_type' => $actor?->getMorphClass(),
            'actor_id' => $actor?->getKey(),

            'auditable_type' => $auditable->getMorphClass(),
            'auditable_id' => $auditable->getKey(),

            'action' => $action,

            'old_values' => $oldValues,
            'new_values' => $newValues,

            'description' => $description,

            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),

            'metadata' => $metadata,
        ]);
    }
}
