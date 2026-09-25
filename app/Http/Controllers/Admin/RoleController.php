<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller implements HasMiddleware
{
    /** Roles whose permissions cannot be edited or that cannot be deleted. */
    private const LOCKED = ['super-admin', 'customer'];

    public static function middleware(): array
    {
        return [new Middleware('permission:roles.manage')];
    }

    public function index(): View
    {
        $roles = Role::withCount(['users', 'permissions'])->orderBy('id')->get();

        return view('admin.roles.index', ['roles' => $roles, 'locked' => self::LOCKED]);
    }

    public function create(): View
    {
        return view('admin.roles.form', ['role' => new Role, 'modules' => config('rbac.modules'), 'granted' => []]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);
        $this->log($request, "Created role {$role->name}");

        return redirect()->route('admin.roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role): View
    {
        abort_if(in_array($role->name, self::LOCKED, true), 403, 'This role cannot be edited.');

        return view('admin.roles.form', [
            'role' => $role, 'modules' => config('rbac.modules'),
            'granted' => $role->permissions->pluck('name')->all(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_if(in_array($role->name, self::LOCKED, true), 403);

        $data = $this->validated($request, $role);
        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->log($request, "Updated permissions of role {$role->name}");

        return redirect()->route('admin.roles.index')->with('success', 'Role permissions saved.');
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        if (in_array($role->name, array_merge(self::LOCKED, ['admin']), true) || $role->users()->exists()) {
            return back()->with('error', 'This role is in use or protected and cannot be deleted.');
        }

        $role->delete();
        $this->log($request, "Deleted role {$role->name}");

        return back()->with('success', 'Role deleted.');
    }

    private function validated(Request $request, ?Role $role = null): array
    {
        $request->merge(['name' => Str::slug((string) $request->input('name'))]);

        return $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('roles', 'name')->ignore($role?->id), Rule::notIn(self::LOCKED)],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in(Permission::pluck('name')->all())],
        ]);
    }

    private function log(Request $request, string $message): void
    {
        activity('roles')->causedBy($request->user())->log($message);
    }
}
