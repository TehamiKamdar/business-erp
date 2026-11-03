<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    private function companyId(Request $request): string
    {
        $companyIds = $request->user()
            ->branches()
            ->where('branches.status', 'active')
            ->pluck('branches.company_id')
            ->unique();

        if ($companyIds->count() !== 1) {
            abort(403, 'Unable to determine company context.');
        }

        return $companyIds->first();
    }

    public function index(Request $request)
    {
        $brands = Brand::where('company_id', $this->companyId($request))
            ->where('status', 'active')
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $brands,
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $this->companyId($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $slug = Str::slug($validated['name']);

        $request->validate([
            'name' => [
                Rule::unique('brands', 'slug')
                    ->where('company_id', $companyId)
                    ->where('slug', $slug),
            ],
        ]);

        $brand = Brand::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Brand created successfully.',
            'data' => $brand,
        ], 201);
    }

    public function show(Request $request, string $id)
    {
        $brand = Brand::where('company_id', $this->companyId($request))
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $brand,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $companyId = $this->companyId($request);

        $brand = Brand::where('company_id', $companyId)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        if (isset($validated['name'])) {
            $slug = Str::slug($validated['name']);

            $request->validate([
                'name' => [
                    Rule::unique('brands', 'slug')
                        ->where('company_id', $companyId)
                        ->where('slug', $slug)
                        ->ignore($brand->id, 'id'),
                ],
            ]);

            $validated['slug'] = $slug;
        }

        $brand->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Brand updated successfully.',
            'data' => $brand->fresh(),
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $brand = Brand::where('company_id', $this->companyId($request))
            ->findOrFail($id);

        $brand->update([
            'status' => 'archived',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Brand archived successfully.',
        ]);
    }

    public function activate(Request $request, string $id)
    {
        $brand = Brand::where('company_id', $this->companyId($request))
            ->where('status', '!=', 'active')
            ->findOrFail($id);

        $brand->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => 'Brand activated successfully.',
            'data' => $brand,
        ]);
    }

    public function deactivate(Request $request, string $id)
    {
        $brand = Brand::where('company_id', $this->companyId($request))
            ->where('status', 'active')
            ->findOrFail($id);

        $brand->update(['status' => 'inactive']);

        return response()->json([
            'success' => true,
            'message' => 'Brand deactivated successfully.',
            'data' => $brand,
        ]);
    }
}
