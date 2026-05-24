<?php

namespace Database\Factories;

use App\Models\BusinessService;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessService>
 */
class BusinessServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => User::factory(),
            'topic' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'keywords' => fake()->words(4),
            'is_active' => true,
        ];
    }
}
