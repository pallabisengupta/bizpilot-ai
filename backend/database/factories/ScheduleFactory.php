<?php

namespace Database\Factories;

use App\Models\Schedule;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Schedule>
 */
class ScheduleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'created_by_user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'content' => fake()->paragraph(),
            'media_urls' => [],
            'platform' => fake()->randomElement(['facebook', 'instagram', 'whatsapp']),
            'scheduled_at' => now()->addDay(),
            'timezone' => 'UTC',
            'status' => Schedule::STATUS_QUEUED,
            'attempts' => 0,
            'payload' => [],
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Schedule::STATUS_FAILED,
            'failure_reason' => 'Provider timeout.',
            'attempts' => 1,
        ]);
    }
}
