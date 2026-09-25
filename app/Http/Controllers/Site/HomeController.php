<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    /** Popular services, in display order. Falls back to featured services if any are missing. */
    private const POPULAR = ['trademark-registration', 'gst-registration', 'private-limited-company-registration', 'fssai-registration'];

    /** Main category cards: slug => label shown on the homepage. */
    private const CATEGORIES = [
        'business-registration' => 'Business Registration',
        'trademark-ipr' => 'Trademark & IPR',
        'gst-tax' => 'GST & Tax',
        'accounting-bookkeeping' => 'Accounting',
        'licenses-certification' => 'License & Certification',
        'annual-compliance' => 'Annual Compliance',
        'legal-odr' => 'Legal Support',
        'business-consultancy' => 'Business Consultancy',
        'business-technology' => 'Business Technology',
    ];

    private const TECHNOLOGY = [
        'website-development', 'software-development', 'erp-software', 'crm-software',
        'accounting-software', 'mobile-app-development',
    ];

    public function __invoke(): View
    {
        $data = Cache::remember('site.home', now()->addMinutes(30), function () {
            $categories = ServiceCategory::active()->whereIn('slug', array_keys(self::CATEGORIES))
                ->withCount('activeServices')->get()->keyBy('slug');

            return [
                'popular' => $this->servicesBySlug(self::POPULAR)
                    ->whenEmpty(fn () => Service::active()->where('is_featured', true)->limit(4)->get()),
                'categories' => collect(self::CATEGORIES)
                    ->map(fn ($label, $slug) => $categories->get($slug) ? ['category' => $categories->get($slug), 'label' => $label] : null)
                    ->filter()->values(),
                'technology' => $this->servicesBySlug(self::TECHNOLOGY),
                'faqs' => Faq::active()->limit(5)->get(),
            ];
        });

        return view('site.home', $data);
    }

    /** Active services for the given slugs, in the same order. */
    private function servicesBySlug(array $slugs): Collection
    {
        $services = Service::active()->whereIn('slug', $slugs)->with('category:id,slug,icon,name')->get()->keyBy('slug');

        return collect($slugs)->map(fn ($slug) => $services->get($slug))->filter()->values();
    }
}
