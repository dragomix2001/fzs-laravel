<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => $this->faker->randomElement(['create', 'update', 'delete']),
            'table_name' => $this->faker->randomElement(['users', 'kandidati', 'bodovanje', 'diplome']),
            'record_id' => $this->faker->numberBetween(1, 1000),
            'old_values' => [
                'name' => $this->faker->name(),
                'email' => $this->faker->email(),
            ],
            'new_values' => [
                'name' => $this->faker->name(),
                'email' => $this->faker->email(),
                'status' => $this->faker->word(),
            ],
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
            'updated_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
        ];
    }

    /**
     * Indicate that the audit log is for a specific table.
     */
    public function forTable(string $tableName): self
    {
        return $this->state(fn (array $attributes) => [
            'table_name' => $tableName,
        ]);
    }

    /**
     * Indicate that the audit log belongs to a specific user.
     */
    public function forUser(User $user): self
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the audit log has no user (deleted user).
     */
    public function withoutUser(): self
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }

    /**
     * Indicate that the audit log is a create action.
     */
    public function asCreate(): self
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'create',
            'old_values' => null,
        ]);
    }

    /**
     * Indicate that the audit log is an update action.
     */
    public function asUpdate(): self
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'update',
        ]);
    }

    /**
     * Indicate that the audit log is a delete action.
     */
    public function asDelete(): self
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'delete',
            'new_values' => null,
        ]);
    }
}
