<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $total = fake()->numberBetween(1000, 10000);

        return [
            'user_id' => User::factory(),
            'student_id' => Student::factory(),
            'invoice_date' => fake()->date(),
            'total_amount' => $total,
            'paid_amount' => 0,
            'due_amount' => $total,
            'status' => 'unpaid',
            'note' => fake()->sentence(),
            'reference' => 'INV-' . fake()->unique()->numberBetween(1000, 9999),
        ];
    }
}