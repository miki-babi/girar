<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBase;
use App\Models\KnowledgeTopic;
use App\Models\Suggestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SuggestionController extends Controller
{
    public function manage(Request $request): Response
    {
        $suggestions = Suggestion::query()
            ->pending()
            ->with('knowledgeTopic:id,topic')
            ->latest()
            ->get();

        $userId = $request->user()?->id ?? \App\Models\User::first()?->id ?? 1;

        $topics = KnowledgeTopic::query()
            ->forBusiness($userId)
            ->orderBy('topic')
            ->get(['id', 'topic']);

        return Inertia::render('suggestions', [
            'suggestions' => $suggestions,
            'topics'      => $topics,
        ]);
    }

    /**
     * Promote a suggestion to the knowledge base.
     */
    public function promote(Request $request, Suggestion $suggestion): RedirectResponse
    {
        $validated = $request->validate([
            'knowledge_topic_id' => ['required', 'integer', Rule::exists('knowledge_topics', 'id')],
            'sub_topic'          => ['required', 'string', 'max:255'],
            'description'        => ['required', 'string'],
            'keywords'           => ['nullable', 'string'],
        ]);

        $keywords = array_values(array_filter(
            array_map('trim', explode(',', $validated['keywords'] ?? '')),
        ));

        KnowledgeBase::create([
            'knowledge_topic_id' => $validated['knowledge_topic_id'],
            'sub_topic'          => $validated['sub_topic'],
            'description'        => $validated['description'],
            'keywords'           => $keywords ?: null,
            'is_active'          => true,
        ]);

        $suggestion->update([
            'added_to_kb'        => true,
            'knowledge_topic_id' => $validated['knowledge_topic_id'],
        ]);

        return to_route('suggestions.manage');
    }

    /**
     * Dismiss a suggestion without adding it.
     */
    public function dismiss(Suggestion $suggestion): RedirectResponse
    {
        $suggestion->update(['added_to_kb' => true]);

        return to_route('suggestions.manage');
    }

    /**
     * Bulk dismiss all pending suggestions.
     */
    public function dismissAll(): RedirectResponse
    {
        Suggestion::query()->pending()->update(['added_to_kb' => true]);

        return to_route('suggestions.manage');
    }
}
