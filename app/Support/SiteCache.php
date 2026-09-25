<?php

namespace App\Support;

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Cache;

/** Clears cached public-website fragments after catalogue / content changes. */
class SiteCache
{
    public static function flush(): void
    {
        Cache::forget('site.home');
        Cache::forget('site.sitemap');
        Cache::forget(AppServiceProvider::MENU_CACHE_KEY);
    }
}
