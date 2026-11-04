<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    private function companyId()
    {
        return auth()->user()->branches()->value('company_id');
    }

    public function index(Request $request)
    {
        $query = AuditLog::where('company_id', $this->companyId())
            ->with(['actor', 'company', 'branch'])
            ->latest('created_at');

        $validated = $request->validate([
            'branch_id' => ['nullable', 'uuid'],
            'action' => ['nullable', 'string', 'max:100'],
            'actor_id' => ['nullable', 'uuid'],
            'auditable_type' => ['nullable', 'string', 'max:255'],
            'auditable_id' => ['nullable', 'uuid'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);

        if (!empty($validated['branch_id'])) {
            $query->where('branch_id', $validated['branch_id']);
        }

        if (!empty($validated['action'])) {
            $query->where('action', $validated['action']);
        }

        if (!empty($validated['actor_id'])) {
            $query->where('actor_id', $validated['actor_id']);
        }

        if (!empty($validated['auditable_type'])) {
            $query->where('auditable_type', $validated['auditable_type']);
        }

        if (!empty($validated['auditable_id'])) {
            $query->where('auditable_id', $validated['auditable_id']);
        }

        if (!empty($validated['from_date'])) {
            $query->whereDate('created_at', '>=', $validated['from_date']);
        }

        if (!empty($validated['to_date'])) {
            $query->whereDate('created_at', '<=', $validated['to_date']);
        }

        return response()->json([
            'message' => 'Audit logs retrieved successfully.',
            'data' => $query->paginate(20),
        ]);
    }

    public function show($id)
    {
        $auditLog = AuditLog::where('company_id', $this->companyId())
            ->with(['actor', 'company', 'branch'])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Audit log retrieved successfully.',
            'data' => $auditLog,
        ]);
    }
}
