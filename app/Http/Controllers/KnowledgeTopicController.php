<?php

namespace App\Http\Controllers;

use App\Http\Concerns\ParsesCommaSeparatedKeywords;
use App\Models\KnowledgeTopic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class KnowledgeTopicController extends Controller
{
    use ParsesCommaSeparatedKeywords;

    public function manage(Request $request): Response
    {
        $userId = $request->user()?->id ?? \App\Models\User::first()?->id ?? 1;

        $topics = KnowledgeTopic::query()
            ->forBusiness($userId)
            ->with(['knowledgeBases' => fn ($query) => $query->orderBy('sub_topic')])
            ->latest()
            ->get();

        return Inertia::render('business-services', [
            'topics' => $topics,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', KnowledgeTopic::class);

        $topics = KnowledgeTopic::query()
            ->forBusiness($request->user()->id)
            ->with('knowledgeBases')
            ->latest()
            ->get();

        return response()->json($topics);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateTopic($request);

        Gate::authorize('create', KnowledgeTopic::class);

        $topic = KnowledgeTopic::create([
            ...$validated,
            'business_id' => $request->user()->id,
        ]);

        return response()->json($topic, 201);
    }

    public function storeFromPage(Request $request): RedirectResponse
    {
        $validated = $this->validateTopic($request);

        $userId = $request->user()?->id ?? \App\Models\User::first()?->id ?? 1;

        KnowledgeTopic::create([
            ...$validated,
            'business_id' => $userId,
            'is_active' => $request->boolean('is_active'),
        ]);

        return to_route('business-services.manage');
    }

    public function show(KnowledgeTopic $knowledgeTopic): JsonResponse
    {
        Gate::authorize('view', $knowledgeTopic);

        $knowledgeTopic->load('knowledgeBases');

        return response()->json($knowledgeTopic);
    }

    public function update(Request $request, KnowledgeTopic $knowledgeTopic): JsonResponse
    {
        $validated = $this->validateTopic($request, updating: true);

        Gate::authorize('update', $knowledgeTopic);

        $knowledgeTopic->update($validated);

        return response()->json($knowledgeTopic->refresh());
    }

    public function updateFromPage(Request $request, KnowledgeTopic $knowledgeTopic): RedirectResponse
    {
        $validated = $this->validateTopic($request, updating: true);

        $knowledgeTopic->update([
            ...$validated,
            'is_active' => $request->boolean('is_active'),
        ]);

        return to_route('business-services.manage');
    }

    public function destroy(KnowledgeTopic $knowledgeTopic): JsonResponse
    {
        Gate::authorize('delete', $knowledgeTopic);

        $knowledgeTopic->delete();

        return response()->json(null, 204);
    }

    public function destroyFromPage(KnowledgeTopic $knowledgeTopic): RedirectResponse
    {
        $knowledgeTopic->delete();

        return to_route('business-services.manage');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTopic(Request $request, bool $updating = false): array
    {
        $this->prepareKeywordsForValidation($request);

        return $request->validate([
            'topic' => [$updating ? 'sometimes' : 'required', 'string', 'max:255'],
            'keywords' => ['nullable', 'array'],
            'keywords.*' => ['string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
