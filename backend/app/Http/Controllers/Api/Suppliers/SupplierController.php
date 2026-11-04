<?php

namespace App\Http\Controllers\Api\Suppliers;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'postal_code' => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $supplier = DB::transaction(function () use ($validated, $companyId) {

            $supplier = Supplier::create([
                ...collect($validated)->except([
                    'address',
                    'city',
                    'country',
                    'postal_code',
                    'contact_person',
                    'state',
                ])->toArray(),
                'company_id' => $companyId,
                'opening_balance' => $validated['opening_balance'] ?? 0,
                'status' => 'active',
            ]);

            $supplier->addresses()->create([
                'type' => 'both',
                'phone' => $validated['phone'] ?? null,
                'contact_person' => $validated['contact_person'] ?? null,
                'address_line_1' => $validated['address'],
                'city' => $validated['city'],
                'country' => $validated['country'],
                'state' => $validated['state'],
                'postal_code' => $validated['postal_code'],
                'is_default' => true,
            ]);

            return $supplier;
        });

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
            'opening_balance' => ['sometimes', 'required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],

            // Address fields
            'address' => ['sometimes', 'required', 'string'],
            'city' => ['sometimes', 'required', 'string', 'max:255'],
            'country' => ['sometimes', 'required', 'string', 'max:255'],
            'state' => ['sometimes', 'string', 'max:255'],
            'contact_person' => ['sometimes', 'string', 'max:255'],
            'postal_code' => ['sometimes', 'string', 'max:20'],
        ]);

        DB::transaction(function () use ($supplier, $validated) {

            // Update supplier information
            $supplierData = collect($validated)->except([
                'address',
                'city',
                'country',
                'state',
                'postal_code',
                'contact_person',
            ])->toArray();

            if (!empty($supplierData)) {
                $supplier->update($supplierData);
            }

            // Update default supplier address if address fields are provided
            $addressData = collect($validated)->only([
                'address',
                'city',
                'country',
                'state',
                'postal_code',
                'contact_person',
            ])->toArray();

            if (!empty($addressData)) {

                $defaultAddress = $supplier->addresses()
                    ->where('is_default', true)
                    ->first();

                $addressPayload = [
                    'address_line_1' => $addressData['address'] ?? $defaultAddress?->address_line_1,
                    'city' => $addressData['city'] ?? $defaultAddress?->city,
                    'country' => $addressData['country'] ?? $defaultAddress?->country,
                    'state' => $addressData['state'] ?? $defaultAddress?->state,
                    'postal_code' => $addressData['postal_code'] ?? $defaultAddress?->postal_code,
                    'contact_person' => $addressData['contact_person'] ?? $defaultAddress?->contact_person,
                ];

                if ($defaultAddress) {
                    $defaultAddress->update($addressPayload);
                } else {
                    $supplier->addresses()->create([
                        ...$addressPayload,
                        'type' => 'both',
                        'is_default' => true,
                    ]);
                }
            }
        });

        return response()->json([
            'message' => 'Supplier updated successfully.',
            'data' => $supplier->fresh()->load('addresses'),
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