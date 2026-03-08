<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Student;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'student_id' => Student::factory(),
            'invoice_id' => Invoice::factory(),
            'payment_date' => fake()->date(),
            'amount' => fake()->numberBetween(100, 1000),
            'method' => fake()->randomElement(['cash', 'card', 'transfer']),
            'reference' => 'PAY-' . fake()->unique()->numberBetween(1000, 9999),
            'note' => fake()->sentence(),
        ];
    }
}