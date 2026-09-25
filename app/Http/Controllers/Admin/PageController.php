<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\Html;
use App\Support\SiteCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Website Content → Pages (About, policies). */
class PageController extends Controller implements HasMiddleware
{
    /** Pages linked from fixed routes; they can be edited but not deleted. */
    private const SYSTEM = ['about-us', 'privacy-policy', 'terms-and-conditions', 'refund-policy'];

    public static function middleware(): array
    {
        return [new Middleware('permission:cms.manage')];
    }

    public function index(): View
    {
        return view('admin.cms.pages.index', ['pages' => Page::orderBy('title')->get(), 'system' => self::SYSTEM]);
    }

    public function create(): View
    {
        return view('admin.cms.pages.form', ['page' => new Page(['status' => true]), 'system' => self::SYSTEM]);
    }

    public function store(Request $request): RedirectResponse
    {
        Page::create($this->validated($request, new Page));
        SiteCache::flush();

        return redirect()->route('admin.pages.index')->with('success', 'Page created.');
    }

    public function edit(Page $page): View
    {
        return view('admin.cms.pages.form', ['page' => $page, 'system' => self::SYSTEM]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $page->update($this->validated($request, $page));
        SiteCache::flush();

        return redirect()->route('admin.pages.index')->with('success', 'Page updated.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        abort_if(in_array($page->slug, self::SYSTEM, true), 403, 'System pages cannot be deleted.');
        $page->delete();
        SiteCache::flush();

        return back()->with('success', 'Page deleted.');
    }

    private function validated(Request $request, Page $page): array
    {
        if (in_array($page->slug, self::SYSTEM, true)) {
            $request->merge(['slug' => $page->slug]);
        } else {
            $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('title'))]);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'alpha_dash', 'max:150', Rule::unique('pages', 'slug')->ignore($page->id)],
            'body' => ['nullable', 'string', 'max:100000'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'status' => ['boolean'],
        ]);
        $data['body'] = Html::clean($data['body'] ?? null);

        return $data;
    }
}
