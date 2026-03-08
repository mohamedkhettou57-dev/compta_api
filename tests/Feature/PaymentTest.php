<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_payments_list(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/payments');

        $response->assertStatus(200);
    }

    public function test_user_can_create_payment_and_invoice_is_recalculated(): void
{
    $user = User::factory()->create();

    $student = \App\Models\Student::factory()->create([
        'user_id' => $user->id,
    ]);

    $invoice = \App\Models\Invoice::create([
        'user_id' => $user->id,
        'student_id' => $student->id,
        'invoice_date' => now()->toDateString(),
        'total_amount' => 5000,
        'paid_amount' => 0,
        'due_amount' => 5000,
        'status' => 'unpaid',
        'note' => 'Payment test invoice',
        'reference' => 'INV-PAY-001',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/payments', [
        'invoice_id' => $invoice->id,
        'payment_date' => now()->toDateString(),
        'amount' => 1000,
        'method' => 'cash',
        'note' => 'First payment',
    ]);

    $response->assertStatus(201)
             ->assertJsonFragment([
                 'invoice_id' => $invoice->id,
                 'student_id' => $student->id,
                 'amount' => 1000,
                 'method' => 'cash',
                 'note' => 'First payment',
             ]);

    $this->assertDatabaseHas('payments', [
        'user_id' => $user->id,
        'student_id' => $student->id,
        'invoice_id' => $invoice->id,
        'amount' => 1000,
        'method' => 'cash',
    ]);

    $this->assertDatabaseHas('invoices', [
        'id' => $invoice->id,
        'paid_amount' => 1000,
        'due_amount' => 4000,
        'status' => 'partial',
    ]);
}


public function test_user_cannot_create_payment_exceeding_invoice_total(): void
{
    $user = User::factory()->create();

    $student = \App\Models\Student::factory()->create([
        'user_id' => $user->id,
    ]);

    $invoice = \App\Models\Invoice::create([
        'user_id' => $user->id,
        'student_id' => $student->id,
        'invoice_date' => now()->toDateString(),
        'total_amount' => 5000,
        'paid_amount' => 0,
        'due_amount' => 5000,
        'status' => 'unpaid',
        'note' => 'Overpayment test',
        'reference' => 'INV-PAY-002',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/payments', [
        'invoice_id' => $invoice->id,
        'payment_date' => now()->toDateString(),
        'amount' => 6000,
        'method' => 'cash',
        'note' => 'Too much payment',
    ]);

    $response->assertStatus(422);

    $this->assertDatabaseMissing('payments', [
        'invoice_id' => $invoice->id,
        'amount' => 6000,
    ]);

    $this->assertDatabaseHas('invoices', [
        'id' => $invoice->id,
        'paid_amount' => 0,
        'due_amount' => 5000,
        'status' => 'unpaid',
    ]);
}

public function test_user_cannot_view_another_users_payment(): void
{
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $student = \App\Models\Student::factory()->create([
        'user_id' => $user2->id,
    ]);

    $invoice = \App\Models\Invoice::create([
        'user_id' => $user2->id,
        'student_id' => $student->id,
        'invoice_date' => now()->toDateString(),
        'total_amount' => 5000,
        'paid_amount' => 0,
        'due_amount' => 5000,
        'status' => 'unpaid',
        'note' => 'Private invoice',
        'reference' => 'INV-PAY-003',
    ]);

    $payment = \App\Models\Payment::create([
        'user_id' => $user2->id,
        'student_id' => $student->id,
        'invoice_id' => $invoice->id,
        'payment_date' => now()->toDateString(),
        'amount' => 1000,
        'method' => 'cash',
        'note' => 'Private payment',
    ]);

    Sanctum::actingAs($user1);

    $response = $this->getJson('/api/payments/' . $payment->id);

    $response->assertStatus(403);
}

public function test_user_can_update_own_payment_and_invoice_is_recalculated(): void
{
    $user = User::factory()->create();

    $student = \App\Models\Student::factory()->create([
        'user_id' => $user->id,
    ]);

    $invoice = \App\Models\Invoice::create([
        'user_id' => $user->id,
        'student_id' => $student->id,
        'invoice_date' => now()->toDateString(),
        'total_amount' => 5000,
        'paid_amount' => 1000,
        'due_amount' => 4000,
        'status' => 'partial',
        'note' => 'Update payment test',
        'reference' => 'INV-PAY-004',
    ]);

    $payment = \App\Models\Payment::create([
        'user_id' => $user->id,
        'student_id' => $student->id,
        'invoice_id' => $invoice->id,
        'payment_date' => now()->toDateString(),
        'amount' => 1000,
        'method' => 'cash',
        'note' => 'Old payment',
    ]);

    Sanctum::actingAs($user);

    $response = $this->patchJson('/api/payments/' . $payment->id, [
        'amount' => 1500,
        'method' => 'card',
        'note' => 'Updated payment',
    ]);

    $response->assertStatus(200)
             ->assertJsonFragment([
                 'amount' => 1500,
                 'method' => 'card',
                 'note' => 'Updated payment',
             ]);

    $this->assertDatabaseHas('payments', [
        'id' => $payment->id,
        'amount' => 1500,
        'method' => 'card',
        'note' => 'Updated payment',
    ]);

    $this->assertDatabaseHas('invoices', [
        'id' => $invoice->id,
        'paid_amount' => 1500,
        'due_amount' => 3500,
        'status' => 'partial',
    ]);
}

public function test_user_can_delete_own_payment_and_invoice_is_recalculated(): void
{
    $user = User::factory()->create();

    $student = \App\Models\Student::factory()->create([
        'user_id' => $user->id,
    ]);

    $invoice = \App\Models\Invoice::create([
        'user_id' => $user->id,
        'student_id' => $student->id,
        'invoice_date' => now()->toDateString(),
        'total_amount' => 5000,
        'paid_amount' => 1000,
        'due_amount' => 4000,
        'status' => 'partial',
        'note' => 'Delete payment test',
        'reference' => 'INV-PAY-005',
    ]);

    $payment = \App\Models\Payment::create([
        'user_id' => $user->id,
        'student_id' => $student->id,
        'invoice_id' => $invoice->id,
        'payment_date' => now()->toDateString(),
        'amount' => 1000,
        'method' => 'cash',
        'note' => 'Delete me',
    ]);

    Sanctum::actingAs($user);

    $response = $this->deleteJson('/api/payments/' . $payment->id);

    $response->assertStatus(200);

    $this->assertDatabaseMissing('payments', [
        'id' => $payment->id,
    ]);

    $this->assertDatabaseHas('invoices', [
        'id' => $invoice->id,
        'paid_amount' => 0,
        'due_amount' => 5000,
        'status' => 'unpaid',
    ]);
}


}