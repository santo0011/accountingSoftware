<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ComplianceStatus;
use App\Enums\DocumentStatus;
use App\Enums\PaymentStatus;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\ComplianceRecord;
use App\Models\Payment;
use App\Models\Task;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, ReportService $reports): View
    {
        $user = $request->user();
        $visibleIds = Application::visibleTo($user)->select('id');

        return view('admin.dashboard', [
            'stats' => $reports->dashboardStats(),
            'revenue' => $user->can('reports.view') || $user->can('payments.view') ? $reports->revenueByMonth(12) : null,
            'byStatus' => $reports->applicationsByStatus(),
            'recentApplications' => Application::visibleTo($user)->with('customer.user:id,name', 'service:id,name')->latest()->limit(7)->get(),
            'pendingDocuments' => $user->can('documents.view')
                ? ApplicationDocument::where('type', 'customer')->whereIn('status', [DocumentStatus::Pending, DocumentStatus::UnderReview])
                    ->whereIn('application_id', $visibleIds)->with('application:id,application_no,customer_id', 'application.customer.user:id,name')->latest()->limit(6)->get()
                : collect(),
            'recentPayments' => $user->can('payments.view') ? Payment::with('customer.user:id,name')->latest()->limit(6)->get() : collect(),
            'pendingVerification' => $user->can('payments.manage') ? Payment::where('status', PaymentStatus::Pending)->whereNotNull('transaction_id')->count() : 0,
            'upcomingCompliance' => $user->can('compliance.view')
                ? ComplianceRecord::pending()->with('customer.user:id,name')->orderBy('due_date')
                    ->when(! $user->can('compliance.view_all'), fn ($q) => $q->where('assigned_staff_id', $user->id))->limit(6)->get()
                : collect(),
            'overdueCompliance' => ComplianceRecord::where('status', ComplianceStatus::Overdue)->count(),
            'myTasks' => Task::where('assigned_to', $user->id)->whereIn('status', [TaskStatus::Pending, TaskStatus::InProgress])->orderBy('due_date')->limit(6)->get(),
        ]);
    }
}
