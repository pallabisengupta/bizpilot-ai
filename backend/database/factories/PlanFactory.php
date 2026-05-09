<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'price_amount' => fake()->numberBetween(0, 99900),
            'currency' => 'USD',
            'billing_interval' => 'monthly',
            'features' => [],
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}
