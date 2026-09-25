<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.profile', ['user' => $request->user()->load('staffProfile', 'professional')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $request->merge(['mobile' => preg_replace('/\D/', '', (string) $request->input('mobile')) ?: null]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'mobile' => ['nullable', 'regex:/^[6-9][0-9]{9}$/', Rule::unique('users', 'mobile')->ignore($user->id)],
        ]);

        $user->update($data);

        return back()->with('success', 'Profile updated.');
    }

    public function password(PasswordUpdateRequest $request): RedirectResponse
    {
        $request->user()->update(['password' => $request->validated('password')]);
        activity('security')->causedBy($request->user())->log('Changed own password');

        return back()->with('success', 'Password changed.');
    }
}
