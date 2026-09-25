<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Support\SiteCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class TestimonialController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:cms.manage')];
    }

    public function index(): View
    {
        return view('admin.cms.testimonials.index', ['testimonials' => Testimonial::orderBy('sort_order')->get()]);
    }

    public function create(): View
    {
        return view('admin.cms.testimonials.form', ['testimonial' => new Testimonial(['status' => true, 'rating' => 5])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Testimonial::create($this->validated($request));
        SiteCache::flush();

        return redirect()->route('admin.testimonials.index')->with('success', 'Review added.');
    }

    public function edit(Testimonial $testimonial): View
    {
        return view('admin.cms.testimonials.form', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update($this->validated($request));
        SiteCache::flush();

        return redirect()->route('admin.testimonials.index')->with('success', 'Review updated.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();
        SiteCache::flush();

        return back()->with('success', 'Review deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'designation' => ['nullable', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:150'],
            'city' => ['nullable', 'string', 'max:100'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'message' => ['required', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['boolean'],
        ]);

        return $data + ['sort_order' => $data['sort_order'] ?? 0];
    }
}
