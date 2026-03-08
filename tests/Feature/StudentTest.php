<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_students_list(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/students');

        $response->assertStatus(200);
    }


    public function test_authenticated_user_can_create_student(): void
{
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/students', [
        'name'  => 'Mohamed Test',
        'email' => 'mohamed@test.com',
        'phone' => '0555555555',
    ]);

    $response->assertStatus(201)
             ->assertJsonFragment([
                 'name'  => 'Mohamed Test',
                 'email' => 'mohamed@test.com',
                 'phone' => '0555555555',
             ]);

    $this->assertDatabaseHas('students', [
        'user_id' => $user->id,
        'name'    => 'Mohamed Test',
        'email'   => 'mohamed@test.com',
        'phone'   => '0555555555',
    ]);
}

public function test_user_cannot_view_another_users_student(): void
{
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $student = \App\Models\Student::factory()->create([
        'user_id' => $user2->id,
    ]);

    Sanctum::actingAs($user1);

    $response = $this->getJson('/api/students/' . $student->id);

    $response->assertStatus(403);
}

public function test_user_can_update_own_student(): void
{
    $user = User::factory()->create();

    $student = \App\Models\Student::factory()->create([
        'user_id' => $user->id,
        'name' => 'Old Name',
        'email' => 'old@test.com',
        'phone' => '0555000000',
    ]);

    Sanctum::actingAs($user);

    $response = $this->patchJson('/api/students/' . $student->id, [
        'name' => 'New Name',
        'email' => 'new@test.com',
        'phone' => '0666000000',
    ]);

    $response->assertStatus(200)
             ->assertJsonFragment([
                 'name' => 'New Name',
                 'email' => 'new@test.com',
                 'phone' => '0666000000',
             ]);

    $this->assertDatabaseHas('students', [
        'id' => $student->id,
        'user_id' => $user->id,
        'name' => 'New Name',
        'email' => 'new@test.com',
        'phone' => '0666000000',
    ]);
}

public function test_user_can_delete_own_student(): void
{
    $user = User::factory()->create();

    $student = \App\Models\Student::factory()->create([
        'user_id' => $user->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->deleteJson('/api/students/' . $student->id);

    $response->assertStatus(200);

    $this->assertDatabaseMissing('students', [
        'id' => $student->id,
    ]);
}



}