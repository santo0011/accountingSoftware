<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\ContactRequest;
use App\Services\LeadService;
use Illuminate\Http\RedirectResponse;

/** Website enquiries become CRM leads. */
class ContactController extends Controller
{
    public function __construct(private LeadService $leads) {}

    public function store(ContactRequest $request): RedirectResponse
    {
        $this->leads->create($request->safe()->except('website') + ['source' => 'contact_form']);

        return back()->with('success', 'Thank you! Our expert will contact you within one working day.');
    }

    public function callback(ContactRequest $request): RedirectResponse
    {
        $this->leads->create($request->safe()->only(['name', 'phone', 'email', 'service_id']) + [
            'source' => 'website',
            'message' => 'Requested a call back from the website.',
        ]);

        return back()->with('success', 'Thanks! We will call you back shortly.');
    }
}
