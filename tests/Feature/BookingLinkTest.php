<?php

use App\Models\BookingLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function bookingLinkApiHeaders(User $user): array
{
    return [
        'Accept' => 'application/json',
        'Authorization' => 'Basic '.base64_encode($user->email.':password'),
    ];
}

test('authenticated users can manage booking links through the api', function () {
    $user = User::factory()->create();

    $firstResponse = $this
        ->withHeaders(bookingLinkApiHeaders($user))
        ->postJson('/booking-links', [
            'name' => 'Consultation',
            'url' => 'https://cal.com/example/consultation',
            'description' => 'Use this for consultations.',
            'is_active' => true,
        ])
        ->assertCreated()
        ->assertJsonPath('name', 'Consultation')
        ->assertJsonPath('is_active', true);

    $firstId = $firstResponse->json('id');

    $secondResponse = $this
        ->withHeaders(bookingLinkApiHeaders($user))
        ->postJson('/booking-links', [
            'name' => 'Support',
            'url' => 'https://cal.com/example/support',
            'is_active' => true,
        ])
        ->assertCreated()
        ->assertJsonPath('name', 'Support')
        ->assertJsonPath('is_active', true);

    $secondId = $secondResponse->json('id');

    expect(BookingLink::find($firstId)->is_active)->toBeFalse();
    expect(BookingLink::find($secondId)->is_active)->toBeTrue();

    $this
        ->withHeaders(bookingLinkApiHeaders($user))
        ->putJson("/booking-links/{$firstId}", [
            'is_active' => true,
        ])
        ->assertOk()
        ->assertJsonPath('is_active', true);

    expect(BookingLink::find($firstId)->is_active)->toBeTrue();
    expect(BookingLink::find($secondId)->is_active)->toBeFalse();

    $this
        ->withHeaders(bookingLinkApiHeaders($user))
        ->deleteJson("/booking-links/{$secondId}")
        ->assertNoContent();

    $this->assertDatabaseMissing('booking_links', ['id' => $secondId]);
});

test('authenticated users can visit the booking links dashboard page', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->get(route('booking-links.manage'))
        ->assertOk();
});
