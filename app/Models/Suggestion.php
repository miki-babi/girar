<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Suggestion extends Model
{
    protected $fillable = [
        'question',
        'chat_id',
        'knowledge_topic_id',
        'added_to_kb',
    ];

    protected function casts(): array
    {
        return [
            'added_to_kb' => 'boolean',
        ];
    }

    public function knowledgeTopic(): BelongsTo
    {
        return $this->belongsTo(KnowledgeTopic::class, 'knowledge_topic_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('added_to_kb', false);
    }
}
