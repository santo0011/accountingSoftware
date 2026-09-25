@extends('layouts.admin')
@section('title', (string) ($role->exists ? 'Edit Role' : 'New Role'))

@php($allActions = collect($modules)->flatMap(fn ($m) => $m['actions'])->unique()->values())

@section('content')
<x-page-header :title="$role->exists ? 'Permissions: '.config('rbac.roles.'.$role->name.'.label', $role->name) : 'New role'" :back="route('admin.roles.index')" />

<form method="POST" action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}" novalidate>
    @csrf
    @if ($role->exists) @method('PUT') @endif

    <div class="card mb-3" style="max-width: 480px"><div class="card-body">
        <x-form.input name="name" label="Role key" :value="$role->name" required help="Lowercase, e.g. senior-accountant." col="mb-0" />
    </div></div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table perm-table table-hover">
                <thead>
                    <tr><th>Module</th>@foreach ($allActions as $action)<th>{{ ucwords(str_replace('_', ' ', $action)) }}</th>@endforeach<th>All</th></tr>
                </thead>
                <tbody>
                @foreach ($modules as $module => $def)
                    <tr>
                        <td class="fw-semibold text-navy">{{ $def['label'] }}</td>
                        @foreach ($allActions as $action)
                            <td>
                                @if (in_array($action, $def['actions'], true))
                                    @php($perm = $module.'.'.$action)
                                    <input type="checkbox" class="form-check-input" name="permissions[]" value="{{ $perm }}" data-module="{{ $module }}" @checked(in_array($perm, old('permissions', $granted), true)) aria-label="{{ $perm }}">
                                @endif
                            </td>
                        @endforeach
                        <td><input type="checkbox" class="form-check-input" data-toggle-module="{{ $module }}" aria-label="All {{ $def['label'] }}"></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="small text-muted mt-2">“View all” lets the role see every record; without it, users only see records assigned to them.</div>
    <div class="text-end mt-3"><button class="btn btn-primary">Save Role</button></div>
</form>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-toggle-module]').forEach((all) => {
        const boxes = document.querySelectorAll('[data-module="' + all.dataset.toggleModule + '"]');
        all.checked = [...boxes].every((b) => b.checked);
        all.addEventListener('change', () => boxes.forEach((b) => b.checked = all.checked));
    });
</script>
@endpush
