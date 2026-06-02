<?php

use App\Models\KnowledgeBase;
use App\Models\KnowledgeTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('owner can view the knowledge base management page', function () {
    $user = User::factory()->create();
    $topic = KnowledgeTopic::factory()->for($user, 'business')->create([
        'topic' => 'Shipping',
    ]);
    $subtopic = KnowledgeBase::factory()->for($topic)->create([
        'sub_topic' => 'Express delivery',
    ]);

    $this
        ->actingAs($user)
        ->get(route('business-services.manage'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('business-services')
            ->where('topics.0.id', $topic->id)
            ->where('topics.0.topic', 'Shipping')
            ->where('topics.0.knowledge_bases.0.id', $subtopic->id)
            ->where('topics.0.knowledge_bases.0.sub_topic', 'Express delivery'),
        );
});

test('owner can create topics from the management page', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->post(route('business-services.manage.topics.store'), [
            'topic' => 'Shipping',
            'keywords' => 'delivery, freight, delivery',
            'is_active' => '1',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('business-services.manage'));

    $this->assertDatabaseHas('knowledge_topics', [
        'business_id' => $user->id,
        'topic' => 'Shipping',
        'is_active' => true,
    ]);

    expect(KnowledgeTopic::first()->keywords)->toBe([
        'delivery',
        'freight',
    ]);
});

test('owner can create subtopics from the management page', function () {
    $user = User::factory()->create();
    $topic = KnowledgeTopic::factory()->for($user, 'business')->create();

    $this
        ->actingAs($user)
        ->post(route('business-services.manage.subtopics.store'), [
            'knowledge_topic_id' => $topic->id,
            'sub_topic' => 'Express delivery',
            'description' => 'Next-day shipping in major cities.',
            'keywords' => 'express, next-day',
            'is_active' => '1',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('business-services.manage'));

    $this->assertDatabaseHas('knowledge_bases', [
        'knowledge_topic_id' => $topic->id,
        'sub_topic' => 'Express delivery',
        'description' => 'Next-day shipping in major cities.',
        'is_active' => true,
    ]);
});

test('owner can update and delete topics and subtopics from the management page', function () {
    $user = User::factory()->create();
    $topic = KnowledgeTopic::factory()->for($user, 'business')->create();
    $subtopic = KnowledgeBase::factory()->for($topic)->create();

    $this
        ->actingAs($user)
        ->put(route('business-services.manage.topics.update', $topic), [
            'topic' => 'Updated Shipping',
            'keywords' => 'shipping',
            'is_active' => '0',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('business-services.manage'));

    expect($topic->refresh())
        ->topic->toBe('Updated Shipping')
        ->keywords->toBe(['shipping'])
        ->is_active->toBeFalse();

    $this
        ->actingAs($user)
        ->put(route('business-services.manage.subtopics.update', $subtopic), [
            'sub_topic' => 'Updated express',
            'description' => 'Updated answer.',
            'keywords' => 'updated',
            'is_active' => '1',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('business-services.manage'));

    expect($subtopic->refresh())
        ->sub_topic->toBe('Updated express')
        ->description->toBe('Updated answer.')
        ->keywords->toBe(['updated'])
        ->is_active->toBeTrue();

    $this
        ->actingAs($user)
        ->delete(route('business-services.manage.subtopics.destroy', $subtopic))
        ->assertRedirect(route('business-services.manage'));

    $this->assertDatabaseMissing('knowledge_bases', ['id' => $subtopic->id]);

    $this
        ->actingAs($user)
        ->delete(route('business-services.manage.topics.destroy', $topic))
        ->assertRedirect(route('business-services.manage'));

    $this->assertDatabaseMissing('knowledge_topics', ['id' => $topic->id]);
});

test('owner cannot update another users topic from the management page', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $topic = KnowledgeTopic::factory()->for($owner, 'business')->create();

    $this
        ->actingAs($otherUser)
        ->put(route('business-services.manage.topics.update', $topic), [
            'topic' => 'Not allowed',
        ])
        ->assertForbidden();
});
