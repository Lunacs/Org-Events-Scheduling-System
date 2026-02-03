<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'category',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Cache duration in seconds
     */
    protected static int $cacheDuration = 300;

    /**
     * Scope for active FAQs only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordering by display_order then created_at
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('created_at');
    }

    /**
     * Get all active FAQs grouped by category with caching
     */
    public static function getActiveGroupedByCategory(): \Illuminate\Support\Collection
    {
        return Cache::remember('faqs_grouped_by_category', static::$cacheDuration, function () {
            return static::active()
                ->ordered()
                ->get()
                ->groupBy(function ($faq) {
                    return $faq->category ?: 'General';
                });
        });
    }

    /**
     * Get all active FAQs with caching
     */
    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('faqs_active', static::$cacheDuration, function () {
            return static::active()->ordered()->get();
        });
    }

    /**
     * Get all unique categories
     */
    public static function getCategories(): array
    {
        return Cache::remember('faqs_categories', static::$cacheDuration, function () {
            return static::whereNotNull('category')
                ->distinct()
                ->pluck('category')
                ->filter()
                ->values()
                ->toArray();
        });
    }

    /**
     * Clear all FAQ-related caches
     */
    public static function clearCache(): void
    {
        Cache::forget('faqs_grouped_by_category');
        Cache::forget('faqs_active');
        Cache::forget('faqs_categories');
        Cache::forget('faqs_all');
    }

    /**
     * Boot method to clear cache on model events
     */
    protected static function booted(): void
    {
        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }
}
