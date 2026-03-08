<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $invoices = Invoice::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->with(['student']) // light relation for list
            ->paginate(15);

        return InvoiceResource::collection($invoices);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Invoice::class);

        $validated = $request->validate([
            'student_id'   => ['required', 'integer'],
            'invoice_date' => ['required', 'date'],
            'due_date'     => ['nullable', 'date'],
            'total_amount' => ['required', 'numeric', 'min:0.01'],
            'note'         => ['nullable', 'string'],
        ]);

        $student = Student::query()
            ->where('user_id', $request->user()->id)
            ->where('id', $validated['student_id'])
            ->firstOrFail();

        $invoice = Invoice::create([
            'user_id'      => $request->user()->id,
            'student_id'   => $student->id,
            'invoice_date' => $validated['invoice_date'],
            'due_date'     => $validated['due_date'] ?? null,
            'total_amount' => $validated['total_amount'],

            'paid_amount'  => 0,
            'due_amount'   => $validated['total_amount'],
            'status'       => 'unpaid',

            'note'      => $validated['note'] ?? null,
            'reference' => 'INV-' . time(),
        ]);

        return new InvoiceResource($invoice->load(['student']));
    }

    public function show(Request $request, Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['student', 'payments']);

        return new InvoiceResource($invoice);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        // IMPORTANT: We do NOT accept "status" from the client.
        // Status is derived from payments (unpaid/partial/paid).
        $validated = $request->validate([
            'total_amount' => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'invoice_date' => ['sometimes', 'nullable', 'date'],
            'due_date'     => ['sometimes', 'nullable', 'date'],
            'note'         => ['sometimes', 'nullable', 'string'],
        ]);

        DB::transaction(function () use ($invoice, $validated) {
            $locked = Invoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();

            $locked->update($validated);

            $paid = (float) $locked->payments()->sum('amount');
            $total = (float) $locked->total_amount;

            // Prevent impossible state: total < paid
            if ($total < $paid) {
                // You can choose: reject or auto-adjust.
                // Here we reject to keep data consistent.
                abort(422, 'total_amount cannot be less than already paid amount.');
            }

            $due = $total - $paid;

            if ($paid <= 0) {
                $status = 'unpaid';
            } elseif ($due <= 0) {
                $status = 'paid';
            } else {
                $status = 'partial';
            }

            $locked->paid_amount = $paid;
            $locked->due_amount  = $due;
            $locked->status      = $status;
            $locked->save();
        });

        $invoice->refresh()->load(['student', 'payments']);

        return new InvoiceResource($invoice);
    }

    public function destroy(Request $request, Invoice $invoice)
    {
        $this->authorize('delete', $invoice);

        $invoice->delete();

        return response()->json([
            'message' => 'Invoice deleted successfully.'
        ]);
    }
}