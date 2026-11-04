<?php

namespace App\Http\Controllers\Api\ProductCatalog;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Http\Request;

class ProductPriceController extends Controller
{
    private function companyId()
    {
        $companyIds = auth()->user()
            ->branches()
            ->pluck('company_id')
            ->unique()
            ->values();

        abort_if($companyIds->count() !== 1, 403, 'Company context not found.');

        return $companyIds->first();
    }

    private function findProduct($productId)
    {
        return Product::where('id', $productId)
            ->where('company_id', $this->companyId())
            ->where('status', 'active')
            ->firstOrFail();
    }

    private function findProductUnit($product, $productUnitId)
    {
        return ProductUnit::where('id', $productUnitId)
            ->where('product_id', $product->id)
            ->where('status', 'active')
            ->firstOrFail();
    }

    public function index($productId, $productUnitId)
    {
        $product = $this->findProduct($productId);
        $productUnit = $this->findProductUnit($product, $productUnitId);

        $prices = $productUnit->productPrices()
            ->latest()
            ->paginate(15);

        return response()->json($prices);
    }

    public function store(Request $request, $productId, $productUnitId)
    {
        $product = $this->findProduct($productId);
        $productUnit = $this->findProductUnit($product, $productUnitId);

        $validated = $request->validate([
            'price_type' => [
                'required',
                'string',
                'max:50',
            ],
            'price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'minimum_quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'effective_from' => [
                'required',
                'date',
            ],
        ]);

        $exists = $productUnit->productPrices()
            ->where('price_type', $validated['price_type'])
            ->where('minimum_quantity', $validated['minimum_quantity'])
            ->whereDate('effective_from', $validated['effective_from'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'A price already exists for this unit, price type, quantity and effective date.',
            ], 422);
        }

        $price = $productUnit->productPrices()->create([
            ...$validated,
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Product price created successfully.',
            'data' => $price,
        ], 201);
    }

    public function show($productId, $productUnitId, $id)
    {
        $product = $this->findProduct($productId);
        $productUnit = $this->findProductUnit($product, $productUnitId);

        $price = $productUnit->productPrices()
            ->where('id', $id)
            ->firstOrFail();

        return response()->json($price);
    }

    public function update(Request $request, $productId, $productUnitId, $id)
    {
        $product = $this->findProduct($productId);
        $productUnit = $this->findProductUnit($product, $productUnitId);

        $price = $productUnit->productPrices()
            ->where('id', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'price_type' => [
                'sometimes',
                'required',
                'string',
                'max:50',
            ],
            'price' => [
                'sometimes',
                'required',
                'numeric',
                'min:0',
            ],
            'minimum_quantity' => [
                'sometimes',
                'required',
                'numeric',
                'gt:0',
            ],
            'effective_from' => [
                'sometimes',
                'required',
                'date',
            ],
        ]);

        $merged = array_merge($price->only([
            'price_type',
            'minimum_quantity',
            'effective_from',
        ]), $validated);

        $exists = $productUnit->productPrices()
            ->where('id', '!=', $price->id)
            ->where('price_type', $merged['price_type'])
            ->where('minimum_quantity', $merged['minimum_quantity'])
            ->whereDate('effective_from', $merged['effective_from'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'A price already exists for this unit, price type, quantity and effective date.',
            ], 422);
        }

        $price->update($validated);

        return response()->json([
            'message' => 'Product price updated successfully.',
            'data' => $price->fresh(),
        ]);
    }

    public function destroy($productId, $productUnitId, $id)
    {
        $product = $this->findProduct($productId);
        $productUnit = $this->findProductUnit($product, $productUnitId);

        $price = $productUnit->productPrices()
            ->where('id', $id)
            ->where('status', '!=', 'archived')
            ->firstOrFail();

        $price->update([
            'status' => 'archived',
        ]);

        return response()->json([
            'message' => 'Product price archived successfully.',
        ]);
    }

    public function activate($productId, $productUnitId, $id)
    {
        $product = $this->findProduct($productId);
        $productUnit = $this->findProductUnit($product, $productUnitId);

        $price = $productUnit->productPrices()
            ->where('id', $id)
            ->where('status', '!=', 'active')
            ->firstOrFail();

        $price->update([
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Product price activated successfully.',
        ]);
    }

    public function deactivate($productId, $productUnitId, $id)
    {
        $product = $this->findProduct($productId);
        $productUnit = $this->findProductUnit($product, $productUnitId);

        $price = $productUnit->productPrices()
            ->where('id', $id)
            ->where('status', 'active')
            ->firstOrFail();

        $price->update([
            'status' => 'inactive',
        ]);

        return response()->json([
            'message' => 'Product price deactivated successfully.',
        ]);
    }
}