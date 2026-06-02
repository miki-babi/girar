<?php

namespace Database\Seeders;

use App\Models\KnowledgeBase;
use App\Models\KnowledgeTopic;
use App\Models\User;
use Illuminate\Database\Seeder;

class KnowledgeBaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $business = User::factory()->create();

        KnowledgeTopic::factory()
            ->for($business, 'business')
            ->has(
                KnowledgeBase::factory()->count(3),
                'knowledgeBases'
            )
            ->count(2)
            ->create();
    }
}
