<?php

namespace App\Http\Concerns;

use Illuminate\Http\Request;

trait ParsesCommaSeparatedKeywords
{
    protected function prepareKeywordsForValidation(Request $request): void
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
    protected function keywordsFromString(string $keywords): array
    {
        return collect(explode(',', $keywords))
            ->map(fn (string $keyword) => trim($keyword))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
