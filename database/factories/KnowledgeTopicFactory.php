<?php

namespace Database\Factories;

use App\Models\KnowledgeTopic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KnowledgeTopic>
 */
class KnowledgeTopicFactory extends Factory
{
    protected $model = KnowledgeTopic::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => User::factory(),
            'topic' => fake()->unique()->sentence(3),
            'keywords' => fake()->words(4),
            'is_active' => true,
        ];
    }
}
