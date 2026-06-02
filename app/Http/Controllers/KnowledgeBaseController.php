<?php

namespace App\Http\Controllers;

use App\Http\Concerns\ParsesCommaSeparatedKeywords;
use App\Models\KnowledgeBase;
use App\Models\KnowledgeTopic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class KnowledgeBaseController extends Controller
{
    use ParsesCommaSeparatedKeywords;

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', KnowledgeBase::class);

        $entries = KnowledgeBase::query()
            ->whereHas('knowledgeTopic', fn ($query) => $query->forBusiness($request->user()->id))
            ->with('knowledgeTopic:id,topic')
            ->latest()
            ->get();

        return response()->json($entries);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateSubtopic($request);

        Gate::authorize('create', KnowledgeBase::class);

        $this->authorizeTopicOwnership($request, (int) $validated['knowledge_topic_id']);

        $entry = KnowledgeBase::create($validated);

        return response()->json($entry->load('knowledgeTopic:id,topic'), 201);
    }

    public function storeFromPage(Request $request): RedirectResponse
    {
        $validated = $this->validateSubtopic($request);

        KnowledgeBase::create([
            ...$validated,
            'is_active' => $request->boolean('is_active'),
        ]);

        return to_route('business-services.manage');
    }

    public function show(KnowledgeBase $knowledgeBase): JsonResponse
    {
        Gate::authorize('view', $knowledgeBase);

        return response()->json($knowledgeBase->load('knowledgeTopic'));
    }

    public function update(Request $request, KnowledgeBase $knowledgeBase): JsonResponse
    {
        $validated = $this->validateSubtopic($request, updating: true);

        Gate::authorize('update', $knowledgeBase);

        if (isset($validated['knowledge_topic_id'])) {
            $this->authorizeTopicOwnership($request, (int) $validated['knowledge_topic_id']);
        }

        $knowledgeBase->update($validated);

        return response()->json($knowledgeBase->refresh()->load('knowledgeTopic:id,topic'));
    }

    public function updateFromPage(Request $request, KnowledgeBase $knowledgeBase): RedirectResponse
    {
        $validated = $this->validateSubtopic($request, updating: true);

        $knowledgeBase->update([
            ...$validated,
            'is_active' => $request->boolean('is_active'),
        ]);

        return to_route('business-services.manage');
    }

    public function destroy(KnowledgeBase $knowledgeBase): JsonResponse
    {
        Gate::authorize('delete', $knowledgeBase);

        $knowledgeBase->delete();

        return response()->json(null, 204);
    }

    public function destroyFromPage(KnowledgeBase $knowledgeBase): RedirectResponse
    {
        $knowledgeBase->delete();

        return to_route('business-services.manage');
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:1000'],
        ]);

        $entries = KnowledgeBase::query()
            ->active()
            ->matchingQuestion($validated['question'])
            ->with('knowledgeTopic:id,topic')
            ->orderBy('sub_topic')
            ->limit(10)
            ->get(['id', 'knowledge_topic_id', 'sub_topic']);

        return response()->json(
            $entries->map(fn (KnowledgeBase $entry) => [
                'id' => $entry->id,
                'topic' => $entry->knowledgeTopic->topic,
                'sub_topic' => $entry->sub_topic,
            ]),
        );
    }

    public function showForAgent(string $id): JsonResponse
    {
        $entry = KnowledgeBase::query()
            ->active()
            ->with('knowledgeTopic:id,topic')
            ->findOrFail($id, ['id', 'knowledge_topic_id', 'sub_topic', 'description']);

        return response()->json([
            'topic' => $entry->knowledgeTopic->topic,
            'sub_topic' => $entry->sub_topic,
            'description' => $entry->description,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateSubtopic(Request $request, bool $updating = false): array
    {
        $this->prepareKeywordsForValidation($request);

        $userId = $request->user()?->id ?? \App\Models\User::first()?->id ?? 1;

        return $request->validate([
            'knowledge_topic_id' => [
                $updating ? 'sometimes' : 'required',
                'integer',
                Rule::exists('knowledge_topics', 'id')->where(
                    'business_id',
                    $userId,
                ),
            ],
            'sub_topic' => [$updating ? 'sometimes' : 'required', 'string', 'max:255'],
            'description' => [$updating ? 'sometimes' : 'required', 'string'],
            'keywords' => ['nullable', 'array'],
            'keywords.*' => ['string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }

    private function authorizeTopicOwnership(Request $request, int $topicId): void
    {
        $topic = KnowledgeTopic::query()
            ->forBusiness($request->user()->id)
            ->findOrFail($topicId);

        Gate::authorize('view', $topic);
    }
}
