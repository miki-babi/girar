<?php

namespace Database\Factories;

use App\Models\KnowledgeBase;
use App\Models\KnowledgeTopic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KnowledgeBase>
 */
class KnowledgeBaseFactory extends Factory
{
    protected $model = KnowledgeBase::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'knowledge_topic_id' => KnowledgeTopic::factory(),
            'sub_topic' => fake()->unique()->words(2, true),
            'description' => fake()->paragraph(),
            'keywords' => fake()->words(5),
            'is_active' => true,
        ];
    }
}
