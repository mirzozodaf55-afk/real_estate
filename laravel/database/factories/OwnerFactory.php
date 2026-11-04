<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Пользователь сайта (App\Models\Owner) для тестов: Owner::factory()->moderator()->create()
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Owner>
 */
class OwnerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => '+992' . fake()->unique()->numerify('#########'),
            'uniq_id' => (string) Str::uuid(),
            'role' => 'user',
        ];
    }

    // фабрика создаёт модель без $fillable — role и blocked_at задаются как обычные поля
    public function moderator(): static
    {
        return $this->state(['role' => 'moderator']);
    }

    public function admin(): static
    {
        return $this->state(['role' => 'admin']);
    }

    public function blocked(): static
    {
        return $this->state(['blocked_at' => now(), 'block_reason' => 'спам']);
    }
}
