<?php

use App\Models\BusinessService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function basicAuthHeaders(User $user): array
{
    return [
        'Accept' => 'application/json',
        'Authorization' => 'Basic '.base64_encode($user->email.':password'),
    ];
}

test('unauthenticated users cannot access owner business service crud', function () {
    $this->getJson('/business-services')->assertUnauthorized();
});

test('authenticated owner can manage business services', function () {
    $user = User::factory()->create();

    $createResponse = $this
        ->withHeaders(basicAuthHeaders($user))
        ->postJson('/business-services', [
            'topic' => 'Delivery Time',
            'description' => 'Orders arrive in 2-3 business days.',
            'keywords' => ['delivery', 'shipping', 'arrival'],
        ])
        ->assertCreated()
        ->assertJsonPath('topic', 'Delivery Time')
        ->assertJsonPath('business_id', $user->id);

    $serviceId = $createResponse->json('id');

    $this
        ->withHeaders(basicAuthHeaders($user))
        ->getJson('/business-services')
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $serviceId);

    $this
        ->withHeaders(basicAuthHeaders($user))
        ->getJson("/business-services/{$serviceId}")
        ->assertOk()
        ->assertJsonPath('id', $serviceId);

    $this
        ->withHeaders(basicAuthHeaders($user))
        ->putJson("/business-services/{$serviceId}", [
            'topic' => 'Updated Delivery Time',
            'is_active' => false,
        ])
        ->assertOk()
        ->assertJsonPath('topic', 'Updated Delivery Time')
        ->assertJsonPath('is_active', false);

    $this
        ->withHeaders(basicAuthHeaders($user))
        ->deleteJson("/business-services/{$serviceId}")
        ->assertNoContent();

    $this->assertDatabaseMissing('business_services', [
        'id' => $serviceId,
    ]);
});

test('owner cannot access another users business service', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $service = BusinessService::factory()->for($owner, 'business')->create();

    $this
        ->withHeaders(basicAuthHeaders($otherUser))
        ->getJson("/business-services/{$service->id}")
        ->assertForbidden();
});

test('mcp search returns active matching service summaries only', function () {
    $active = BusinessService::factory()->create([
        'topic' => 'Delivery Time',
        'description' => 'Orders arrive in 2-3 business days.',
        'keywords' => ['delivery', 'shipping', 'arrival'],
    ]);

    BusinessService::factory()->create([
        'topic' => 'Hidden Delivery',
        'description' => 'Inactive delivery information.',
        'keywords' => ['delivery'],
        'is_active' => false,
    ]);

    $this
        ->postJson('/mcp/business-services/search', [
            'question' => 'When will my delivery arrive?',
        ])
        ->assertOk()
        ->assertExactJson([
            [
                'id' => $active->id,
                'topic' => 'Delivery Time',
            ],
        ]);
});

test('mcp detail returns active service topic and description only', function () {
    $service = BusinessService::factory()->create([
        'topic' => 'Delivery Time',
        'description' => 'Orders arrive in 2-3 business days.',
        'keywords' => ['delivery'],
    ]);

    $inactive = BusinessService::factory()->create([
        'is_active' => false,
    ]);

    $this
        ->getJson("/mcp/business-services/{$service->id}")
        ->assertOk()
        ->assertExactJson([
            'topic' => 'Delivery Time',
            'description' => 'Orders arrive in 2-3 business days.',
        ]);

    $this
        ->getJson("/mcp/business-services/{$inactive->id}")
        ->assertNotFound();
});
