<?php

use App\Models\KnowledgeBase;
use App\Models\KnowledgeTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function knowledgeApiHeaders(User $user): array
{
    return [
        'Accept' => 'application/json',
        'Authorization' => 'Basic '.base64_encode($user->email.':password'),
    ];
}

test('unauthenticated users cannot access knowledge topic crud', function () {
    $this->getJson('/knowledge-topics')->assertUnauthorized();
});

test('authenticated owner can manage knowledge topics and subtopics', function () {
    $user = User::factory()->create();

    $topicResponse = $this
        ->withHeaders(knowledgeApiHeaders($user))
        ->postJson('/knowledge-topics', [
            'topic' => 'Shipping',
            'keywords' => ['delivery', 'freight'],
        ])
        ->assertCreated()
        ->assertJsonPath('topic', 'Shipping')
        ->assertJsonPath('business_id', $user->id);

    $topicId = $topicResponse->json('id');

    $entryResponse = $this
        ->withHeaders(knowledgeApiHeaders($user))
        ->postJson('/knowledge-bases', [
            'knowledge_topic_id' => $topicId,
            'sub_topic' => 'Express delivery',
            'description' => 'Next-day shipping in major cities.',
            'keywords' => ['express'],
        ])
        ->assertCreated()
        ->assertJsonPath('sub_topic', 'Express delivery');

    $entryId = $entryResponse->json('id');

    $this
        ->withHeaders(knowledgeApiHeaders($user))
        ->getJson('/knowledge-topics')
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $topicId);

    $this
        ->withHeaders(knowledgeApiHeaders($user))
        ->getJson('/knowledge-bases')
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $entryId);

    $this
        ->withHeaders(knowledgeApiHeaders($user))
        ->putJson("/knowledge-bases/{$entryId}", [
            'sub_topic' => 'Updated express',
            'is_active' => false,
        ])
        ->assertOk()
        ->assertJsonPath('sub_topic', 'Updated express')
        ->assertJsonPath('is_active', false);

    $this
        ->withHeaders(knowledgeApiHeaders($user))
        ->deleteJson("/knowledge-bases/{$entryId}")
        ->assertNoContent();

    $this->assertDatabaseMissing('knowledge_bases', ['id' => $entryId]);
});

test('owner cannot access another users knowledge subtopic', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $topic = KnowledgeTopic::factory()->for($owner, 'business')->create();
    $entry = KnowledgeBase::factory()->for($topic)->create();

    $this
        ->withHeaders(knowledgeApiHeaders($otherUser))
        ->getJson("/knowledge-bases/{$entry->id}")
        ->assertForbidden();
});

test('mcp search returns active matching subtopic summaries only', function () {
    $topic = KnowledgeTopic::factory()->create([
        'topic' => 'Shipping',
        'is_active' => true,
    ]);

    $active = KnowledgeBase::factory()->for($topic)->create([
        'sub_topic' => 'Express delivery',
        'description' => 'Orders arrive next day.',
        'keywords' => ['express', 'delivery'],
        'is_active' => true,
    ]);

    KnowledgeBase::factory()->for($topic)->create([
        'sub_topic' => 'Hidden delivery',
        'description' => 'Inactive delivery information.',
        'keywords' => ['delivery'],
        'is_active' => false,
    ]);

    $this
        ->postJson('/mcp/knowledge-bases/search', [
            'question' => 'When will my express delivery arrive?',
        ])
        ->assertOk()
        ->assertExactJson([
            [
                'id' => $active->id,
                'topic' => 'Shipping',
                'sub_topic' => 'Express delivery',
            ],
        ]);
});

test('mcp detail returns active subtopic with parent topic', function () {
    $topic = KnowledgeTopic::factory()->create([
        'topic' => 'Shipping',
    ]);

    $entry = KnowledgeBase::factory()->for($topic)->create([
        'sub_topic' => 'Express delivery',
        'description' => 'Orders arrive next day.',
    ]);

    $inactive = KnowledgeBase::factory()->create([
        'is_active' => false,
    ]);

    $this
        ->getJson("/mcp/knowledge-bases/{$entry->id}")
        ->assertOk()
        ->assertExactJson([
            'topic' => 'Shipping',
            'sub_topic' => 'Express delivery',
            'description' => 'Orders arrive next day.',
        ]);

    $this
        ->getJson("/mcp/knowledge-bases/{$inactive->id}")
        ->assertNotFound();
});
