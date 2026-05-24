<?php

use App\Models\BusinessService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('owner can view the business services management page', function () {
    $user = User::factory()->create();
    $service = BusinessService::factory()->for($user, 'business')->create([
        'topic' => 'Delivery Time',
    ]);

    $this
        ->actingAs($user)
        ->get(route('business-services.manage'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('business-services')
            ->where('services.0.id', $service->id)
            ->where('services.0.topic', 'Delivery Time'),
        );
});

test('owner can create business services from the management page', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->post(route('business-services.manage.store'), [
            'topic' => 'Delivery Time',
            'description' => 'Orders arrive in 2-3 business days.',
            'keywords' => 'delivery, shipping, delivery, arrival',
            'is_active' => '1',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('business-services.manage'));

    $this->assertDatabaseHas('business_services', [
        'business_id' => $user->id,
        'topic' => 'Delivery Time',
        'description' => 'Orders arrive in 2-3 business days.',
        'is_active' => true,
    ]);

    expect(BusinessService::first()->keywords)->toBe([
        'delivery',
        'shipping',
        'arrival',
    ]);
});

test('owner can update and delete their business service from the management page', function () {
    $user = User::factory()->create();
    $service = BusinessService::factory()->for($user, 'business')->create();

    $this
        ->actingAs($user)
        ->put(route('business-services.manage.update', $service), [
            'topic' => 'Updated Delivery',
            'description' => 'Updated answer.',
            'keywords' => 'updated, delivery',
            'is_active' => '0',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('business-services.manage'));

    expect($service->refresh())
        ->topic->toBe('Updated Delivery')
        ->description->toBe('Updated answer.')
        ->keywords->toBe(['updated', 'delivery'])
        ->is_active->toBeFalse();

    $this
        ->actingAs($user)
        ->delete(route('business-services.manage.destroy', $service))
        ->assertRedirect(route('business-services.manage'));

    $this->assertDatabaseMissing('business_services', [
        'id' => $service->id,
    ]);
});

test('owner cannot update another users business service from the management page', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $service = BusinessService::factory()->for($owner, 'business')->create();

    $this
        ->actingAs($otherUser)
        ->put(route('business-services.manage.update', $service), [
            'topic' => 'Not allowed',
            'description' => 'Nope.',
        ])
        ->assertForbidden();
});
