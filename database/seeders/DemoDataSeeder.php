<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\Invoice;
use App\Models\Payment;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // create one user
       $user = User::factory()->create([
    'name' => 'Demo User',
    'email' => 'demo@example.com',
    'password' => bcrypt('12345678'),
]);

        // create students for this user
        $students = Student::factory()
            ->count(5)
            ->create([
                'user_id' => $user->id,
            ]);

        foreach ($students as $student) {

            // create invoices for each student
            $invoices = Invoice::factory()
                ->count(2)
                ->create([
                    'user_id' => $user->id,
                    'student_id' => $student->id,
                ]);

            foreach ($invoices as $invoice) {

    $payments = Payment::factory()
        ->count(2)
        ->create([
            'user_id' => $user->id,
            'student_id' => $student->id,
            'invoice_id' => $invoice->id,
        ]);

    $paidAmount = $payments->sum('amount');
    $dueAmount = max(0, $invoice->total_amount - $paidAmount);

    $invoice->update([
        'paid_amount' => $paidAmount,
        'due_amount' => $dueAmount,
        'status' => $paidAmount <= 0
            ? 'unpaid'
            : ($dueAmount <= 0 ? 'paid' : 'partial'),
    ]);
}
        }
    }
}