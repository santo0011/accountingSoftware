<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerRequest;
use App\Models\Customer;
use App\Models\CustomerService;
use App\Services\CustomerAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:customers.view', only: ['index', 'show']),
            new Middleware('permission:customers.create', only: ['create', 'store']),
            new Middleware('permission:customers.edit', only: ['edit', 'update', 'updateSubscription']),
            new Middleware('permission:customers.delete', only: ['destroy']),
        ];
    }

    public function __construct(private CustomerAccountService $accounts) {}

    public function index(Request $request): View
    {
        $customers = Customer::with('user:id,name,email,mobile,status,last_login_at', 'primaryBusiness')
            ->withCount('applications')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('customer_code', 'like', $term)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('mobile', 'like', $term))
                    ->orWhereHas('businesses', fn ($b) => $b->where('name', 'like', $term)->orWhere('gstin', 'like', $term)));
            })
            ->when($request->filled('status'), fn ($q) => $q->whereHas('user', fn ($u) => $u->where('status', $request->status)))
            ->when($request->filled('state'), fn ($q) => $q->where('state', $request->state))
            ->latest()->paginate(per_page(20))->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('admin.customers.form', ['customer' => new Customer]);
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $customer = $this->accounts->create($data + ['source' => $data['source'] ?? 'admin'], verified: true);
        $customer->update(collect($data)->only(['pan', 'address', 'pincode'])->all());

        if ($request->boolean('send_invite', true)) {
            $this->accounts->sendSetPasswordLink($customer->user);
        }

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer created'.($request->boolean('send_invite', true) ? ' and invited by email.' : '.'));
    }

    public function show(Customer $customer): View
    {
        // Full history for the customer profile (one customer's records, so no paging needed).
        $customer->load([
            'user', 'businesses',
            'applications' => fn ($q) => $q->with(['service:id,name,icon,service_category_id', 'service.category:id,icon', 'business:id,name',
                'staff:id,name', 'professional:id,name', 'invoice'])->withCount('documents')->latest(),
            'payments' => fn ($q) => $q->with('application:id,application_no')->latest(),
            'invoices' => fn ($q) => $q->latest('invoice_date')->latest('id'),
            'customerServices' => fn ($q) => $q->with(['service:id,name,icon,service_category_id', 'service.category:id,icon', 'business:id,name'])->latest('start_date'),
            'complianceRecords' => fn ($q) => $q->orderByRaw('completed_at is not null')->orderBy('due_date'),
            'tickets' => fn ($q) => $q->latest(),
        ]);

        $billed = (float) $customer->invoices->sum('total');
        $paid = (float) $customer->payments->filter(fn ($p) => $p->status->value === 'paid')->sum('amount');
        $totals = ['billed' => $billed, 'paid' => $paid, 'outstanding' => max(0, $billed - $paid)];

        return view('admin.customers.show', compact('customer', 'totals'));
    }

    public function edit(Customer $customer): View
    {
        $customer->load('user');

        return view('admin.customers.form', compact('customer'));
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($customer, $data) {
            $customer->user->update(collect($data)->only(['name', 'email', 'mobile'])->all() + ['status' => $data['status'] ?? $customer->user->status]);
            $customer->update(collect($data)->only(['pan', 'address', 'city', 'state', 'pincode', 'source'])->all());
        });

        activity('customers')->performedOn($customer)->causedBy($request->user())->log('Updated customer '.$customer->customer_code);

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer updated.');
    }

    /** Customers are never hard-deleted (financial records); the account is deactivated. */
    public function destroy(Request $request, Customer $customer): RedirectResponse
    {
        $customer->user->update(['status' => 'inactive']);
        activity('customers')->performedOn($customer)->causedBy($request->user())->log('Deactivated customer '.$customer->customer_code);

        return redirect()->route('admin.customers.index')->with('success', 'Customer account deactivated.');
    }

    public function updateSubscription(Request $request, CustomerService $customerService): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(CustomerService::STATUSES))]]);
        $customerService->update($data + ['end_date' => in_array($data['status'], ['cancelled', 'completed'], true) ? now() : null]);

        activity('customers')->performedOn($customerService)->causedBy($request->user())->log("Service subscription set to {$data['status']}");

        return back()->with('success', 'Service status updated.');
    }
}
