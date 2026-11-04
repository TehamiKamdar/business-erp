<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
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

    public function index()
    {
        $suppliers = Supplier::where('company_id', $this->companyId())
            ->where('status', 'active')
            ->with('addresses')
            ->latest()
            ->paginate(15);

        return response()->json($suppliers);
    }

    public function store(Request $request)
    {
        $companyId = $this->companyId();

        $validated = $request->validate([
            'supplier_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('suppliers', 'supplier_code')
                    ->where('company_id', $companyId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $supplier = Supplier::create([
            ...$validated,
            'company_id' => $companyId,
            'opening_balance' => $validated['opening_balance'] ?? 0,
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Supplier created successfully.',
            'data' => $supplier,
        ], 201);
    }

    public function show($id)
    {
        $supplier = Supplier::where('company_id', $this->companyId())
            ->with('addresses')
            ->findOrFail($id);

        return response()->json($supplier);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::where('company_id', $this->companyId())
            ->where('status', '!=', 'archived')
            ->findOrFail($id);

        $validated = $request->validate([
            'supplier_code' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('suppliers', 'supplier_code')
                    ->where('company_id', $this->companyId())
                    ->ignore($supplier->id),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'opening_balance' => ['sometimes', 'required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $supplier->update($validated);

        return response()->json([
            'message' => 'Supplier updated successfully.',
            'data' => $supplier->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $supplier = Supplier::where('company_id', $this->companyId())
            ->where('status', '!=', 'archived')
            ->findOrFail($id);

        $supplier->update(['status' => 'archived']);

        return response()->json([
            'message' => 'Supplier archived successfully.',
        ]);
    }

    public function activate($id)
    {
        $supplier = Supplier::where('company_id', $this->companyId())
            ->where('status', '!=', 'active')
            ->findOrFail($id);

        $supplier->update(['status' => 'active']);

        return response()->json([
            'message' => 'Supplier activated successfully.',
        ]);
    }

    public function deactivate($id)
    {
        $supplier = Supplier::where('company_id', $this->companyId())
            ->where('status', '!=', 'inactive')
            ->findOrFail($id);

        $supplier->update(['status' => 'inactive']);

        return response()->json([
            'message' => 'Supplier deactivated successfully.',
        ]);
    }
}