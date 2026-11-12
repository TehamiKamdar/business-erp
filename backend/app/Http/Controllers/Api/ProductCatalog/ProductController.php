<?php

namespace App\Http\Controllers\Api\ProductCatalog;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    private function companyId(Request $request)
    {
        $companyIds = $request->user()
            ->branches()
            ->where('branches.status', 'active')
            ->pluck('company_id')
            ->unique()
            ->values();

        if ($companyIds->count() !== 1) {
            return null;
        }

        return $companyIds->first();
    }

    public function index(Request $request)
    {
        $companyId = $this->companyId($request);

        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Company context could not be resolved.',
            ], 403);
        }

        $products = Product::with([
                'category:id,name',
                'brand:id,name',
                'productUnits' => function ($query) {
                    $query->where('status', 'active')
                        ->with('unit:id,name,code');
                },
            ])
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully.',
            'data' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $this->companyId($request);

        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Company context could not be resolved.',
            ], 403);
        }

        $validated = $request->validate([
            'category_id' => [
                'required',
                'uuid',
                Rule::exists('categories', 'id')
                    ->where('company_id', $companyId)
                    ->where('status', 'active'),
            ],

            'brand_id' => [
                'nullable',
                'uuid',
                Rule::exists('brands', 'id')
                    ->where('company_id', $companyId)
                    ->where('status', 'active'),
            ],

            'base_unit_id' => [
                'required',
                'uuid',
                Rule::exists('units', 'id')
                    ->where('company_id', $companyId)
                    ->where('status', 'active'),
            ],

            'name' => ['required', 'string', 'max:255'],

            'sku' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'sku')
                    ->where('company_id', $companyId),
            ],

            'barcode' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'barcode')
                    ->where('company_id', $companyId),
            ],

            'description' => ['nullable', 'string'],

            'reorder_level' => [
                'nullable',
                'numeric',
                'min:0',
            ]
        ]);

        $product = DB::transaction(function () use ($validated, $companyId) {

            $product = Product::create([
                'company_id' => $companyId,
                'category_id' => $validated['category_id'],
                'brand_id' => $validated['brand_id'] ?? null,
                'name' => $validated['name'],
                'sku' => $validated['sku'],
                'barcode' => $validated['barcode'] ?? null,
                'description' => $validated['description'] ?? null,
                'reorder_level' => $validated['reorder_level'] ?? 0,
                'status' => 'active',
            ]);

            $product->productUnits()->create([
                'unit_id' => $validated['base_unit_id'],
                'conversion_factor' => 1,
                'is_base_unit' => true,
                'is_purchase_unit' => false,
                'is_sale_unit' => true,
                'status' => 'active',
            ]);

            return $product;
        });

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data' => $product->load([
                'category:id,name',
                'brand:id,name',
                'productUnits.unit:id,name,code',
            ]),
        ], 201);
    }

    public function show(Request $request, string $id)
    {
        $companyId = $this->companyId($request);

        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Company context could not be resolved.',
            ], 403);
        }

        $product = Product::with([
                'category:id,name',
                'brand:id,name',
                'productUnits' => function ($query) {
                    $query->with('unit:id,name,code')
                        ->with('productPrices');
                },
            ])
            ->where('company_id', $companyId)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully.',
            'data' => $product,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $companyId = $this->companyId($request);

        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Company context could not be resolved.',
            ], 403);
        }

        $product = Product::where('company_id', $companyId)
            ->where('status', '!=', 'archived')
            ->findOrFail($id);

        $validated = $request->validate([
            'category_id' => [
                'sometimes',
                'required',
                'uuid',
                Rule::exists('categories', 'id')
                    ->where('company_id', $companyId)
                    ->where('status', 'active'),
            ],

            'brand_id' => [
                'sometimes',
                'nullable',
                'uuid',
                Rule::exists('brands', 'id')
                    ->where('company_id', $companyId)
                    ->where('status', 'active'),
            ],

            'name' => ['sometimes', 'required', 'string', 'max:255'],

            'sku' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'sku')
                    ->where('company_id', $companyId)
                    ->ignore($product->id),
            ],

            'barcode' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'barcode')
                    ->where('company_id', $companyId)
                    ->ignore($product->id),
            ],

            'description' => ['sometimes', 'nullable', 'string'],

            'reorder_level' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => $product->fresh()->load([
                'category:id,name',
                'brand:id,name',
                'productUnits.unit:id,name,code',
            ]),
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $companyId = $this->companyId($request);

        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Company context could not be resolved.',
            ], 403);
        }

        $product = Product::where('company_id', $companyId)
            ->where('status', 'active')
            ->findOrFail($id);

        $product->update([
            'status' => 'archived',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product archived successfully.',
        ]);
    }

    public function activate(Request $request, string $id)
    {
        $companyId = $this->companyId($request);

        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Company context could not be resolved.',
            ], 403);
        }

        $product = Product::where('company_id', $companyId)
            ->where('status', '!=', 'active')
            ->findOrFail($id);

        $product->update([
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product activated successfully.',
        ]);
    }

    public function deactivate(Request $request, string $id)
    {
        $companyId = $this->companyId($request);

        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Company context could not be resolved.',
            ], 403);
        }

        $product = Product::where('company_id', $companyId)
            ->where('status', '!=', 'inactive')
            ->where('status', '!=', 'archived')
            ->findOrFail($id);

        $product->update([
            'status' => 'inactive',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product deactivated successfully.',
        ]);
    }
}
