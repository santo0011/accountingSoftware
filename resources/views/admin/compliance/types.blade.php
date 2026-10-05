@extends('layouts.admin')
@section('title', (string) ('Compliance Types'))

@section('content')
<x-page-header title="Compliance types" subtitle="Due-date rules used to generate compliance records. Due date = period end month + offset, on the due day." :back="route('admin.compliance.index')" />

<div class="table-card mb-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th class="col-sl">#</th><th>Name</th><th>Frequency</th><th>Due day</th><th>Month offset</th><th>Remind (days)</th><th>Active</th><th>Records</th><th></th></tr></thead>
            <tbody>
            @foreach ($types as $t)
                <tr>
                    <td class="col-sl" data-label="#">{{ $types->firstItem() + $loop->index }}</td>
                    <form method="POST" action="{{ route('admin.compliance-types.update', $t) }}">
                        @csrf @method('PUT')
                        <td><input type="text" name="name" value="{{ $t->name }}" class="form-control form-control-sm" required><input type="hidden" name="description" value="{{ $t->description }}"></td>
                        <td><select name="frequency" class="form-select form-select-sm">@foreach (\App\Models\ComplianceType::FREQUENCIES as $v => $l)<option value="{{ $v }}" @selected($t->frequency === $v)>{{ $l }}</option>@endforeach</select></td>
                        <td><input type="number" name="due_day" value="{{ $t->due_day }}" min="1" max="31" class="form-control form-control-sm" style="width:80px"></td>
                        <td><input type="number" name="due_month_offset" value="{{ $t->due_month_offset }}" min="0" max="12" class="form-control form-control-sm" style="width:80px"></td>
                        <td><input type="number" name="reminder_days_before" value="{{ $t->reminder_days_before }}" min="0" max="90" class="form-control form-control-sm" style="width:80px"></td>
                        <td><input type="hidden" name="status" value="0"><input type="checkbox" name="status" value="1" class="form-check-input" @checked($t->status)></td>
                        <td>{{ $t->records_count }}</td>
                        <td><button class="btn btn-sm btn-light" data-no-lock>Save</button></td>
                    </form>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <x-table-footer :items="$types" label="types" />
</div>

<form method="POST" action="{{ route('admin.compliance-types.store') }}" class="card" style="max-width: 900px">
    @csrf
    <div class="card-header">Add compliance type</div>
    <div class="card-body row g-2">
        <div class="col-md-4"><input type="text" name="name" class="form-control" placeholder="Name" required></div>
        <div class="col-md-2"><select name="frequency" class="form-select">@foreach (\App\Models\ComplianceType::FREQUENCIES as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></div>
        <div class="col-md-2"><input type="number" name="due_day" class="form-control" placeholder="Due day" value="20" min="1" max="31"></div>
        <div class="col-md-2"><input type="number" name="due_month_offset" class="form-control" placeholder="Offset" value="1" min="0" max="12"></div>
        <div class="col-md-2"><input type="number" name="reminder_days_before" class="form-control" placeholder="Remind" value="7" min="0" max="90"></div>
        <input type="hidden" name="status" value="1">
        <div class="col-12 text-end"><button class="btn btn-primary">Add Type</button></div>
    </div>
</form>
@endsection
