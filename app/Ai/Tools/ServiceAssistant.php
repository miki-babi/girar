<?php

namespace App\Ai\Tools;

use App\Models\KnowledgeBase;
use App\Models\Suggestion;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ServiceAssistant implements Tool
{
    public function __construct(private readonly ?string $chatId = null) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search the knowledge base and return concise topic and answer details.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $question = trim((string) ($request['question'] ?? ''));

        if ($question === '') {
            return 'No question was provided to search the knowledge base.';
        }

        $entries = KnowledgeBase::query()
            ->active()
            ->matchingQuestion($question)
            ->with('knowledgeTopic:id,topic')
            ->orderBy('sub_topic')
            ->limit(3)
            ->get(['knowledge_topic_id', 'sub_topic', 'description']);

        if ($entries->isEmpty()) {
            Suggestion::firstOrCreate(
                ['question' => $question],
                ['chat_id' => $this->chatId, 'added_to_kb' => false],
            );

            return 'No matching knowledge base entries found for this question.';
        }

        return $entries
            ->map(fn (KnowledgeBase $entry) => "{$entry->knowledgeTopic->topic} — {$entry->sub_topic}: {$entry->description}")
            ->implode("\n");
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'question' => $schema->string()
                ->description('The customer question to search for in the knowledge base.')
                ->required(),
        ];
    }
}
