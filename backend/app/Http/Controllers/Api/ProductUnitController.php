<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductUnitController extends Controller
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

    private function findProduct(string $productId, $companyId)
    {
        return Product::where('company_id', $companyId)
            ->where('status', 'active')
            ->findOrFail($productId);
    }

    public function index(Request $request, string $productId)
    {
        $companyId = $this->companyId($request);

        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Company context could not be resolved.',
            ], 403);
        }

        $product = $this->findProduct($productId, $companyId);

        $productUnits = $product->productUnits()
            ->with('unit:id,name,code')
            ->where('status', 'active')
            ->orderByDesc('is_base_unit')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Product units retrieved successfully.',
            'data' => $productUnits,
        ]);
    }

    public function store(Request $request, string $productId)
    {
        $companyId = $this->companyId($request);

        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Company context could not be resolved.',
            ], 403);
        }

        $product = $this->findProduct($productId, $companyId);

        $validated = $request->validate([
            'unit_id' => [
                'required',
                'uuid',
                Rule::exists('units', 'id')
                    ->where('company_id', $companyId)
                    ->where('status', 'active'),

                Rule::unique('product_units', 'unit_id')
                    ->where('product_id', $product->id),
            ],

            'conversion_factor' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'is_purchase_unit' => [
                'sometimes',
                'boolean',
            ],

            'is_sale_unit' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $baseUnitExists = $product->productUnits()
            ->where('is_base_unit', true)
            ->where('status', 'active')
            ->exists();

        if (!$baseUnitExists) {
            return response()->json([
                'success' => false,
                'message' => 'Product base unit is not configured.',
            ], 422);
        }

        $productUnit = $product->productUnits()->create([
            'unit_id' => $validated['unit_id'],
            'conversion_factor' => $validated['conversion_factor'],
            'is_base_unit' => false,
            'is_purchase_unit' => $validated['is_purchase_unit'] ?? false,
            'is_sale_unit' => $validated['is_sale_unit'] ?? false,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product unit created successfully.',
            'data' => $productUnit->load('unit:id,name,code'),
        ], 201);
    }

    public function show(Request $request, string $productId, string $id)
    {
        $companyId = $this->companyId($request);

        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Company context could not be resolved.',
            ], 403);
        }

        $product = $this->findProduct($productId, $companyId);

        $productUnit = $product->productUnits()
            ->with('unit:id,name,code', 'productPrices')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Product unit retrieved successfully.',
            'data' => $productUnit,
        ]);
    }

    public function update(Request $request, string $productId, string $id)
    {
        $companyId = $this->companyId($request);

        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Company context could not be resolved.',
            ], 403);
        }

        $product = $this->findProduct($productId, $companyId);

        $productUnit = $product->productUnits()->findOrFail($id);

        if ($productUnit->is_base_unit) {
            return response()->json([
                'success' => false,
                'message' => 'Base unit cannot be modified here.',
            ], 422);
        }

        $validated = $request->validate([
            'unit_id' => [
                'sometimes',
                'required',
                'uuid',

                Rule::exists('units', 'id')
                    ->where('company_id', $companyId)
                    ->where('status', 'active'),

                Rule::unique('product_units', 'unit_id')
                    ->where('product_id', $product->id)
                    ->ignore($productUnit->id),
            ],

            'conversion_factor' => [
                'sometimes',
                'required',
                'numeric',
                'gt:0',
            ],

            'is_purchase_unit' => [
                'sometimes',
                'boolean',
            ],

            'is_sale_unit' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $productUnit->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product unit updated successfully.',
            'data' => $productUnit->fresh()->load('unit:id,name,code'),
        ]);
    }

    public function destroy(Request $request, string $productId, string $id)
    {
        $companyId = $this->companyId($request);

        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Company context could not be resolved.',
            ], 403);
        }

        $product = $this->findProduct($productId, $companyId);

        $productUnit = $product->productUnits()
            ->where('status', 'active')
            ->findOrFail($id);

        if ($productUnit->is_base_unit) {
            return response()->json([
                'success' => false,
                'message' => 'Base unit cannot be archived.',
            ], 422);
        }

        $productUnit->update([
            'status' => 'archived',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product unit archived successfully.',
        ]);
    }

    public function activate(Request $request, string $productId, string $id)
    {
        $companyId = $this->companyId($request);

        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Company context could not be resolved.',
            ], 403);
        }

        $product = $this->findProduct($productId, $companyId);

        $productUnit = $product->productUnits()
            ->where('status', '!=', 'active')
            ->findOrFail($id);

        $productUnit->update([
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product unit activated successfully.',
        ]);
    }

    public function deactivate(Request $request, string $productId, string $id)
    {
        $companyId = $this->companyId($request);

        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Company context could not be resolved.',
            ], 403);
        }

        $product = $this->findProduct($productId, $companyId);

        $productUnit = $product->productUnits()
            ->where('status', '!=', 'inactive')
            ->findOrFail($id);

        $productUnit->update([
            'status' => 'inactive',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product unit deactivated successfully.',
        ]);
    }
}
