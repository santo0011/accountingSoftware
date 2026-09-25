<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $xml = Cache::remember('site.sitemap', now()->addHours(6), function () {
            $urls = collect([
                [route('site.home'), now(), '1.0'],
                [route('site.services.index'), now(), '0.9'],
                [route('site.pricing'), now(), '0.7'],
                [route('site.about'), now(), '0.5'],
                [route('site.faq'), now(), '0.5'],
                [route('site.contact'), now(), '0.5'],
            ]);

            ServiceCategory::active()->get(['slug', 'updated_at'])
                ->each(fn ($c) => $urls->push([route('site.categories.show', $c->slug), $c->updated_at, '0.8']));
            Service::active()->whereHas('category', fn ($q) => $q->where('status', true))->get(['slug', 'updated_at'])
                ->each(fn ($s) => $urls->push([route('site.services.show', $s->slug), $s->updated_at, '0.8']));
            Page::where('status', true)->where('slug', '!=', 'about-us')->get(['slug', 'updated_at'])
                ->each(fn ($p) => $urls->push([url($p->slug), $p->updated_at, '0.3']));

            return view('site.sitemap', ['urls' => $urls])->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /account',
            'Disallow: /login',
            'Disallow: /register',
            'Allow: /',
            '',
            'Sitemap: '.route('site.sitemap'),
        ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain']);
    }
}
