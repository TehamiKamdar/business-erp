<?php

namespace App\Http\Controllers\Api\Suppliers;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SupplierAddressController extends Controller
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

    private function findSupplier($supplierId)
    {
        return Supplier::where('id', $supplierId)
            ->where('company_id', $this->companyId())
            ->where('status', 'active')
            ->firstOrFail();
    }

    public function index($supplierId)
    {
        $supplier = $this->findSupplier($supplierId);

        $addresses = $supplier->addresses()
            ->latest()
            ->get();

        return response()->json($addresses);
    }

    public function store(Request $request, $supplierId)
    {
        $supplier = $this->findSupplier($supplierId);

        $validated = $request->validate([
            'type' => [
                'required',
                'string',
                Rule::in(['billing', 'shipping', 'both']),
            ],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'country' => ['required', 'string', 'max:255'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        $address = DB::transaction(function () use ($supplier, $validated) {

            if ($validated['is_default'] ?? false) {
                $supplier->addresses()->update([
                    'is_default' => false,
                ]);
            }

            return $supplier->addresses()->create($validated);
        });

        return response()->json([
            'message' => 'Supplier address created successfully.',
            'data' => $address,
        ], 201);
    }

    public function show($supplierId, $id)
    {
        $supplier = $this->findSupplier($supplierId);

        $address = $supplier->addresses()
            ->where('id', $id)
            ->firstOrFail();

        return response()->json($address);
    }

    public function update(Request $request, $supplierId, $id)
    {
        $supplier = $this->findSupplier($supplierId);

        $address = $supplier->addresses()
            ->where('id', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'type' => [
                'sometimes',
                'required',
                'string',
                Rule::in(['billing', 'shipping', 'both']),
            ],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address_line_1' => ['sometimes', 'required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'country' => ['sometimes', 'required', 'string', 'max:255'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        DB::transaction(function () use ($supplier, $address, $validated) {

            if ($validated['is_default'] ?? false) {
                $supplier->addresses()
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }

            $address->update($validated);
        });

        return response()->json([
            'message' => 'Supplier address updated successfully.',
            'data' => $address->fresh(),
        ]);
    }

    public function destroy($supplierId, $id)
    {
        $supplier = $this->findSupplier($supplierId);

        $address = $supplier->addresses()
            ->where('id', $id)
            ->firstOrFail();

        $address->delete();

        return response()->json([
            'message' => 'Supplier address deleted successfully.',
        ]);
    }
}