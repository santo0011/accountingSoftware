<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Support\SiteCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:categories.manage')];
    }

    public function index(): View
    {
        $categories = ServiceCategory::withCount('services')->orderBy('sort_order')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new ServiceCategory(['status' => true, 'sort_order' => ServiceCategory::max('sort_order') + 1])]);
    }

    public function store(Request $request): RedirectResponse
    {
        ServiceCategory::create($this->validated($request, new ServiceCategory));
        SiteCache::flush();

        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(ServiceCategory $category): View
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(Request $request, ServiceCategory $category): RedirectResponse
    {
        $category->update($this->validated($request, $category));
        SiteCache::flush();

        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(ServiceCategory $category): RedirectResponse
    {
        if ($category->services()->exists()) {
            return back()->with('error', 'Move or delete the services in this category first.');
        }

        $category->delete();
        SiteCache::flush();

        return back()->with('success', 'Category deleted.');
    }

    private function validated(Request $request, ServiceCategory $category): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('name'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'alpha_dash', 'max:140', Rule::unique('service_categories', 'slug')->ignore($category->id)],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'icon' => ['nullable', 'string', 'max:60', 'regex:/^bi-[a-z0-9-]+$/'],
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
        ]);

        if ($request->hasFile('banner')) {
            if ($category->banner_image) {
                Storage::disk('public')->delete($category->banner_image);
            }
            $data['banner_image'] = $request->file('banner')->store('categories', 'public');
        }
        unset($data['banner']);
        $data['sort_order'] ??= 0;

        return $data;
    }
}
