<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'assigned_user_id' => User::factory(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'company_name' => fake()->company(),
            'source' => fake()->randomElement(['manual', 'whatsapp', 'facebook', 'instagram']),
            'status' => Lead::STATUS_NEW,
            'priority' => 'normal',
            'score' => fake()->numberBetween(10, 90),
            'notes' => fake()->sentence(),
            'custom_fields' => [],
        ];
    }
}
