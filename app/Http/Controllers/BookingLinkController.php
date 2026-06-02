<?php

namespace App\Http\Controllers;

use App\Models\BookingLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingLinkController extends Controller
{
    public function manage(): Response
    {
        return Inertia::render('booking-links', [
            'bookingLinks' => BookingLink::query()
                ->latest()
                ->get(),
        ]);
    }

    public function index(): JsonResponse
    {
        return response()->json(
            BookingLink::query()
                ->latest()
                ->get(),
        );
    }

    public function store(Request $request): JsonResponse
    {
        $bookingLink = BookingLink::create($this->validateBookingLink($request));

        return response()->json($bookingLink->refresh(), 201);
    }

    public function storeFromPage(Request $request): RedirectResponse
    {
        BookingLink::create($this->validateBookingLink($request));

        return to_route('booking-links.manage');
    }

    public function show(BookingLink $bookingLink): JsonResponse
    {
        return response()->json($bookingLink);
    }

    public function update(Request $request, BookingLink $bookingLink): JsonResponse
    {
        $bookingLink->update($this->validateBookingLink($request, updating: true));

        return response()->json($bookingLink->refresh());
    }

    public function updateFromPage(Request $request, BookingLink $bookingLink): RedirectResponse
    {
        $bookingLink->update($this->validateBookingLink($request, updating: true));

        return to_route('booking-links.manage');
    }

    public function destroy(BookingLink $bookingLink): JsonResponse
    {
        $bookingLink->delete();

        return response()->json(null, 204);
    }

    public function destroyFromPage(BookingLink $bookingLink): RedirectResponse
    {
        $bookingLink->delete();

        return to_route('booking-links.manage');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateBookingLink(Request $request, bool $updating = false): array
    {
        return $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'url' => [$updating ? 'sometimes' : 'required', 'url', 'max:2048'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
