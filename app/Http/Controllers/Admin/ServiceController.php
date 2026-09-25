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
            ->orderBy('service_category_id')->orderBy('sort_order')->paginate(25)->withQueryString();

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

        return redirect()->route('admin.services.edit', $service)->with('success', 'Service created.');
    }

    public function edit(Service $service): View
    {
        $service->load('documents', 'fields', 'faqs', 'steps');

        return view('admin.services.form', $this->formData($service));
    }

    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        $this->catalog->save($service, $request->validated());

        return back()->with('success', 'Service updated.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        if ($service->applications()->exists()) {
            $service->update(['status' => false]);
            SiteCache::flush();

            return back()->with('warning', 'This service has applications, so it was disabled instead of deleted.');
        }

        $service->delete();
        SiteCache::flush();

        return redirect()->route('admin.services.index')->with('success', 'Service deleted.');
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
