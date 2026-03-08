<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Payment::class);

        $payments = Payment::with(['invoice', 'student'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->paginate(15);

        return PaymentResource::collection($payments);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Payment::class);

        $validated = $request->validate([
            'invoice_id'   => ['required', 'integer', 'exists:invoices,id'],
            'payment_date' => ['required', 'date'],
            'amount'       => ['required', 'numeric', 'gt:0'],
            'method'       => ['required', 'string', 'max:50'],
            'reference'    => ['nullable', 'string', 'max:100'],
            'note'         => ['nullable', 'string'],
        ]);

        $payment = DB::transaction(function () use ($validated, $request) {

            $invoice = Invoice::query()
                ->whereKey($validated['invoice_id'])
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->firstOrFail();

            $incoming = (float) $validated['amount'];
            $currentPaid = (float) $invoice->payments()->sum('amount');
            $total = (float) $invoice->total_amount;

            if ($currentPaid + $incoming > $total) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment exceeds invoice remaining due.',
                ]);
            }

            $payment = Payment::create([
                'user_id'      => $request->user()->id,
                'student_id'   => $invoice->student_id,
                'invoice_id'   => $invoice->id,
                'payment_date' => $validated['payment_date'],
                'amount'       => $incoming,
                'method'       => $validated['method'],
                'reference'    => $validated['reference'] ?? null,
                'note'         => $validated['note'] ?? null,
            ]);

            $newPaid = (float) $invoice->payments()->sum('amount');
            $newDue  = max($total - $newPaid, 0);

            $newStatus = match (true) {
                $newPaid <= 0 => 'unpaid',
                $newPaid >= $total => 'paid',
                default => 'partial',
            };

            $invoice->update([
                'paid_amount' => $newPaid,
                'due_amount'  => $newDue,
                'status'      => $newStatus,
            ]);

            return $payment;
        });

        return new PaymentResource($payment->load(['invoice', 'student']));
    }

    public function show(Request $request, Payment $payment)
    {
        $this->authorize('view', $payment);

        return new PaymentResource($payment->load(['invoice', 'student']));
    }

    public function update(Request $request, Payment $payment)
    {
        $this->authorize('update', $payment);

        $validated = $request->validate([
            'payment_date' => ['sometimes', 'date'],
            'amount'       => ['sometimes', 'numeric', 'gt:0'],
            'method'       => ['sometimes', 'required', 'string', 'max:50'],
            'reference'    => ['sometimes', 'nullable', 'string', 'max:100'],
            'note'         => ['sometimes', 'nullable', 'string'],
        ]);

        DB::transaction(function () use ($payment, $validated) {

            $invoice = Invoice::query()
                ->whereKey($payment->invoice_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (array_key_exists('amount', $validated)) {
                $incoming = (float) $validated['amount'];
                $total = (float) $invoice->total_amount;

                $paidWithoutThis = (float) $invoice->payments()
                    ->where('id', '!=', $payment->id)
                    ->sum('amount');

                if ($paidWithoutThis + $incoming > $total) {
                    throw ValidationException::withMessages([
                        'amount' => 'Payment exceeds invoice remaining due.',
                    ]);
                }
            }

            $payment->update($validated);

            $newPaid = (float) $invoice->payments()->sum('amount');
            $total   = (float) $invoice->total_amount;
            $newDue  = max($total - $newPaid, 0);

            $newStatus = match (true) {
                $newPaid <= 0 => 'unpaid',
                $newPaid >= $total => 'paid',
                default => 'partial',
            };

            $invoice->update([
                'paid_amount' => $newPaid,
                'due_amount'  => $newDue,
                'status'      => $newStatus,
            ]);
        });

        $payment->refresh()->load(['invoice', 'student']);

        return new PaymentResource($payment);
    }

    public function destroy(Payment $payment)
    {
        $this->authorize('delete', $payment);

        DB::transaction(function () use ($payment) {

// Lock the invoice row to prevent race conditions while recalculating totals

            $invoice = Invoice::query()
                ->whereKey($payment->invoice_id)
                ->lockForUpdate()
                ->firstOrFail();

            $payment->delete();

            $newPaid = (float) $invoice->payments()->sum('amount');
            $total   = (float) $invoice->total_amount;
            $newDue  = max($total - $newPaid, 0);

            $newStatus = match (true) {
                $newPaid <= 0 => 'unpaid',
                $newPaid >= $total => 'paid',
                default => 'partial',
            };

            $invoice->update([
                'paid_amount' => $newPaid,
                'due_amount'  => $newDue,
                'status'      => $newStatus,
            ]);
        });

        return response()->json([
            'message' => 'Payment deleted successfully.',
        ]);
    }
}