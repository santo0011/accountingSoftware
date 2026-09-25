<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComplianceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ComplianceTypeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:compliance.manage')];
    }

    public function index(): View
    {
        $types = ComplianceType::withCount('records')->orderBy('name')->get();

        return view('admin.compliance.types', compact('types'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        ComplianceType::create($data + ['code' => Str::slug($data['name'], '_').'_'.Str::lower(Str::random(4))]);

        return back()->with('success', 'Compliance type added.');
    }

    public function update(Request $request, ComplianceType $complianceType): RedirectResponse
    {
        $complianceType->update($this->validated($request));

        return back()->with('success', 'Compliance type updated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'frequency' => ['required', Rule::in(array_keys(ComplianceType::FREQUENCIES))],
            'due_day' => ['required', 'integer', 'between:1,31'],
            'due_month_offset' => ['required', 'integer', 'between:0,12'],
            'reminder_days_before' => ['required', 'integer', 'between:0,90'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['boolean'],
        ]);
    }
}
