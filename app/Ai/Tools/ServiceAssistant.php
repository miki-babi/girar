<?php

namespace App\Ai\Tools;

use App\Models\BusinessService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ServiceAssistant implements Tool
{
    private const NOT_FOUND_REPLY = 'we will get back to you soon';

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search the business_services table and return concise service details. If no matching service exists, reply exactly: we will get back to you soon';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $question = trim((string) ($request['question'] ?? ''));

        if ($question === '') {
            return self::NOT_FOUND_REPLY;
        }

        $services = BusinessService::query()
            ->active()
            ->matchingQuestion($question)
            ->orderBy('topic')
            ->limit(3)
            ->get(['topic', 'description']);

        if ($services->isEmpty()) {
            return self::NOT_FOUND_REPLY;
        }

        return $services
            ->map(fn (BusinessService $service) => "{$service->topic}: {$service->description}")
            ->implode("\n");
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'question' => $schema->string()
                ->description('The customer question to search for in the business service knowledge base.')
                ->required(),
        ];
    }
}
