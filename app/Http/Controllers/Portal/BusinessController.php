<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\BusinessRequest;
use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    public function store(BusinessRequest $request): RedirectResponse
    {
        $customer = $request->user()->customer;
        $customer->businesses()->create($request->validated() + ['is_primary' => ! $customer->businesses()->exists()]);

        return redirect()->to(route('portal.profile.edit').'#businesses')->with('success', 'Business added.');
    }

    public function update(BusinessRequest $request, Business $business): RedirectResponse
    {
        $business->update($request->validated());

        return redirect()->to(route('portal.profile.edit').'#businesses')->with('success', 'Business details updated.');
    }

    public function destroy(Request $request, Business $business): RedirectResponse
    {
        abort_unless($business->customer_id === $request->user()->customer->id, 403);

        if ($business->customer->applications()->where('business_id', $business->id)->exists()) {
            return back()->with('error', 'This business has applications linked to it and cannot be removed.');
        }

        $business->delete();

        return redirect()->to(route('portal.profile.edit').'#businesses')->with('success', 'Business removed.');
    }
}
