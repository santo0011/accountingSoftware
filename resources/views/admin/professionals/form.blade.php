@extends('layouts.admin')
@section('title', (string) ($professional->exists ? 'Edit Professional' : 'Add Professional'))

@section('content')
<x-page-header :title="$professional->exists ? 'Edit '.$professional->name : 'Add professional'" :subtitle="$professional->exists ? 'Update their details or panel access.' : 'Add a CA, CS or lawyer who works on applications.'" :back="route('admin.professionals.index')" />

<form method="POST" action="{{ $professional->exists ? route('admin.professionals.update', $professional) : route('admin.professionals.store') }}" novalidate>
    @csrf
    @if ($professional->exists) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <x-form.section title="Professional details" text="Who they are and how to reach them." icon="bi-mortarboard">
                <x-form.input name="name" label="Full name" :value="$professional->name" required placeholder="e.g. CA Vikram Joshi" col="col-md-6 mb-3" />
                <x-form.select name="professional_type" label="Professional type" :options="\App\Models\Professional::TYPES" :value="$professional->professional_type" placeholder="Select" required col="col-md-6 mb-3" />
                <x-form.input name="email" type="email" label="Email" :value="$professional->email" placeholder="name@example.com" col="col-md-6 mb-3" />
                <x-form.input name="phone" label="Phone" :value="$professional->phone" prepend="+91" maxlength="10" placeholder="10-digit mobile" col="col-md-6 mb-3" />
                <x-form.input name="registration_no" label="Registration / licence no." :value="$professional->registration_no" placeholder="e.g. ICAI M.No., Bar Council no." col="col-md-6 mb-3" />
                <x-form.input name="specialization" label="Specialization" :value="$professional->specialization" placeholder="e.g. GST, Income Tax, Audit" col="col-md-6 mb-3" />
                <x-form.select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive']" :value="$professional->status" col="col-md-6" />
            </x-form.section>

            <x-form.section title="Admin-panel access" text="Optional — lets them sign in and work on the applications assigned to them." icon="bi-key" tone="amber">
                @if ($professional->user)
                    <div class="col-12 mb-3"><span class="status-dot on">Has a login</span> <span class="text-muted small ms-1">{{ $professional->user->email }}</span></div>
                    <input type="hidden" name="create_login" value="1">
                @else
                    <div class="col-12 mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="create_login" value="1" id="create_login" @checked(old('create_login'))
                                onchange="document.getElementById('loginBox').classList.toggle('d-none', !this.checked)">
                            <label class="form-check-label" for="create_login">Give this professional a login to the admin panel</label>
                        </div>
                        <div class="form-text">They'll only see applications assigned to them.</div>
                    </div>
                @endif
                <div id="loginBox" class="col-12 {{ $professional->user || old('create_login') ? '' : 'd-none' }}">
                    <div class="row">
                        <x-form.select name="login_role" label="Access role" :options="$loginRoles" :value="$professional->user?->getRoleNames()->first() ?? 'tax-professional'" col="col-md-4 mb-md-0 mb-3" />
                        <x-form.input name="password" type="password" label="{{ $professional->user ? 'New password (optional)' : 'Password' }}" autocomplete="new-password" col="col-md-4 mb-md-0 mb-3" />
                        <x-form.input name="password_confirmation" type="password" label="Confirm password" autocomplete="new-password" col="col-md-4" />
                    </div>
                </div>
            </x-form.section>
        </div>

        <div class="col-xl-4">
            <x-form.tips title="Good to know">
                <li>Professionals can be <strong>assigned to applications</strong> that need a CA, CS or lawyer.</li>
                <li>With a login they see <strong>only their assigned applications</strong> — nothing else.</li>
                <li>Deactivating a professional keeps their history on past applications.</li>
            </x-form.tips>
        </div>
    </div>

    <x-form.actions :cancel="route('admin.professionals.index')" :label="$professional->exists ? 'Save changes' : 'Add professional'" />
</form>
@endsection
