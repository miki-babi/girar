<?php

namespace App\Models;

use Database\Factories\KnowledgeBaseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class KnowledgeBase extends Model
{
    /** @use HasFactory<KnowledgeBaseFactory> */
    use HasFactory;

    protected $fillable = [
        'knowledge_topic_id',
        'sub_topic',
        'description',
        'keywords',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function knowledgeTopic(): BelongsTo
    {
        return $this->belongsTo(KnowledgeTopic::class, 'knowledge_topic_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->whereHas('knowledgeTopic', fn (Builder $query) => $query->where('is_active', true));
    }

    public function scopeMatchingQuestion(Builder $query, string $question): Builder
    {
        $terms = collect(preg_split('/\s+/', Str::lower($question), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn (string $term) => trim($term, " \t\n\r\0\x0B.,!?;:\"'()[]{}"))
            ->filter(fn (string $term) => Str::length($term) >= 3)
            ->prepend(Str::lower(trim($question)))
            ->filter()
            ->unique()
            ->take(8)
            ->values();

        if ($terms->isEmpty()) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($terms): void {
            foreach ($terms as $term) {
                $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $term).'%';

                $query->orWhere('sub_topic', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('keywords', 'like', $like)
                    ->orWhereHas('knowledgeTopic', function (Builder $query) use ($like): void {
                        $query->where('topic', 'like', $like)
                            ->orWhere('keywords', 'like', $like);
                    });
            }
        });
    }
}
