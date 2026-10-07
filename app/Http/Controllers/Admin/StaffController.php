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
            ->when(in_array($request->role, self::STAFF_ROLES, true), fn ($q) => $q->role($request->role))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderBy('name')->paginate(per_page(20))->withQueryString();

        return view('admin.staff.index', ['staff' => $staff, 'roles' => $this->roles(), 'filterRoles' => $this->assignableRoles()]);
    }

    public function create(): View
    {
        return view('admin.staff.form', ['user' => new User(['status' => 'active']), 'roles' => $this->assignableRoles()]);
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

        return $this->toList('admin.staff.index')->with('success', 'Staff member added.');
    }

    public function edit(User $user): View
    {
        $this->guardTarget($user);
        $user->load('staffProfile', 'roles');

        return view('admin.staff.form', ['user' => $user, 'roles' => $this->assignableRoles($user)]);
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

        return $this->toList('admin.staff.index')->with('success', 'Staff member updated.');
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

    /** Re-enable a deactivated staff account so they can sign in again. */
    public function activate(Request $request, User $user): RedirectResponse
    {
        $this->guardTarget($user);

        $user->update(['status' => 'active']);
        activity('staff')->performedOn($user)->causedBy($request->user())->log("Activated staff {$user->email}");

        return back()->with('success', "{$user->name} is active again and can sign in.");
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

        $roles = collect($this->assignableRoles($user))->keys();

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

    /** Roles that can be given from the staff form */
    private const STAFF_ROLES = ['staff', 'accountant'];

    /**
     * Roles offered on the staff form: Staff and Accountant. When editing someone who already has
     * another role (e.g. a super admin), that role is offered too so saving does not change it —
     * a super admin role only for a super admin editor.
     *
     * @return array<string, string>
     */
    private function assignableRoles(?User $user = null): array
    {
        $super = config('rbac.super_admin_role');
        $names = self::STAFF_ROLES;
        $current = $user?->exists ? $user->getRoleNames()->first() : null;
        if ($current && ! in_array($current, $names, true) && ($current !== $super || auth()->user()->hasRole($super))) {
            array_unshift($names, $current);
        }

        return collect($names)->mapWithKeys(fn ($r) => [$r => config("rbac.roles.$r.label", ucwords(str_replace('-', ' ', $r)))])->all();
    }
}
