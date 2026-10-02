<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Professional;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfessionalController extends Controller implements HasMiddleware
{
    /** Roles a professional's login may have. */
    private const LOGIN_ROLES = ['legal-professional' => 'Legal Professional', 'tax-professional' => 'Tax Professional'];

    public static function middleware(): array
    {
        return [
            new Middleware('permission:professionals.view', only: ['index']),
            new Middleware('permission:professionals.manage', except: ['index']),
        ];
    }

    public function index(Request $request): View
    {
        $professionals = Professional::with('user:id,email,status')
            ->withCount(['applications as open_applications_count' => fn ($q) => $q->active()])
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%'.$request->q.'%')->orWhere('specialization', 'like', '%'.$request->q.'%')))
            ->when($request->filled('type'), fn ($q) => $q->where('professional_type', $request->type))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderBy('name')->paginate(per_page(20))->withQueryString();

        return view('admin.professionals.index', compact('professionals'));
    }

    public function create(): View
    {
        return view('admin.professionals.form', ['professional' => new Professional(['status' => 'active']), 'loginRoles' => self::LOGIN_ROLES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, new Professional);

        DB::transaction(function () use ($data, $request) {
            $professional = Professional::create(collect($data)->only(['name', 'email', 'phone', 'professional_type', 'registration_no', 'specialization', 'status'])->all());
            $this->syncLogin($professional, $data, $request->boolean('create_login'));
        });

        return redirect()->route('admin.professionals.index')->with('success', 'Professional added.');
    }

    public function edit(Professional $professional): View
    {
        $professional->load('user.roles');

        return view('admin.professionals.form', ['professional' => $professional, 'loginRoles' => self::LOGIN_ROLES]);
    }

    public function update(Request $request, Professional $professional): RedirectResponse
    {
        $data = $this->validated($request, $professional);

        DB::transaction(function () use ($professional, $data, $request) {
            $professional->update(collect($data)->only(['name', 'email', 'phone', 'professional_type', 'registration_no', 'specialization', 'status'])->all());
            $this->syncLogin($professional, $data, $request->boolean('create_login'));
        });

        return redirect()->route('admin.professionals.index')->with('success', 'Professional updated.');
    }

    public function destroy(Professional $professional): RedirectResponse
    {
        $professional->update(['status' => 'inactive']);
        $professional->user?->update(['status' => 'inactive']);

        return back()->with('success', 'Professional deactivated.');
    }

    /** Create / update the optional panel login for a professional. */
    private function syncLogin(Professional $professional, array $data, bool $wantsLogin): void
    {
        if ($professional->user) {
            $professional->user->update([
                'name' => $data['name'], 'email' => $data['email'], 'mobile' => $data['phone'] ?? null,
                'status' => $data['status'],
            ] + (! empty($data['password']) ? ['password' => $data['password']] : []));
            $professional->user->syncRoles([$data['login_role']]);

            return;
        }

        if ($wantsLogin) {
            $user = User::create([
                'name' => $data['name'], 'email' => $data['email'], 'mobile' => $data['phone'] ?? null,
                'user_type' => User::TYPE_PROFESSIONAL, 'status' => $data['status'],
                'password' => $data['password'], 'email_verified_at' => now(),
            ]);
            $user->syncRoles([$data['login_role']]);
            $professional->update(['user_id' => $user->id]);
        }
    }

    private function validated(Request $request, Professional $professional): array
    {
        $request->merge(['phone' => preg_replace('/\D/', '', (string) $request->input('phone')) ?: null]);
        $hasLogin = (bool) $professional->user_id;
        $wantsLogin = $hasLogin || $request->boolean('create_login');

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => [$wantsLogin ? 'required' : 'nullable', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($professional->user_id)],
            'phone' => ['nullable', 'regex:/^[6-9][0-9]{9}$/', Rule::unique('users', 'mobile')->ignore($professional->user_id)],
            'professional_type' => ['required', Rule::in(array_keys(Professional::TYPES))],
            'registration_no' => ['nullable', 'string', 'max:100'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'login_role' => [$wantsLogin ? 'required' : 'nullable', Rule::in(array_keys(self::LOGIN_ROLES))],
            'password' => [$wantsLogin && ! $hasLogin ? 'required' : 'nullable', 'confirmed', Password::defaults()],
        ]);
    }
}
