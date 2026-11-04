<?php

namespace App\Http\Controllers\Api\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\PurchaseRequest;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseRequestController extends Controller
{
    private function companyId()
    {
        return auth()->user()
            ->branches()
            ->value('company_id');
    }

    private function accessibleBranch($branchId)
    {
        return auth()->user()
            ->branches()
            ->where('branches.id', $branchId)
            ->where('branches.company_id', $this->companyId())
            ->exists();
    }

    private function validateItems(array $items)
    {
        foreach ($items as $item) {
            $product = Product::where('company_id', $this->companyId())
                ->where('status', 'active')
                ->findOrFail($item['product_id']);

            $unit = Unit::where('status', 'active')
                ->findOrFail($item['unit_id']);

            $validUnit = DB::table('product_units')
                ->where('product_id', $product->id)
                ->where('unit_id', $unit->id)
                ->where('is_purchase_unit', true)
                ->where('status', 'active')
                ->exists();

            if (!$validUnit) {
                abort(422, "Selected unit is not an active purchase unit for product: {$product->name}");
            }
        }
    }

    public function index(Request $request)
    {
        $query = PurchaseRequest::with([
            'branch',
            'warehouse',
            'requester',
            'approver',
            'items.product',
            'items.unit',
        ])->whereHas('branch', function ($query) {
            $query->where('company_id', $this->companyId());
        });

        if ($request->filled('branch_id')) {
            if (!$this->accessibleBranch($request->branch_id)) {
                abort(403, 'You do not have access to this branch.');
            }

            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where('request_number', 'like', "%{$search}%");
        }

        return response()->json([
            'data' => $query->latest()->paginate(15),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'uuid', 'exists:branches,id'],
            'warehouse_id' => ['nullable', 'uuid', 'exists:warehouses,id'],

            'request_number' => [
                'required',
                'string',
                'max:255',
            ],

            'request_date' => ['required', 'date'],
            'required_date' => ['nullable', 'date', 'after_or_equal:request_date'],

            'priority' => [
                'required',
                Rule::in(['low', 'normal', 'high', 'urgent']),
            ],

            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.unit_id' => ['required', 'uuid', 'exists:units,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        if (!$this->accessibleBranch($validated['branch_id'])) {
            abort(403, 'You do not have access to this branch.');
        }

        if (!empty($validated['warehouse_id'])) {
            $warehouse = Warehouse::where('branch_id', $validated['branch_id'])
                ->findOrFail($validated['warehouse_id']);
        }

        $this->validateItems($validated['items']);

        $purchaseRequest = DB::transaction(function () use ($validated) {

            $purchaseRequest = PurchaseRequest::create([
                'branch_id' => $validated['branch_id'],
                'warehouse_id' => $validated['warehouse_id'] ?? null,
                'request_number' => $validated['request_number'],
                'request_date' => $validated['request_date'],
                'required_date' => $validated['required_date'] ?? null,
                'priority' => $validated['priority'],
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'requested_by' => auth()->id(),
            ]);

            $purchaseRequest->items()->createMany($validated['items']);

            return $purchaseRequest;
        });

        return response()->json([
            'message' => 'Purchase request created successfully.',
            'data' => $purchaseRequest->load([
                'branch',
                'warehouse',
                'requester',
                'items.product',
                'items.unit',
            ]),
        ], 201);
    }

    public function show($id)
    {
        $purchaseRequest = PurchaseRequest::with([
            'branch',
            'warehouse',
            'requester',
            'approver',
            'items.product',
            'items.unit',
            'purchaseOrders',
        ])
            ->whereHas('branch', function ($query) {
                $query->where('company_id', $this->companyId());
            })
            ->findOrFail($id);

        if (!$this->accessibleBranch($purchaseRequest->branch_id)) {
            abort(403, 'You do not have access to this branch.');
        }

        return response()->json([
            'data' => $purchaseRequest,
        ]);
    }

    public function update(Request $request, $id)
    {
        $purchaseRequest = PurchaseRequest::whereHas('branch', function ($query) {
            $query->where('company_id', $this->companyId());
        })->findOrFail($id);

        if (!$this->accessibleBranch($purchaseRequest->branch_id)) {
            abort(403, 'You do not have access to this branch.');
        }

        if ($purchaseRequest->status !== 'draft') {
            return response()->json([
                'message' => 'Only draft purchase requests can be updated.',
            ], 422);
        }

        $validated = $request->validate([
            'warehouse_id' => ['sometimes', 'nullable', 'uuid', 'exists:warehouses,id'],

            'request_number' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('purchase_requests', 'request_number')
                    ->where('branch_id', $purchaseRequest->branch_id)
                    ->ignore($purchaseRequest->id),
            ],

            'request_date' => ['sometimes', 'required', 'date'],
            'required_date' => ['sometimes', 'nullable', 'date'],
            'priority' => [
                'sometimes',
                'required',
                Rule::in(['low', 'normal', 'high', 'urgent']),
            ],

            'notes' => ['nullable', 'string'],

            'items' => ['sometimes', 'required', 'array', 'min:1'],
            'items.*.product_id' => ['required_with:items', 'uuid', 'exists:products,id'],
            'items.*.unit_id' => ['required_with:items', 'uuid', 'exists:units,id'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'gt:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        if (array_key_exists('warehouse_id', $validated) && $validated['warehouse_id'] !== null) {
            Warehouse::where('branch_id', $purchaseRequest->branch_id)
                ->findOrFail($validated['warehouse_id']);
        }

        if (isset($validated['items'])) {
            $this->validateItems($validated['items']);
        }

        DB::transaction(function () use ($purchaseRequest, $validated) {

            $items = $validated['items'] ?? null;

            $purchaseRequest->update(
                collect($validated)->except('items')->toArray()
            );

            if ($items !== null) {
                $purchaseRequest->items()->delete();
                $purchaseRequest->items()->createMany($items);
            }
        });

        return response()->json([
            'message' => 'Purchase request updated successfully.',
            'data' => $purchaseRequest->fresh()->load([
                'branch',
                'warehouse',
                'requester',
                'items.product',
                'items.unit',
            ]),
        ]);
    }

    public function submit($id)
    {
        $purchaseRequest = PurchaseRequest::whereHas('branch', function ($query) {
            $query->where('company_id', $this->companyId());
        })->findOrFail($id);

        if (!$this->accessibleBranch($purchaseRequest->branch_id)) {
            abort(403, 'You do not have access to this branch.');
        }

        if ($purchaseRequest->status !== 'draft') {
            return response()->json([
                'message' => 'Only draft purchase requests can be submitted.',
            ], 422);
        }

        $purchaseRequest->update([
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Purchase request submitted successfully.',
            'data' => $purchaseRequest->fresh()->load('items.product', 'items.unit'),
        ]);
    }

    public function approve($id)
    {
        $purchaseRequest = PurchaseRequest::whereHas('branch', function ($query) {
            $query->where('company_id', $this->companyId());
        })->findOrFail($id);

        if (!$this->accessibleBranch($purchaseRequest->branch_id)) {
            abort(403, 'You do not have access to this branch.');
        }

        if ($purchaseRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending purchase requests can be approved.',
            ], 422);
        }

        $purchaseRequest->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Purchase request approved successfully.',
            'data' => $purchaseRequest->fresh()->load([
                'requester',
                'approver',
                'items.product',
                'items.unit',
            ]),
        ]);
    }

    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ]);

        $purchaseRequest = PurchaseRequest::whereHas('branch', function ($query) {
            $query->where('company_id', $this->companyId());
        })->findOrFail($id);

        if (!$this->accessibleBranch($purchaseRequest->branch_id)) {
            abort(403, 'You do not have access to this branch.');
        }

        if ($purchaseRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending purchase requests can be rejected.',
            ], 422);
        }

        $purchaseRequest->update([
            'status' => 'rejected',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return response()->json([
            'message' => 'Purchase request rejected successfully.',
            'data' => $purchaseRequest->fresh()->load([
                'requester',
                'rejecter',
                'items.product',
                'items.unit',
            ]),
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:2000'],
        ]);

        $purchaseRequest = PurchaseRequest::whereHas('branch', function ($query) {
            $query->where('company_id', $this->companyId());
        })->findOrFail($id);

        if (!$this->accessibleBranch($purchaseRequest->branch_id)) {
            abort(403, 'You do not have access to this branch.');
        }

        if (!in_array($purchaseRequest->status, ['draft', 'pending'])) {
            return response()->json([
                'message' => 'Only draft or pending purchase requests can be canceled.',
            ], 422);
        }

        $purchaseRequest->update([
            'status' => 'canceled',
            'canceled_by' => auth()->id(),
            'canceled_at' => now(),
            'cancellation_reason' => $validated['cancellation_reason'],
        ]);

        return response()->json([
            'message' => 'Purchase request canceled successfully.',
            'data' => $purchaseRequest->fresh()->load([
                'requester',
                'canceler',
                'items.product',
                'items.unit',
            ]),
        ]);
    }
}
