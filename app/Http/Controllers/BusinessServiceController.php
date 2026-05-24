<?php

namespace App\Http\Controllers;

use App\Models\BusinessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

use Illuminate\Support\Facades\Http;
class BusinessServiceController extends Controller
{
    public function manage(Request $request): Response
    {
        // Gate::authorize('viewAny', BusinessService::class);
        Log::info('BusinessServiceController@manage reached', [
            'path' => $request->path(),
            'route_name' => $request->route()?->getName(),
            'user_id' => $request->user()?->id,
        ]);

        $services = BusinessService::query()
            ->where('business_id', $request->user()->id)
            ->latest()
            ->get(['id', 'topic', 'description', 'keywords', 'is_active', 'created_at', 'updated_at']);

        return Inertia::render('business-services', [
            'services' => $services,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        // Gate::authorize('viewAny', BusinessService::class);
        Log::info('BusinessServiceController@index reached', [
            'path' => $request->path(),
            'route_name' => $request->route()?->getName(),
            'user_id' => $request->user()?->id,
        ]);

        $services = BusinessService::query()
            ->where('business_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($services);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateStoreBusinessService($request);

        Gate::authorize('create', BusinessService::class);

        $service = BusinessService::create([
            ...$validated,
            'business_id' => $request->user()->id,
        ]);

        return response()->json($service, 201);
    }

    public function storeFromPage(Request $request)
    {
        $validated = $this->validateStoreBusinessService($request);

        Gate::authorize('create', BusinessService::class);

        BusinessService::create([
            ...$validated,
            'business_id' => $request->user()->id,
            'is_active' => $request->boolean('is_active'),
        ]);

        return to_route('business-services.manage');
    }

    /**
     * Display the specified resource.
     */
    public function show(BusinessService $businessService): JsonResponse
    {
        Log::info('BusinessServiceController@show reached', [
            'business_service_id' => $businessService->id,
            'route_name' => request()->route()?->getName(),
        ]);

        Gate::authorize('view', $businessService);

        return response()->json($businessService);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BusinessService $businessService): JsonResponse
    {
        $validated = $this->validateUpdateBusinessService($request);

        Gate::authorize('update', $businessService);

        $businessService->update($validated);

        return response()->json($businessService->refresh());
    }

    public function updateFromPage(Request $request, BusinessService $businessService)
    {
        $validated = $this->validateUpdateBusinessService($request);

        Gate::authorize('update', $businessService);

        $businessService->update([
            ...$validated,
            'is_active' => $request->boolean('is_active'),
        ]);

        return to_route('business-services.manage');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BusinessService $businessService): JsonResponse
    {
        Gate::authorize('delete', $businessService);

        $businessService->delete();

        return response()->json(null, 204);
    }

    public function destroyFromPage(BusinessService $businessService)
    {
        Gate::authorize('delete', $businessService);

        $businessService->delete();

        return to_route('business-services.manage');
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:1000'],
        ]);

        $services = BusinessService::query()
            ->active()
            ->matchingQuestion($validated['question'])
            ->orderBy('topic')
            ->limit(10)
            ->get(['id', 'topic']);

        return response()->json($services);
    }

    public function showForAgent(string $id): JsonResponse
    {
        $service = BusinessService::query()
            ->active()
            ->findOrFail($id, ['topic', 'description']);

        return response()->json($service);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateStoreBusinessService(Request $request): array
    {
        $this->prepareKeywordsForValidation($request);

        return $request->validate([
            'topic' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'keywords' => ['nullable', 'array'],
            'keywords.*' => ['string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateUpdateBusinessService(Request $request): array
    {
        $this->prepareKeywordsForValidation($request);

        return $request->validate([
            'topic' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'keywords' => ['nullable', 'array'],
            'keywords.*' => ['string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }

    private function prepareKeywordsForValidation(Request $request): void
    {
        if (is_string($request->input('keywords'))) {
            $request->merge([
                'keywords' => $this->keywordsFromString($request->input('keywords')),
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    private function keywordsFromString(string $keywords): array
    {
        return collect(explode(',', $keywords))
            ->map(fn (string $keyword) => trim($keyword))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
