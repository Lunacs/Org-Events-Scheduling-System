<?php

namespace App\Services\Cache;

use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;

trait SupportsTags
{
    /**
     * Check if the current cache store supports tagging.
     */
    protected static function supportsTags(): bool
    {
        return Cache::store()->getStore() instanceof TaggableStore;
    }
}
