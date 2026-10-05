<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Support\SiteCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FaqController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:cms.manage')];
    }

    public function index(): View
    {
        return view('admin.cms.faqs.index', ['faqs' => Faq::orderBy('group')->orderBy('sort_order')->paginate(per_page(20))->withQueryString()]);
    }

    public function create(): View
    {
        return view('admin.cms.faqs.form', ['faq' => new Faq(['status' => true, 'group' => 'general'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Faq::create($this->validated($request));
        SiteCache::flush();

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ added.');
    }

    public function edit(Faq $faq): View
    {
        return view('admin.cms.faqs.form', compact('faq'));
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->validated($request));
        SiteCache::flush();

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ updated.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();
        SiteCache::flush();

        return back()->with('success', 'FAQ deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:3000'],
            'group' => ['required', Rule::in(array_keys(Faq::GROUPS))],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['boolean'],
        ]);

        return $data + ['sort_order' => $data['sort_order'] ?? 0];
    }
}
