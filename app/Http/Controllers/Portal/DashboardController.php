<?php

namespace App\Http\Controllers\Portal;

use App\Enums\ApplicationStatus;
use App\Enums\DocumentStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $customer = $request->user()->customer;

        $applications = $customer->applications();
        $pendingDocs = DocumentRequest::open()->whereIn('application_id', $customer->applications()->select('id'))->count()
            + $customer->applications()->join('application_documents', 'application_documents.application_id', '=', 'applications.id')
                ->where('application_documents.status', DocumentStatus::ReuploadRequired)->count();

        $stats = [
            'active' => (clone $applications)->active()->count(),
            'completed' => (clone $applications)->where('status', ApplicationStatus::Completed)->count(),
            'pending_documents' => $pendingDocs,
            'pending_payments' => (clone $applications)->where('payment_status', PaymentStatus::Pending)->where('total', '>', 0)
                ->whereNotIn('status', [ApplicationStatus::Cancelled, ApplicationStatus::Rejected])->count(),
            'upcoming_compliance' => $customer->complianceRecords()->pending()->whereDate('due_date', '<=', now()->addDays(30))->count(),
        ];

        return view('portal.dashboard', [
            'customer' => $customer,
            'stats' => $stats,
            'activeApplications' => $customer->applications()->active()->with('service:id,name,icon,service_category_id', 'service.category:id,icon')->latest()->limit(5)->get(),
            'actionNeeded' => $customer->applications()->active()
                ->where(fn ($q) => $q->where('status', ApplicationStatus::DocumentsPending)
                    ->orWhere(fn ($p) => $p->where('payment_status', PaymentStatus::Pending)->where('total', '>', 0)))
                ->with('service:id,name')->latest()->limit(4)->get(),
            'compliance' => $customer->complianceRecords()->pending()->with('business:id,name')->orderBy('due_date')->limit(5)->get(),
            'payments' => $customer->payments()->with('application:id,application_no,service_id', 'application.service:id,name')->latest()->limit(5)->get(),
            'suggested' => Service::active()->where('is_featured', true)
                ->whereNotIn('id', $customer->applications()->select('service_id'))->with('category:id,icon')->limit(3)->get(),
        ]);
    }
}
