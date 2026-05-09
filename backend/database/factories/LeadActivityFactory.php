<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\LeadActivity>
 */
class LeadActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'lead_id' => Lead::factory(),
            'user_id' => User::factory(),
            'activity_type' => 'note',
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'occurred_at' => now(),
            'metadata' => [],
        ];
    }
}
