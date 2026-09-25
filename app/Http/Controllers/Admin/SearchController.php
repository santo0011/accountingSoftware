<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Global search from the admin top bar. */
class SearchController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $q = trim(mb_substr((string) $request->query('q'), 0, 80));
        $user = $request->user();

        if ($q === '') {
            return redirect()->route('admin.dashboard');
        }

        // Exact application number → go straight to it.
        if ($exact = Application::visibleTo($user)->where('application_no', $q)->first()) {
            return redirect()->route('admin.applications.show', $exact);
        }

        $term = "%{$q}%";

        return view('admin.search', [
            'q' => $q,
            'applications' => $user->can('applications.view')
                ? Application::visibleTo($user)->with('customer.user:id,name', 'service:id,name')
                    ->where(fn ($w) => $w->where('application_no', 'like', $term)->orWhereHas('customer.user', fn ($u) => $u->where('name', 'like', $term)->orWhere('mobile', 'like', $term)))
                    ->latest()->limit(10)->get()
                : collect(),
            'customers' => $user->can('customers.view')
                ? Customer::with('user:id,name,email,mobile')
                    ->where(fn ($w) => $w->where('customer_code', 'like', $term)->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('mobile', 'like', $term))
                        ->orWhereHas('businesses', fn ($b) => $b->where('name', 'like', $term)->orWhere('gstin', 'like', $term)))
                    ->limit(10)->get()
                : collect(),
            'invoices' => $user->can('invoices.view')
                ? Invoice::where('invoice_no', 'like', $term)->orWhere('billing_name', 'like', $term)->limit(10)->get()
                : collect(),
            'leads' => $user->can('leads.view')
                ? Lead::where(fn ($w) => $w->where('name', 'like', $term)->orWhere('phone', 'like', $term)->orWhere('email', 'like', $term)->orWhere('company', 'like', $term))
                    ->when(! $user->can('leads.view_all'), fn ($l) => $l->where('assigned_to', $user->id))->limit(10)->get()
                : collect(),
        ]);
    }
}
