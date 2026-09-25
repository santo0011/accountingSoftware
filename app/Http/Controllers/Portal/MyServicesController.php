<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyServicesController extends Controller
{
    public function index(Request $request): View
    {
        $customer = $request->user()->customer;

        $subscriptions = $customer->customerServices()
            ->with(['service.category:id,icon', 'business:id,name', 'complianceRecords' => fn ($q) => $q->pending()->orderBy('due_date')])
            ->latest()->get();

        $inProgress = $customer->applications()->active()->with('service.category:id,icon')->latest()->get();

        return view('portal.services.index', compact('subscriptions', 'inProgress'));
    }
}
