<?php

use App\Models\KnowledgeBase;
use App\Models\KnowledgeTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('knowledge topic has many knowledge base sub topics', function () {
    $user = User::factory()->create();
    $topic = KnowledgeTopic::factory()->for($user, 'business')->create([
        'topic' => 'Shipping',
        'keywords' => ['freight', 'delivery'],
    ]);

    $subTopic = KnowledgeBase::factory()->for($topic)->create([
        'sub_topic' => 'Express delivery',
        'description' => 'Next-day shipping is available in major cities.',
        'keywords' => ['express', 'next-day'],
    ]);

    expect($topic->knowledgeBases)->toHaveCount(1);
    expect($topic->knowledgeBases->first()->is($subTopic))->toBeTrue();
    expect($subTopic->knowledgeTopic->is($topic))->toBeTrue();
    expect($subTopic->sub_topic)->toBe('Express delivery');
    expect($subTopic->keywords)->toBe(['express', 'next-day']);
    expect($topic->keywords)->toBe(['freight', 'delivery']);
});

test('deleting a knowledge topic cascades to its sub topics', function () {
    $topic = KnowledgeTopic::factory()
        ->has(KnowledgeBase::factory()->count(2), 'knowledgeBases')
        ->create();

    $subTopicIds = $topic->knowledgeBases->pluck('id');

    $topic->delete();

    foreach ($subTopicIds as $id) {
        $this->assertDatabaseMissing('knowledge_bases', ['id' => $id]);
    }
});

test('matching question scope searches topic and subtopic fields', function () {
    $topic = KnowledgeTopic::factory()->create([
        'topic' => 'Billing',
        'keywords' => ['invoice'],
        'is_active' => true,
    ]);

    $match = KnowledgeBase::factory()->for($topic)->create([
        'sub_topic' => 'Payment methods',
        'description' => 'We accept cards and bank transfer.',
        'keywords' => ['payment'],
        'is_active' => true,
    ]);

    KnowledgeBase::factory()->for($topic)->create([
        'sub_topic' => 'Unrelated',
        'description' => 'Nothing here.',
        'is_active' => true,
    ]);

    $results = KnowledgeBase::query()
        ->active()
        ->matchingQuestion('payment methods')
        ->pluck('id');

    expect($results)->toContain($match->id)->toHaveCount(1);
});
