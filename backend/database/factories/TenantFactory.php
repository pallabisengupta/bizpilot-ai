<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    public function definition(): array
    {
        $companyName = fake()->unique()->company();

        return [
            'company_name' => $companyName,
            'slug' => Str::slug($companyName),
            'industry' => fake()->randomElement(['retail', 'education', 'healthcare', 'services']),
            'logo' => null,
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'website' => fake()->url(),
            'timezone' => 'UTC',
            'language' => 'en',
            'plan_id' => Plan::factory(),
            'status' => 'active',
            'onboarding' => [
                'completed' => true,
                'current_step' => 'complete',
            ],
        ];
    }

    public function onboarding(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'onboarding',
            'onboarding' => [
                'completed' => false,
                'current_step' => 'company_profile',
            ],
        ]);
    }
}
