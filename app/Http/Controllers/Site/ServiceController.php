<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $categories = ServiceCategory::active()
            ->with(['activeServices' => function ($query) use ($q) {
                $query->with('category:id,icon')->when($q, fn ($s) => $s->where(fn ($w) => $w
                    ->where('name', 'like', "%{$q}%")->orWhere('short_description', 'like', "%{$q}%")));
            }])->get()
            ->filter(fn ($c) => $c->activeServices->isNotEmpty());

        return view('site.services.index', compact('categories', 'q'));
    }

    public function category(ServiceCategory $category): View
    {
        abort_unless($category->status, 404);

        $category->load(['activeServices' => fn ($q) => $q->with('category:id,icon')]);
        $others = ServiceCategory::active()->where('id', '!=', $category->id)->withCount('activeServices')->get();

        return view('site.services.category', compact('category', 'others'));
    }

    public function show(Service $service): View
    {
        abort_unless($service->status && $service->category?->status, 404);

        $service->load(['category', 'documents', 'steps', 'faqs']);
        $related = Service::active()->where('service_category_id', $service->service_category_id)
            ->where('id', '!=', $service->id)->with('category:id,icon')->orderByDesc('is_featured')->limit(4)->get();

        return view('site.services.show', compact('service', 'related'));
    }

    /** AJAX autocomplete used by the hero and services search boxes. */
    public function search(Request $request): JsonResponse
    {
        $q = trim(mb_substr((string) $request->query('q'), 0, 60));
        if (mb_strlen($q) < 2) {
            return response()->json(['data' => []]);
        }

        $results = Service::active()->whereHas('category', fn ($c) => $c->where('status', true))
            ->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('short_description', 'like', "%{$q}%"))
            ->with('category:id,name,icon')
            ->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', ["{$q}%"])
            ->limit(8)->get()
            ->map(fn (Service $s) => [
                'name' => $s->name,
                'category' => $s->category->name,
                'icon' => $s->iconClass(),
                'price' => $s->effectivePrice() > 0 ? money($s->effectivePrice(), false) : null,
                'url' => route('site.services.show', $s->slug),
            ]);

        return response()->json(['data' => $results]);
    }
}
