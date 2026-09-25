<?php

namespace App\Http\Controllers\Portal;

use App\Enums\ComplianceStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplianceController extends Controller
{
    public function index(Request $request): View
    {
        $customer = $request->user()->customer;

        $upcoming = $customer->complianceRecords()->pending()->with('business:id,name', 'type:id,name,description')
            ->orderBy('due_date')->get()
            ->groupBy(fn ($r) => $r->due_date->format('F Y'));

        $completed = $customer->complianceRecords()->where('status', ComplianceStatus::Completed)
            ->with('business:id,name')->latest('completed_at')->limit(10)->get();

        $counts = [
            'overdue' => $customer->complianceRecords()->where('status', ComplianceStatus::Overdue)->count(),
            'due_soon' => $customer->complianceRecords()->where('status', ComplianceStatus::DueSoon)->count(),
            'upcoming' => $customer->complianceRecords()->where('status', ComplianceStatus::Upcoming)->count(),
            'completed' => $customer->complianceRecords()->where('status', ComplianceStatus::Completed)->count(),
        ];

        return view('portal.compliance.index', compact('upcoming', 'completed', 'counts'));
    }
}
