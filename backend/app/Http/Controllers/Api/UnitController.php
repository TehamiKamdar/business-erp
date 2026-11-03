<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
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
        $units = Unit::where('company_id', $this->companyId($request))
            ->where('status', 'active')
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $units,
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $this->companyId($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('units', 'code')
                    ->where('company_id', $companyId),
            ],
        ]);

        $unit = Unit::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
            'code' => $validated['code'],
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Unit created successfully.',
            'data' => $unit,
        ], 201);
    }

    public function show(Request $request, string $id)
    {
        $unit = Unit::where('company_id', $this->companyId($request))
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $unit,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $companyId = $this->companyId($request);

        $unit = Unit::where('company_id', $companyId)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('units', 'code')
                    ->where('company_id', $companyId)
                    ->ignore($unit->id),
            ],
        ]);

        $unit->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Unit updated successfully.',
            'data' => $unit->fresh(),
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $unit = Unit::where('company_id', $this->companyId($request))
            ->findOrFail($id);

        $unit->update([
            'status' => 'archived',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Unit archived successfully.',
        ]);
    }

    public function activate(Request $request, string $id)
    {
        $unit = Unit::where('company_id', $this->companyId($request))
            ->where('status', '!=', 'active')
            ->findOrFail($id);

        $unit->update([
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Unit activated successfully.',
            'data' => $unit,
        ]);
    }

    public function deactivate(Request $request, string $id)
    {
        $unit = Unit::where('company_id', $this->companyId($request))
            ->where('status', '!=', 'active')
            ->findOrFail($id);

        $unit->update([
            'status' => 'inactive',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Unit deactivated successfully.',
            'data' => $unit,
        ]);
    }
}
