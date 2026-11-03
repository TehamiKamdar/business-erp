<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    private function companyId(Request $request): string
    {
        $user = $request->user();

        $companyIds = $user->branches()
            ->where('branches.status', 'active')
            ->pluck('branches.company_id')
            ->unique();

        if ($companyIds->count() !== 1) {
            abort(403, 'Unable to determine company context.');
        }

        return $companyIds->first();
    }

    private function hasCircularParent(Category $category, string $parentId): bool
    {
        $parent = Category::where('company_id', $category->company_id)
            ->find($parentId);

        while ($parent) {
            if ($parent->id === $category->id) {
                return true;
            }

            $parent = $parent->parent;
        }

        return false;
    }

    public function index(Request $request)
    {
        $companyId = $this->companyId($request);

        $categories = Category::where('company_id', $companyId)
            ->with('parent:id,name')
            ->where('status', 'active')
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $this->companyId($request);

        $validated = $request->validate([
            'parent_id' => [
                'nullable',
                'uuid',
                Rule::exists('categories', 'id')
                    ->where('company_id', $companyId)
                    ->where('status', 'active'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $slug = Str::slug($validated['name']);

        $category = Category::create([
            'company_id' => $companyId,
            'parent_id' => $validated['parent_id'] ?? null,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'data' => $category,
        ], 201);
    }

    public function show(Request $request, string $id)
    {
        $category = Category::where('company_id', $this->companyId($request))
            ->with('parent:id,name')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $companyId = $this->companyId($request);

        $category = Category::where('company_id', $companyId)
            ->findOrFail($id);

        $validated = $request->validate([
            'parent_id' => [
                'nullable',
                'uuid',
                Rule::exists('categories', 'id')
                    ->where('company_id', $companyId)
                    ->where('status', 'active'),
                Rule::notIn([$category->id]),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if (!empty($validated['parent_id'])) {
            if ($this->hasCircularParent($category, $validated['parent_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This category cannot be assigned under one of its own subcategories.',
                ], 422);
            }
        }

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
            'data' => $category->fresh(),
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $category = Category::where('company_id', $this->companyId($request))
            ->findOrFail($id);

        $category->update([
            'status' => 'archived',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category archived successfully.',
        ]);
    }

    public function activate(Request $request, string $id)
    {
        $category = Category::where('company_id', $this->companyId($request))
            ->where('status', '!=', 'active')
            ->findOrFail($id);

        $category->update([
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category activated successfully.',
            'data' => $category,
        ]);
    }

    public function deactivate(Request $request, string $id)
    {
        $category = Category::where('company_id', $this->companyId($request))
            ->where('status', '!=', 'active')
            ->findOrFail($id);

        $category->update([     
            'status' => 'inactive',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category deactivated successfully.',
            'data' => $category,
        ]);
    }
}
