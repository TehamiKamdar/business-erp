<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Models\CustomerPrice;
use App\Models\ProductUnit;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerPriceController extends Controller
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

        abort_if($companyIds->count() !== 1, 403, 'Company context not found.');

        return $companyIds->first();
    }

    public function index(Request $request)
    {
        $companyId = $this->companyId();

        $prices = CustomerPrice::with([
            'customer',
            'productUnit.product',
            'productUnit.unit',
        ])
            ->whereHas('customer', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->when($request->customer_id, fn ($q, $id) =>
                $q->where('customer_id', $id)
            )
            ->when($request->product_unit_id, fn ($q, $id) =>
                $q->where('product_unit_id', $id)
            )
            ->when($request->status !== null, fn ($q) =>
                $q->where('status', $request->boolean('status'))
            )
            ->latest()
            ->paginate(15);

        return response()->json($prices);
    }

    public function store(Request $request)
    {
        $companyId = $this->companyId();

        $validated = $request->validate([
            'customer_id' => [
                'required',
                'uuid',
                Rule::exists('customers', 'id')
                    ->where('company_id', $companyId),
            ],
            'product_unit_id' => [
                'required',
                'uuid',
                function ($attribute, $value, $fail) use ($companyId) {
                    $exists = ProductUnit::where('id', $value)
                        ->whereHas('product', function ($query) use ($companyId) {
                            $query->where('company_id', $companyId);
                        })
                        ->exists();

                    if (! $exists) {
                        $fail('The selected product unit is invalid for this company.');
                    }
                },
            ],
            'price' => ['required', 'numeric', 'min:0'],
            'min_quantity' => ['required', 'numeric', 'gt:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'effective_from' => ['nullable', 'date'],
            'effective_until' => [
                'nullable',
                'date',
                'after_or_equal:effective_from',
            ],
            'status' => ['sometimes', 'boolean'],
        ]);

        $price = DB::transaction(function () use ($validated, $companyId) {
            $price = CustomerPrice::create([
                ...$validated,
                'currency' => $validated['currency'] ?? 'PKR',
                'status' => $validated['status'] ?? true,
            ]);

            $this->auditService->log(
                $price,
                'created',
                null,
                $price->toArray(),
                'Customer-specific product price created.',
                [],
                auth()->user(),
                $companyId
            );

            return $price;
        });

        return response()->json([
            'message' => 'Customer price created successfully.',
            'data' => $price->load([
                'customer',
                'productUnit.product',
                'productUnit.unit',
            ]),
        ], 201);
    }

    public function show(string $priceId)
    {
        $companyId = $this->companyId();

        $price = CustomerPrice::with([
            'customer',
            'productUnit.product',
            'productUnit.unit',
        ])
            ->whereHas('customer', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->findOrFail($priceId);

        return response()->json($price);
    }

    public function update(Request $request, string $priceId)
    {
        $companyId = $this->companyId();

        $price = CustomerPrice::whereHas('customer', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->findOrFail($priceId);

        $validated = $request->validate([
            'customer_id' => [
                'sometimes',
                'nullable',
                'uuid',
                Rule::exists('customers', 'id')
                    ->where('company_id', $companyId),
            ],
            'product_unit_id' => [
                'nullable',
                'uuid',
                function ($attribute, $value, $fail) use ($companyId) {
                    $exists = ProductUnit::where('id', $value)
                        ->whereHas('product', function ($query) use ($companyId) {
                            $query->where('company_id', $companyId);
                        })
                        ->exists();

                    if (! $exists) {
                        $fail('The selected product unit is invalid for this company.');
                    }
                },
            ],
            'price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'min_quantity' => ['sometimes', 'nullable', 'numeric', 'gt:0'],
            'currency' => ['sometimes', 'nullable', 'string', 'size:3'],
            'effective_from' => ['nullable', 'date'],
            'effective_until' => [
                'nullable',
                'date',
                'after_or_equal:effective_from',
            ],
            'status' => ['sometimes', 'boolean'],
        ]);

        $oldValues = $price->getOriginal();

        DB::transaction(function () use ($price, $validated, $oldValues, $companyId) {
            $price->update($validated);

            $this->auditService->log(
                $price,
                'updated',
                $oldValues,
                $price->fresh()->toArray(),
                'Customer-specific product price updated.',
                [],
                auth()->user(),
                $companyId
            );
        });

        return response()->json([
            'message' => 'Customer price updated successfully.',
            'data' => $price->fresh()->load([
                'customer',
                'productUnit.product',
                'productUnit.unit',
            ]),
        ]);
    }

    public function destroy(string $priceId)
    {
        $companyId = $this->companyId();

        $price = CustomerPrice::whereHas('customer', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->findOrFail($priceId);

        DB::transaction(function () use ($price, $companyId) {
            $oldValues = $price->toArray();

            $this->auditService->log(
                $price,
                'deleted',
                $oldValues,
                null,
                'Customer-specific product price deleted.',
                [],
                auth()->user(),
                $companyId
            );

            $price->delete();
        });

        return response()->json([
            'message' => 'Customer price deleted successfully.',
        ]);
    }
}
