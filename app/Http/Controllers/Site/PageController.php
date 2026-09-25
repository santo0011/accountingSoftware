<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Testimonial;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        $page = Page::where('slug', 'about-us')->where('status', true)->firstOrFail();
        $categories = ServiceCategory::active()->withCount('activeServices')->get();
        $testimonials = Testimonial::active()->limit(3)->get();

        return view('site.pages.about', compact('page', 'categories', 'testimonials'));
    }

    public function pricing(): View
    {
        $categories = ServiceCategory::active()->with('activeServices')->get()->filter(fn ($c) => $c->activeServices->isNotEmpty());

        return view('site.pages.pricing', compact('categories'));
    }

    public function faq(): View
    {
        $faqs = Faq::active()->get()->groupBy('group');

        return view('site.pages.faq', compact('faqs'));
    }

    public function contact(): View
    {
        $services = Service::active()->orderBy('name')->pluck('name', 'id');

        return view('site.pages.contact', compact('services'));
    }

    /** Policy / static pages managed in Admin → Website Content. */
    public function show(string $slug): View
    {
        $page = Page::where('slug', $slug)->where('status', true)->firstOrFail();

        return view('site.pages.show', compact('page'));
    }
}
