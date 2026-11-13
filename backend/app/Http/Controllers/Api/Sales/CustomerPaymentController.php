<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CashBankAccount;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\PaymentMethod;
use App\Models\SalesInvoice;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\CustomerPaymentAllocation;

class CustomerPaymentController extends Controller
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

    private function authorizeBranch(string $branchId): void
    {
        $hasAccess = auth()->user()
            ->branches()
            ->where('branches.id', $branchId)
            ->exists();

        abort_unless($hasAccess, 403, 'You do not have access to this branch.');
    }
    
    public function index(Request $request)
    {
        $companyId = $this->companyId();

        $payments = CustomerPayment::with([
            'customer',
            'branch',
            'cashBankAccount',
            'paymentMethod',
            'creator',
            'allocations.salesInvoice',
        ])
            ->whereHas('customer', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->when($request->branch_id, function ($query, $branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when($request->customer_id, function ($query, $customerId) {
                $query->where('customer_id', $customerId);
            })
            ->when($request->cash_bank_account_id, function ($query, $accountId) {
                $query->where('cash_bank_account_id', $accountId);
            })
            ->when($request->payment_method_id, function ($query, $methodId) {
                $query->where('payment_method_id', $methodId);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->from_date, function ($query, $date) {
                $query->whereDate('payment_date', '>=', $date);
            })
            ->when($request->to_date, function ($query, $date) {
                $query->whereDate('payment_date', '<=', $date);
            })
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('receipt_number', 'like', "%{$search}%")
                        ->orWhere('reference_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('payment_date')
            ->paginate(15);

        return response()->json($payments);
    }

    public function store(Request $request)
    {
        $companyId = $this->companyId();

        $validated = $request->validate([
            'customer_id' => ['required', 'uuid', 'exists:customers,id'],
            'branch_id' => ['required', 'uuid', 'exists:branches,id'],
            'cash_bank_account_id' => ['nullable', 'uuid', 'exists:cash_bank_accounts,id'],
            'payment_method_id' => ['nullable', 'uuid', 'exists:payment_methods,id'],
            'receipt_number' => ['required', 'string', 'max:100'],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency' => ['required', 'string', 'size:3'],
            'reference_number' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.sales_invoice_id' => [
                'required',
                'uuid',
                'distinct',
                'exists:sales_invoices,id',
            ],
            'allocations.*.allocated_amount' => [
                'required',
                'numeric',
                'gt:0',
            ],
        ]);

        $payment = DB::transaction(function () use ($validated, $companyId) {

            $customer = Customer::where('company_id', $companyId)
                ->whereKey($validated['customer_id'])
                ->firstOrFail();

            $branch = Branch::where('company_id', $companyId)
                ->where('company_id', $companyId)
                ->whereKey($validated['branch_id'])
                ->firstOrFail();

            $this->authorizeBranch($branch->id);

            $currency = strtoupper($validated['currency']);

            if (!empty($validated['cash_bank_account_id'])) {
                CashBankAccount::where('company_id', $companyId)
                    ->where('branch_id', $branch->id)
                    ->where('currency', $currency)
                    ->where('status', true)
                    ->whereKey($validated['cash_bank_account_id'])
                    ->firstOrFail();
            }

            if (!empty($validated['payment_method_id'])) {
                PaymentMethod::where('company_id', $companyId)
                    ->where('status', true)
                    ->whereKey($validated['payment_method_id'])
                    ->firstOrFail();
            }

            $allocations = collect($validated['allocations']);

            $allocatedTotal = round(
                (float) $allocations->sum('allocated_amount'),
                2
            );

            if ($allocatedTotal !== round((float) $validated['amount'], 2)) {
                throw ValidationException::withMessages([
                    'allocations' => 'Total allocated amount must equal payment amount.',
                ]);
            }

            $invoiceIds = $allocations->pluck('sales_invoice_id')->unique();

            $invoices = SalesInvoice::whereIn('id', $invoiceIds)
                ->where('customer_id', $customer->id)
                ->where('branch_id', $branch->id)
                ->where('currency', $currency)
                ->whereIn('status', ['approved', 'partially_paid'])
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($invoices->count() !== $invoiceIds->count()) {
                throw ValidationException::withMessages([
                    'allocations' => 'One or more invoices are invalid or do not belong to this customer, branch and currency.',
                ]);
            }

            foreach ($allocations as $allocation) {
                $invoice = $invoices->get($allocation['sales_invoice_id']);

                $alreadyAllocated = CustomerPaymentAllocation::where(
                    'sales_invoice_id',
                    $invoice->id
                )
                    ->whereHas('customerPayment', function ($query) {
                        $query->whereIn('status', ['draft', 'posted']);
                    })
                    ->sum('allocated_amount');

                $availableBalance = round(
                    (float) $invoice->total_amount - (float) $alreadyAllocated,
                    2
                );

                if (
                    round((float) $allocation['allocated_amount'], 2) >
                    $availableBalance
                ) {
                    throw ValidationException::withMessages([
                        'allocations' => "Allocated amount exceeds available balance of invoice {$invoice->invoice_number}.",
                    ]);
                }
            }

            $payment = CustomerPayment::create([
                'customer_id' => $customer->id,
                'branch_id' => $branch->id,
                'cash_bank_account_id' => $validated['cash_bank_account_id'] ?? null,
                'payment_method_id' => $validated['payment_method_id'] ?? null,
                'receipt_number' => $validated['receipt_number'],
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'currency' => $currency,
                'reference_number' => $validated['reference_number'] ?? null,
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $this->auditService->log(
                $payment,
                'created',
                null,
                $payment->getAttributes(),
                'Customer payment created.',
                [],
                null,
                $companyId,
                $branch->id
            );

            foreach ($allocations as $allocation) {
                $paymentAllocation = CustomerPaymentAllocation::create([
                    'customer_payment_id' => $payment->id,
                    'sales_invoice_id' => $allocation['sales_invoice_id'],
                    'allocated_amount' => $allocation['allocated_amount'],
                ]);

                $this->auditService->log(
                    $paymentAllocation,
                    'created',
                    null,
                    $paymentAllocation->getAttributes(),
                    'Customer payment allocation created.',
                    [],
                    null,
                    $companyId,
                    $branch->id
                );
            }

            return $payment;
        });

        return response()->json([
            'message' => 'Customer payment draft created successfully.',
            'data' => $payment->load([
                'customer',
                'branch',
                'cashBankAccount',
                'paymentMethod',
                'creator',
                'allocations.salesInvoice',
            ]),
        ], 201);
    }

    public function show(string $paymentId)
    {
        $companyId = $this->companyId();

        $payment = CustomerPayment::with([
            'customer',
            'branch',
            'cashBankAccount',
            'paymentMethod',
            'creator',
            'allocations.salesInvoice',
        ])
            ->whereHas('customer', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->whereKey($paymentId)
            ->firstOrFail();

        $this->authorizeBranch($payment->branch_id);

        return response()->json([
            'data' => $payment,
        ]);
    }

    public function update(Request $request, string $paymentId)
    {
        $companyId = $this->companyId();

        $validated = $request->validate([
            'customer_id' => ['required', 'uuid', 'exists:customers,id'],
            'branch_id' => ['required', 'uuid', 'exists:branches,id'],
            'cash_bank_account_id' => ['nullable', 'uuid', 'exists:cash_bank_accounts,id'],
            'payment_method_id' => ['nullable', 'uuid', 'exists:payment_methods,id'],
            'receipt_number' => ['required', 'string', 'max:100'],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency' => ['required', 'string', 'size:3'],
            'reference_number' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.sales_invoice_id' => [
                'required',
                'uuid',
                'distinct',
                'exists:sales_invoices,id',
            ],
            'allocations.*.allocated_amount' => [
                'required',
                'numeric',
                'gt:0',
            ],
        ]);

        $payment = DB::transaction(function () use ($validated, $companyId, $paymentId) {

            $payment = CustomerPayment::whereHas('customer', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
                ->whereKey($paymentId)
                ->lockForUpdate()
                ->firstOrFail();

            $this->authorizeBranch($payment->branch_id);

            if ($payment->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Only draft payments can be updated.',
                ]);
            }

            $customer = Customer::where('company_id', $companyId)
                ->whereKey($validated['customer_id'])
                ->firstOrFail();

            $branch = Branch::where('company_id', $companyId)
                ->whereKey($validated['branch_id'])
                ->firstOrFail();

            $this->authorizeBranch($branch->id);

            $currency = strtoupper($validated['currency']);

            $account = null;

            if (!empty($validated['cash_bank_account_id'])) {
                $account = CashBankAccount::where('company_id', $companyId)
                    ->where('branch_id', $branch->id)
                    ->where('currency', $currency)
                    ->where('status', true)
                    ->whereKey($validated['cash_bank_account_id'])
                    ->firstOrFail();
            }

            if (!empty($validated['payment_method_id'])) {
                PaymentMethod::where('company_id', $companyId)
                    ->where('status', true)
                    ->whereKey($validated['payment_method_id'])
                    ->firstOrFail();
            }

            $allocations = collect($validated['allocations']);

            $allocatedTotal = round(
                (float) $allocations->sum('allocated_amount'),
                2
            );

            if ($allocatedTotal !== round((float) $validated['amount'], 2)) {
                throw ValidationException::withMessages([
                    'allocations' => 'Total allocated amount must equal payment amount.',
                ]);
            }

            $invoiceIds = $allocations->pluck('sales_invoice_id')->unique()->values();

            $invoices = SalesInvoice::whereIn('id', $invoiceIds)
                ->where('customer_id', $customer->id)
                ->where('branch_id', $branch->id)
                ->where('currency', $currency)
                ->whereIn('status', ['approved', 'partially_paid'])
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($invoices->count() !== $invoiceIds->count()) {
                throw ValidationException::withMessages([
                    'allocations' => 'One or more invoices are invalid or do not belong to this customer, branch and currency.',
                ]);
            }

            foreach ($allocations as $allocation) {
                $invoice = $invoices->get($allocation['sales_invoice_id']);

                $otherAllocated = CustomerPaymentAllocation::where(
                    'sales_invoice_id',
                    $invoice->id
                )
                    ->where('customer_payment_id', '!=', $payment->id)
                    ->whereHas('customerPayment', function ($query) {
                        $query->whereIn('status', ['draft', 'posted']);
                    })
                    ->sum('allocated_amount');

                $availableBalance = round(
                    (float) $invoice->total_amount - (float) $otherAllocated,
                    2
                );

                if (
                    round((float) $allocation['allocated_amount'], 2) >
                    $availableBalance
                ) {
                    throw ValidationException::withMessages([
                        'allocations' => "Allocated amount exceeds available balance of invoice {$invoice->invoice_number}.",
                    ]);
                }
            }

            $oldPaymentValues = $payment->getOriginal();

            $oldAllocations = $payment->allocations()->get();

            foreach ($oldAllocations as $oldAllocation) {
                $this->auditService->log(
                    $oldAllocation,
                    'deleted',
                    $oldAllocation->getAttributes(),
                    null,
                    'Customer payment allocation removed during payment update.',
                    [],
                    null,
                    $companyId,
                    $payment->branch_id
                );
            }

            $oldAllocationIds = $oldAllocations->pluck('id');

            $payment->allocations()->delete();

            $payment->update([
                'customer_id' => $customer->id,
                'branch_id' => $branch->id,
                'cash_bank_account_id' => $account?->id,
                'payment_method_id' => $validated['payment_method_id'] ?? null,
                'receipt_number' => $validated['receipt_number'],
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'currency' => $currency,
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($allocations as $allocation) {
                $newAllocation = CustomerPaymentAllocation::create([
                    'customer_payment_id' => $payment->id,
                    'sales_invoice_id' => $allocation['sales_invoice_id'],
                    'allocated_amount' => $allocation['allocated_amount'],
                ]);

                $this->auditService->log(
                    $newAllocation,
                    'created',
                    null,
                    $newAllocation->getAttributes(),
                    'Customer payment allocation created during payment update.',
                    [],
                    null,
                    $companyId,
                    $branch->id
                );
            }

            $this->auditService->log(
                $payment,
                'updated',
                $oldPaymentValues,
                $payment->getAttributes(),
                'Customer payment updated.',
                [
                    'removed_allocation_ids' => $oldAllocationIds->values()->all(),
                ],
                null,
                $companyId,
                $branch->id
            );

            return $payment;
        });

        return response()->json([
            'message' => 'Customer payment updated successfully.',
            'data' => $payment->load([
                'customer',
                'branch',
                'cashBankAccount',
                'paymentMethod',
                'creator',
                'allocations.salesInvoice',
            ]),
        ]);
    }

    public function post(string $paymentId)
    {
        $companyId = $this->companyId();

        $payment = DB::transaction(function () use ($paymentId, $companyId) {

            $payment = CustomerPayment::whereHas('customer', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
                ->whereKey($paymentId)
                ->lockForUpdate()
                ->firstOrFail();

            $this->authorizeBranch($payment->branch_id);

            if ($payment->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Only draft payments can be posted.',
                ]);
            }

            if (!$payment->cash_bank_account_id) {
                throw ValidationException::withMessages([
                    'cash_bank_account_id' => 'A cash or bank account is required before posting.',
                ]);
            }

            if ($payment->cashBankTransaction()->exists()) {
                throw ValidationException::withMessages([
                    'payment' => 'A cash/bank transaction already exists for this payment.',
                ]);
            }

            $account = CashBankAccount::where('company_id', $companyId)
                ->where('branch_id', $payment->branch_id)
                ->where('currency', $payment->currency)
                ->where('status', true)
                ->whereKey($payment->cash_bank_account_id)
                ->lockForUpdate()
                ->firstOrFail();

            $allocations = $payment->allocations()
                ->lockForUpdate()
                ->get();

            if ($allocations->isEmpty()) {
                throw ValidationException::withMessages([
                    'allocations' => 'Payment must have at least one invoice allocation.',
                ]);
            }

            $allocatedTotal = round(
                (float) $allocations->sum('allocated_amount'),
                2
            );

            if ($allocatedTotal !== round((float) $payment->amount, 2)) {
                throw ValidationException::withMessages([
                    'allocations' => 'Total allocated amount must equal payment amount.',
                ]);
            }

            $invoiceIds = $allocations
                ->pluck('sales_invoice_id')
                ->unique()
                ->values();

            $invoices = SalesInvoice::whereIn('id', $invoiceIds)
                ->where('customer_id', $payment->customer_id)
                ->where('branch_id', $payment->branch_id)
                ->where('currency', $payment->currency)
                ->whereIn('status', ['approved', 'partially_paid'])
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($invoices->count() !== $invoiceIds->count()) {
                throw ValidationException::withMessages([
                    'allocations' => 'One or more invoices are no longer eligible for payment.',
                ]);
            }

            foreach ($allocations as $allocation) {
                $invoice = $invoices->get($allocation->sales_invoice_id);

                if (
                    round((float) $allocation->allocated_amount, 2) >
                    round((float) $invoice->balance_amount, 2)
                ) {
                    throw ValidationException::withMessages([
                        'allocations' => "Payment allocation exceeds the current balance of invoice {$invoice->invoice_number}.",
                    ]);
                }
            }

            // Update cash/bank account balance.
            $oldAccountValues = $account->getOriginal();

            $newAccountBalance = round(
                (float) $account->current_balance + (float) $payment->amount,
                2
            );

            $account->update([
                'current_balance' => $newAccountBalance,
            ]);

            $this->auditService->log(
                $account,
                'updated',
                $oldAccountValues,
                $account->getAttributes(),
                'Cash/bank account balance updated through customer payment posting.',
                [
                    'customer_payment_id' => $payment->id,
                ],
                null,
                $companyId,
                $payment->branch_id
            );

            // Create incoming cash/bank transaction.
            $transaction = $account->transactions()->create([
                'branch_id' => $payment->branch_id,
                'transaction_date' => $payment->payment_date,
                'transaction_type' => 'customer_receipt',
                'direction' => 'in',
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'reference_type' => $payment->getMorphClass(),
                'reference_id' => $payment->id,
                'reference_number' => $payment->receipt_number,
                'description' => 'Customer payment received.',
                'balance_after' => $newAccountBalance,
                'created_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            $this->auditService->log(
                $transaction,
                'created',
                null,
                $transaction->getAttributes(),
                'Customer receipt transaction created.',
                [],
                null,
                $companyId,
                $payment->branch_id
            );

            // Update invoice balances and statuses.
            foreach ($allocations as $allocation) {
                $invoice = $invoices->get($allocation->sales_invoice_id);

                $oldInvoiceValues = $invoice->getOriginal();
                $oldStatus = $invoice->status;

                $newPaidAmount = round(
                    (float) $invoice->paid_amount + (float) $allocation->allocated_amount,
                    2
                );

                $newBalanceAmount = round(
                    (float) $invoice->balance_amount - (float) $allocation->allocated_amount,
                    2
                );

                $newBalanceAmount = max(0, $newBalanceAmount);

                $newStatus = $newBalanceAmount <= 0
                    ? 'paid'
                    : 'partially_paid';

                $invoice->update([
                    'paid_amount' => $newPaidAmount,
                    'balance_amount' => $newBalanceAmount,
                    'status' => $newStatus,
                ]);

                if ($oldStatus !== $newStatus) {
                    $statusHistory = $invoice->statusHistories()->create([
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'changed_by' => auth()->id(),
                        'notes' => 'Status updated through customer payment posting.',
                    ]);

                    $this->auditService->log(
                        $statusHistory,
                        'created',
                        null,
                        $statusHistory->getAttributes(),
                        'Sales invoice status changed through customer payment.',
                        [],
                        null,
                        $companyId,
                        $payment->branch_id
                    );
                }

                $this->auditService->log(
                    $invoice,
                    'updated',
                    $oldInvoiceValues,
                    $invoice->getAttributes(),
                    'Sales invoice balance updated through customer payment.',
                    [
                        'customer_payment_id' => $payment->id,
                        'allocated_amount' => $allocation->allocated_amount,
                    ],
                    null,
                    $companyId,
                    $payment->branch_id
                );
            }

            // Mark payment as posted.
            $oldPaymentValues = $payment->getOriginal();

            $payment->update([
                'status' => 'posted',
                'posted_at' => now(),
            ]);

            $this->auditService->log(
                $payment,
                'posted',
                $oldPaymentValues,
                $payment->getAttributes(),
                'Customer payment posted successfully.',
                [
                    'cash_bank_transaction_id' => $transaction->id,
                ],
                null,
                $companyId,
                $payment->branch_id
            );

            return $payment;
        });

        return response()->json([
            'message' => 'Customer payment posted successfully.',
            'data' => $payment->load([
                'customer',
                'branch',
                'cashBankAccount',
                'paymentMethod',
                'creator',
                'allocations.salesInvoice',
                'cashBankTransaction',
            ]),
        ]);
    }

    public function cancel(string $paymentId)
    {
        $companyId = $this->companyId();

        $payment = DB::transaction(function () use ($paymentId, $companyId) {

            $payment = CustomerPayment::whereHas('customer', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
                ->whereKey($paymentId)
                ->lockForUpdate()
                ->firstOrFail();

            $this->authorizeBranch($payment->branch_id);

            if ($payment->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'status' => 'Payment is already cancelled.',
                ]);
            }

            if (!in_array($payment->status, ['draft', 'posted'])) {
                throw ValidationException::withMessages([
                    'status' => 'This payment cannot be cancelled.',
                ]);
            }

            $oldPaymentValues = $payment->getOriginal();

            // Draft payment: no financial reversal required.
            if ($payment->status === 'draft') {

                $payment->update([
                    'status' => 'cancelled',
                ]);

                $this->auditService->log(
                    $payment,
                    'cancelled',
                    $oldPaymentValues,
                    $payment->getAttributes(),
                    'Draft customer payment cancelled.',
                    [],
                    null,
                    $companyId,
                    $payment->branch_id
                );

                return $payment;
            }

            // Posted payment: reverse cash/bank transaction.
            $account = CashBankAccount::where('company_id', $companyId)
                ->where('branch_id', $payment->branch_id)
                ->whereKey($payment->cash_bank_account_id)
                ->lockForUpdate()
                ->firstOrFail();

            $originalTransaction = $payment->cashBankTransaction()->first();

            if (!$originalTransaction) {
                throw ValidationException::withMessages([
                    'payment' => 'Original cash/bank transaction not found. Payment cannot be reversed.',
                ]);
            }

            $allocations = $payment->allocations()
                ->lockForUpdate()
                ->get();

            $invoiceIds = $allocations
                ->pluck('sales_invoice_id')
                ->unique()
                ->values();

            $invoices = SalesInvoice::whereIn('id', $invoiceIds)
                ->where('customer_id', $payment->customer_id)
                ->where('branch_id', $payment->branch_id)
                ->where('currency', $payment->currency)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($invoices->count() !== $invoiceIds->count()) {
                throw ValidationException::withMessages([
                    'allocations' => 'One or more allocated invoices could not be found.',
                ]);
            }

            // Reverse invoice balances.
            foreach ($allocations as $allocation) {
                $invoice = $invoices->get($allocation->sales_invoice_id);

                $oldInvoiceValues = $invoice->getOriginal();
                $oldStatus = $invoice->status;

                $newPaidAmount = round(
                    (float) $invoice->paid_amount - (float) $allocation->allocated_amount,
                    2
                );

                if ($newPaidAmount < 0) {
                    throw ValidationException::withMessages([
                        'allocations' => "Cannot reverse payment from invoice {$invoice->invoice_number}. Its paid amount is insufficient.",
                    ]);
                }

                $newBalanceAmount = round(
                    (float) $invoice->total_amount - $newPaidAmount,
                    2
                );

                $newStatus = $newPaidAmount <= 0
                    ? 'approved'
                    : 'partially_paid';

                $invoice->update([
                    'paid_amount' => $newPaidAmount,
                    'balance_amount' => $newBalanceAmount,
                    'status' => $newStatus,
                ]);

                if ($oldStatus !== $newStatus) {
                    $statusHistory = $invoice->statusHistories()->create([
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'changed_by' => auth()->id(),
                        'notes' => 'Invoice status restored through customer payment cancellation.',
                    ]);

                    $this->auditService->log(
                        $statusHistory,
                        'created',
                        null,
                        $statusHistory->getAttributes(),
                        'Sales invoice status changed through customer payment cancellation.',
                        [],
                        null,
                        $companyId,
                        $payment->branch_id
                    );
                }

                $this->auditService->log(
                    $invoice,
                    'updated',
                    $oldInvoiceValues,
                    $invoice->getAttributes(),
                    'Sales invoice balance reversed through customer payment cancellation.',
                    [
                        'customer_payment_id' => $payment->id,
                        'reversed_amount' => $allocation->allocated_amount,
                    ],
                    null,
                    $companyId,
                    $payment->branch_id
                );
            }

            // Reverse cash/bank account balance.
            $oldAccountValues = $account->getOriginal();

            $newAccountBalance = round(
                (float) $account->current_balance - (float) $payment->amount,
                2
            );

            $account->update([
                'current_balance' => $newAccountBalance,
            ]);

            $this->auditService->log(
                $account,
                'updated',
                $oldAccountValues,
                $account->getAttributes(),
                'Cash/bank account balance reversed through customer payment cancellation.',
                [
                    'customer_payment_id' => $payment->id,
                ],
                null,
                $companyId,
                $payment->branch_id
            );

            // Create a separate reversal transaction.
            $reversalTransaction = $account->transactions()->create([
                'branch_id' => $payment->branch_id,
                'transaction_date' => now()->toDateString(),
                'transaction_type' => 'customer_receipt',
                'direction' => 'out',
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'reference_type' => $payment->getMorphClass(),
                'reference_id' => $payment->id,
                'reference_number' => 'REV-' . $payment->receipt_number,
                'description' => 'Reversal of customer receipt ' . $payment->receipt_number,
                'balance_after' => $newAccountBalance,
                'created_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            $this->auditService->log(
                $reversalTransaction,
                'created',
                null,
                $reversalTransaction->getAttributes(),
                'Customer payment reversal transaction created.',
                [
                    'original_transaction_id' => $originalTransaction->id,
                ],
                null,
                $companyId,
                $payment->branch_id
            );

            // Mark payment as cancelled.
            $payment->update([
                'status' => 'cancelled',
            ]);

            $this->auditService->log(
                $payment,
                'cancelled',
                $oldPaymentValues,
                $payment->getAttributes(),
                'Posted customer payment cancelled and reversed.',
                [
                    'original_transaction_id' => $originalTransaction->id,
                    'reversal_transaction_id' => $reversalTransaction->id,
                ],
                null,
                $companyId,
                $payment->branch_id
            );

            return $payment;
        });

        return response()->json([
            'message' => 'Customer payment cancelled successfully.',
            'data' => $payment->load([
                'customer',
                'branch',
                'cashBankAccount',
                'paymentMethod',
                'creator',
                'allocations.salesInvoice',
            ]),
        ]);
    }
}
