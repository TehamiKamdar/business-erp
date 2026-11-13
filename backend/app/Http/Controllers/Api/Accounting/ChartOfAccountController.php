<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChartOfAccountController extends Controller
{
    public function __construct(
        protected AuditService $auditService
    ) {}
    private function companyId(): string
    {
        $companyIds = auth()->user()
            ->branches()
            ->pluck('company_id')
            ->unique()
            ->values();

        abort_if(
            $companyIds->count() !== 1,
            403,
            'Company context not found.'
        );

        return $companyIds->first();
    }

    public function index(Request $request)
    {
        $companyId = $this->companyId();

        $validated = $request->validate([
            'account_type' => [
                'nullable',
                'in:asset,liability,equity,income,expense'
            ],
            'status' => ['nullable', 'boolean'],
            'parent_id' => ['nullable', 'uuid'],
            'root_only' => ['nullable', 'boolean'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $accounts = ChartOfAccount::query()
            ->where('company_id', $companyId)
            ->with([
                'parent:id,account_code,name',
                'children:id,parent_id,account_code,name',
            ])
            ->when(
                isset($validated['account_type']),
                fn ($query) => $query->where(
                    'account_type',
                    $validated['account_type']
                )
            )
            ->when(
                array_key_exists('status', $validated),
                fn ($query) => $query->where(
                    'status',
                    $validated['status']
                )
            )
            ->when(
                isset($validated['parent_id']),
                fn ($query) => $query->where(
                    'parent_id',
                    $validated['parent_id']
                )
            )
            ->when(
                !empty($validated['root_only']),
                fn ($query) => $query->whereNull('parent_id')
            )
            ->when(
                !empty($validated['search']),
                function ($query) use ($validated) {
                    $search = $validated['search'];

                    $query->where(function ($q) use ($search) {
                        $q->where('account_code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
                }
            )
            ->orderBy('account_code')
            ->get();

        return response()->json([
            'message' => 'Chart of accounts retrieved successfully.',
            'data' => $accounts,
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $this->companyId();

        $validated = $request->validate([
            'parent_id' => [
                'nullable',
                'uuid',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('company_id', $companyId),
            ],

            'account_code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('chart_of_accounts', 'account_code')
                    ->where('company_id', $companyId),
            ],

            'name' => ['required', 'string', 'max:255'],

            'account_type' => [
                'required',
                Rule::in([
                    'asset',
                    'liability',
                    'equity',
                    'income',
                    'expense',
                ]),
            ],

            'account_subtype' => [
                'nullable',
                'string',
                'max:100',
            ],

            'normal_balance' => [
                'required',
                Rule::in(['debit', 'credit']),
            ],

            'is_control_account' => [
                'sometimes',
                'boolean',
            ],

            'allow_manual_entries' => [
                'sometimes',
                'boolean',
            ],

            'status' => [
                'sometimes',
                'boolean',
            ],

            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $isControlAccount = $validated['is_control_account'] ?? false;

        if ($isControlAccount && !empty($validated['parent_id'])) {
            $parent = ChartOfAccount::where('company_id', $companyId)
                ->findOrFail($validated['parent_id']);

            abort_if(
                $parent->is_control_account,
                422,
                'A control account cannot have child accounts.'
            );
        }

        $validated['company_id'] = $companyId;

        $validated['is_control_account'] = $isControlAccount;

        $validated['allow_manual_entries'] = $isControlAccount
            ? false
            : ($validated['allow_manual_entries'] ?? true);

        $validated['status'] = $validated['status'] ?? true;

        $account = ChartOfAccount::create($validated);

        $this->auditService->log(
            $account,
            'created',
            null,
            $account->getAttributes(),
            'Chart of account created.',
            [],
            null,
            $companyId,
            null
        );

        return response()->json([
            'message' => 'Chart of account created successfully.',
            'data' => $account->load('parent'),
        ], 201);
    }

    public function show(string $accountId)
    {
        $companyId = $this->companyId();

        $account = ChartOfAccount::query()
            ->where('company_id', $companyId)
            ->with([
                'parent:id,account_code,name,account_type',
                'children:id,parent_id,account_code,name,account_type,normal_balance,status',
            ])
            ->withCount('journalEntryLines')
            ->findOrFail($accountId);

        return response()->json([
            'message' => 'Chart of account retrieved successfully.',
            'data' => $account,
        ]);
    }

    public function update(Request $request, string $accountId)
    {
        $companyId = $this->companyId();

        $account = ChartOfAccount::where('company_id', $companyId)
            ->findOrFail($accountId);

        $validated = $request->validate([
            'parent_id' => [
                'sometimes',
                'nullable',
                'uuid',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('company_id', $companyId),
            ],

            'account_code' => [
                'sometimes',
                'required',
                'string',
                'max:30',
                Rule::unique('chart_of_accounts', 'account_code')
                    ->where('company_id', $companyId)
                    ->ignore($account->id),
            ],

            'name' => ['sometimes', 'required', 'string', 'max:255'],

            'account_type' => [
                'sometimes',
                'required',
                Rule::in(['asset', 'liability', 'equity', 'income', 'expense']),
            ],

            'account_subtype' => ['sometimes', 'nullable', 'string', 'max:100'],

            'normal_balance' => [
                'sometimes',
                'required',
                Rule::in(['debit', 'credit']),
            ],

            'is_control_account' => ['sometimes', 'boolean'],

            'allow_manual_entries' => ['sometimes', 'boolean'],

            'status' => ['sometimes', 'boolean'],

            'description' => ['sometimes', 'nullable', 'string'],
        ]);

        if (array_key_exists('parent_id', $validated)) {
            abort_if(
                $validated['parent_id'] === $account->id,
                422,
                'An account cannot be its own parent.'
            );

            if ($validated['parent_id']) {
                $descendantIds = $this->getDescendantIds($account);

                abort_if(
                    in_array($validated['parent_id'], $descendantIds, true),
                    422,
                    'An account cannot be moved under its own child.'
                );
            }
        }

        $isControlAccount = $validated['is_control_account']
            ?? $account->is_control_account;

        if ($isControlAccount) {
            abort_if(
                $account->children()->exists(),
                422,
                'An account with child accounts cannot be a control account.'
            );

            $validated['allow_manual_entries'] = false;
        }

        $oldValues = $account->getOriginal();

        $account->update($validated);

        $this->auditService->log(
            $account,
            'updated',
            $oldValues,
            $account->getChanges(),
            'Chart of account updated.',
            [],
            null,
            $companyId,
            null
        );

        return response()->json([
            'message' => 'Chart of account updated successfully.',
            'data' => $account->fresh()->load('parent'),
        ]);
    }

    private function getDescendantIds(ChartOfAccount $account): array
    {
        $ids = [];

        $children = ChartOfAccount::where('parent_id', $account->id)->get();

        foreach ($children as $child) {
            $ids[] = $child->id;

            $ids = array_merge(
                $ids,
                $this->getDescendantIds($child)
            );
        }

        return $ids;
    }

    public function deactivate(string $accountId)
    {
        $companyId = $this->companyId();

        $account = ChartOfAccount::where('company_id', $companyId)
            ->findOrFail($accountId);

        abort_if(
            !$account->status,
            422,
            'Account is already inactive.'
        );

        abort_if(
            $account->journalEntryLines()->exists(),
            422,
            'Account has journal entries and cannot be deactivated.'
        );

        abort_if(
            $account->children()->where('status', true)->exists(),
            422,
            'Account has active child accounts. Deactivate them first.'
        );

        $oldValues = $account->getOriginal();

        $account->update([
            'status' => false,
        ]);

        $this->auditService->log(
            $account,
            'deactivated',
            $oldValues,
            $account->getChanges(),
            'Chart of account deactivated.',
            [],
            null,
            $companyId,
            null
        );

        return response()->json([
            'message' => 'Chart of account deactivated successfully.',
            'data' => $account,
        ]);
    }

}
