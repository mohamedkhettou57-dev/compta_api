<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_invoices_list(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/invoices');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_create_invoice_for_own_student(): void
{
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $student = \App\Models\Student::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->postJson('/api/invoices', [
        'student_id' => $student->id,
        'invoice_date' => now()->toDateString(),
        'total_amount' => 5000,
        'note' => 'Test invoice',
    ]);

    $response->assertStatus(201)
             ->assertJsonFragment([
                 'student_id' => $student->id,
                 'total_amount' => 5000,
                 'note' => 'Test invoice',
             ]);

    $this->assertDatabaseHas('invoices', [
        'user_id' => $user->id,
        'student_id' => $student->id,
        'total_amount' => 5000,
        'paid_amount' => 0,
        'due_amount' => 5000,
        'status' => 'unpaid',
        'note' => 'Test invoice',
    ]);
}

public function test_user_cannot_create_invoice_for_another_users_student(): void
{
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $student = \App\Models\Student::factory()->create([
        'user_id' => $user2->id,
    ]);

    Sanctum::actingAs($user1);

    $response = $this->postJson('/api/invoices', [
        'student_id' => $student->id,
        'invoice_date' => now()->toDateString(),
        'total_amount' => 5000,
        'note' => 'Forbidden invoice',
    ]);

    $response->assertStatus(404);
}

public function test_user_cannot_view_another_users_invoice(): void
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
        'reference' => 'INV-TEST-001',
    ]);

    Sanctum::actingAs($user1);

    $response = $this->getJson('/api/invoices/' . $invoice->id);

    $response->assertStatus(403);
}


public function test_user_can_update_own_invoice(): void
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
        'note' => 'Old note',
        'reference' => 'INV-TEST-002',
    ]);

    Sanctum::actingAs($user);

    $response = $this->patchJson('/api/invoices/' . $invoice->id, [
        'note' => 'Updated note',
        'total_amount' => 6000,
    ]);

    $response->assertStatus(200)
             ->assertJsonFragment([
                 'note' => 'Updated note',
                 'total_amount' => 6000,
             ]);

    $this->assertDatabaseHas('invoices', [
        'id' => $invoice->id,
        'note' => 'Updated note',
        'total_amount' => 6000,
    ]);
}

public function test_user_can_delete_own_invoice(): void
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
        'note' => 'Delete me',
        'reference' => 'INV-TEST-003',
    ]);

    Sanctum::actingAs($user);

    $response = $this->deleteJson('/api/invoices/' . $invoice->id);

    $response->assertStatus(200);

    $this->assertDatabaseMissing('invoices', [
        'id' => $invoice->id,
    ]);
}



}