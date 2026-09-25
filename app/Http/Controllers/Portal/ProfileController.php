<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordUpdateRequest;
use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('portal.profile.edit', [
            'user' => $user,
            'customer' => $user->customer,
            'businesses' => $user->customer->businesses()->orderByDesc('is_primary')->get(),
            'businessTypes' => Business::TYPES,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $request->merge(['mobile' => preg_replace('/\D/', '', (string) $request->input('mobile'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'mobile' => ['required', 'regex:/^[6-9][0-9]{9}$/', Rule::unique('users', 'mobile')->ignore($user->id)],
            'alt_phone' => ['nullable', 'string', 'max:15'],
            'pan' => ['nullable', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/i'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'digits:6'],
        ], ['mobile.regex' => 'Enter a valid 10-digit mobile number.', 'pan.regex' => 'Enter a valid PAN, e.g. ABCDE1234F.']);

        $user->update(['name' => $data['name'], 'mobile' => $data['mobile']]);
        $user->customer->update(collect($data)->only(['alt_phone', 'address', 'city', 'state', 'pincode'])->all() + [
            'pan' => isset($data['pan']) ? strtoupper($data['pan']) : null,
        ]);

        return back()->with('success', 'Profile updated.');
    }

    public function password(PasswordUpdateRequest $request): RedirectResponse
    {
        $request->user()->update(['password' => $request->validated('password')]);

        return back()->with('success', 'Password changed successfully.');
    }
}
