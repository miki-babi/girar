<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BookingLink extends Model
{
    protected $fillable = [
        'name',
        'url',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function (BookingLink $bookingLink): void {
            if (! $bookingLink->is_active) {
                return;
            }

            static::query()
                ->whereKeyNot($bookingLink->getKey())
                ->where('is_active', true)
                ->update(['is_active' => false]);
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
