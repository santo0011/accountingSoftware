<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceRequest;
use App\Models\ComplianceType;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\ServiceCatalogService;
use App\Support\SiteCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ServiceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:services.view', only: ['index']),
            new Middleware('permission:services.manage', except: ['index']),
        ];
    }

    public function __construct(private ServiceCatalogService $catalog) {}

    public function index(Request $request): View
    {
        $services = Service::with('category:id,name')->withCount('applications')
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->when($request->filled('category'), fn ($q) => $q->where('service_category_id', $request->category))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status === 'active'))
            ->when($request->filled('billing'), fn ($q) => $q->where('billing_type', $request->billing))
            ->orderBy('service_category_id')->orderBy('sort_order')->paginate(per_page(20))->withQueryString();

        $categories = ServiceCategory::orderBy('sort_order')->pluck('name', 'id');

        return view('admin.services.index', compact('services', 'categories'));
    }

    public function create(): View
    {
        return view('admin.services.form', $this->formData(new Service([
            'gst_rate' => setting('default_tax', 18), 'status' => true, 'billing_type' => 'one_time', 'sac_code' => setting('default_sac_code'),
        ])));
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        $service = $this->catalog->save(new Service, $request->validated());

        return $this->toList('admin.services.index')->with('success', "“{$service->name}” created.");
    }

    public function edit(Service $service): View
    {
        $service->load('documents', 'fields', 'faqs', 'steps');

        return view('admin.services.form', $this->formData($service));
    }

    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        $this->catalog->save($service, $request->validated());

        return $this->toList('admin.services.index')->with('success', "“{$service->name}” updated.");
    }

    public function destroy(Service $service): RedirectResponse
    {
        // Back to the list the admin came from, keeping its filters and page (e.g. ?category=2).
        $previous = url()->previous();
        $list = str_starts_with($previous, route('admin.services.index')) ? $previous : route('admin.services.index');

        if ($service->applications()->exists()) {
            $service->update(['status' => false]);
            SiteCache::flush();

            return redirect()->to($list)->with('warning', "“{$service->name}” has applications, so it was disabled (hidden from the website) instead of deleted.");
        }

        // Soft delete keeps the row for history; free its slug so a new service can reuse the name.
        $service->forceFill(['slug' => Str::limit($service->slug, 140, '').'--deleted-'.$service->id])->saveQuietly();
        $service->delete();
        SiteCache::flush();

        return redirect()->to($list)->with('success', "“{$service->name}” deleted.");
    }

    private function formData(Service $service): array
    {
        return [
            'service' => $service,
            'categories' => ServiceCategory::orderBy('sort_order')->pluck('name', 'id'),
            'complianceTypes' => ComplianceType::where('status', true)->orderBy('name')->pluck('name', 'id'),
        ];
    }
}
