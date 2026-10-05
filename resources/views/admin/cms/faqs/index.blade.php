@extends('layouts.admin')
@section('title', (string) ('FAQs'))

@section('content')
<x-page-header title="Website content" subtitle="General FAQs shown on the homepage and FAQ page.">
    <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New FAQ</a>
</x-page-header>
@include('admin.cms.nav')

<div class="table-card">
    <table class="table table-hover table-stack">
        <thead><tr><th class="col-sl">#</th><th>Question</th><th>Group</th><th>Order</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($faqs as $faq)
            <tr>
                <td class="col-sl" data-label="#">{{ $faqs->firstItem() + $loop->index }}</td>
                <td data-label="Question"><span class="fw-semibold text-navy">{{ $faq->question }}</span><br><small class="text-muted">{{ \Illuminate\Support\Str::limit($faq->answer, 100) }}</small></td>
                <td data-label="Group">{{ \App\Models\Faq::GROUPS[$faq->group] ?? $faq->group }}</td>
                <td data-label="Order">{{ $faq->sort_order }}</td>
                <td data-label="Status"><span class="badge badge-soft-{{ $faq->status ? 'success' : 'secondary' }}">{{ $faq->status ? 'Visible' : 'Hidden' }}</span></td>
                <td class="td-actions text-end text-nowrap">
                    <a href="{{ route('admin.faqs.edit', $faq) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}" class="d-inline" data-confirm="Delete?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><x-empty-state title="No FAQs yet" /></td></tr>
        @endforelse
        </tbody>
    </table>
    <x-table-footer :items="$faqs" label="FAQs" />
</div>
@endsection
