<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class StaffController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:staff.view', only: ['index']),
            new Middleware('permission:staff.manage', except: ['index']),
        ];
    }

    public function index(Request $request): View
    {
        $staff = User::where('user_type', User::TYPE_STAFF)->with('staffProfile', 'roles:id,name')
            ->withCount(['assignedApplications as open_applications_count' => fn ($q) => $q->active()])
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%'.$request->q.'%')->orWhere('email', 'like', '%'.$request->q.'%')))
            ->when($request->filled('role'), fn ($q) => $q->role($request->role))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderBy('name')->paginate(per_page(20))->withQueryString();

        return view('admin.staff.index', ['staff' => $staff, 'roles' => $this->roles()]);
    }

    public function create(): View
    {
        return view('admin.staff.form', ['user' => new User(['status' => 'active']), 'roles' => $this->roles()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, new User);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'], 'email' => $data['email'], 'mobile' => $data['mobile'] ?? null,
                'user_type' => User::TYPE_STAFF, 'status' => $data['status'],
                'password' => $data['password'], 'email_verified_at' => now(),
            ]);
            $user->syncRoles([$data['role']]);
            $user->staffProfile()->create(collect($data)->only(['employee_code', 'department', 'designation', 'joined_on'])->all());
        });

        return redirect()->route('admin.staff.index')->with('success', 'Staff member added.');
    }

    public function edit(User $user): View
    {
        $this->guardTarget($user);
        $user->load('staffProfile', 'roles');

        return view('admin.staff.form', ['user' => $user, 'roles' => $this->roles()]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->guardTarget($user);
        $data = $this->validated($request, $user);

        if ($user->is($request->user()) && ($data['status'] !== 'active' || ! in_array($data['role'], $user->getRoleNames()->all(), true))) {
            return back()->with('error', 'You cannot change your own role or deactivate yourself.');
        }

        DB::transaction(function () use ($user, $data) {
            $user->update(collect($data)->only(['name', 'email', 'mobile', 'status'])->all() + (! empty($data['password']) ? ['password' => $data['password']] : []));
            $user->syncRoles([$data['role']]);
            $user->staffProfile()->updateOrCreate([], collect($data)->only(['employee_code', 'department', 'designation', 'joined_on'])->all());
        });

        activity('staff')->performedOn($user)->causedBy($request->user())->log("Updated staff {$user->email} (role: {$data['role']})");

        return redirect()->route('admin.staff.index')->with('success', 'Staff member updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->guardTarget($user);
        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['status' => 'inactive']);
        activity('staff')->performedOn($user)->causedBy($request->user())->log("Deactivated staff {$user->email}");

        return back()->with('success', 'Staff member deactivated.');
    }

    /** Only staff accounts are managed here, and only a Super Admin may manage another Super Admin. */
    private function guardTarget(User $user): void
    {
        abort_unless($user->user_type === User::TYPE_STAFF, 404);
        abort_if($user->hasRole(config('rbac.super_admin_role')) && ! request()->user()->hasRole(config('rbac.super_admin_role')), 403);
    }

    private function validated(Request $request, User $user): array
    {
        $request->merge(['mobile' => preg_replace('/\D/', '', (string) $request->input('mobile')) ?: null]);

        $roles = collect($this->roles())->keys();
        if (! $request->user()->hasRole(config('rbac.super_admin_role'))) {
            $roles = $roles->reject(fn ($r) => $r === config('rbac.super_admin_role')); // only a super admin can create another
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'mobile' => ['nullable', 'regex:/^[6-9][0-9]{9}$/', Rule::unique('users', 'mobile')->ignore($user->id)],
            'role' => ['required', Rule::in($roles->all())],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'employee_code' => ['nullable', 'string', 'max:30', Rule::unique('staff_profiles', 'employee_code')->ignore($user->staffProfile?->id)],
            'department' => ['nullable', 'string', 'max:100'],
            'designation' => ['nullable', 'string', 'max:100'],
            'joined_on' => ['nullable', 'date'],
            'password' => [$user->exists ? 'nullable' : 'required', 'confirmed', Password::defaults()],
        ]);
    }

    /** @return array<string, string> role name => label (back-office roles only) */
    private function roles(): array
    {
        return Role::where('name', '!=', 'customer')->orderBy('id')->pluck('name')
            ->mapWithKeys(fn ($r) => [$r => config("rbac.roles.$r.label", ucwords(str_replace('-', ' ', $r)))])->all();
    }
}
