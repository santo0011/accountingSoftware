@extends('layouts.admin')
@section('title', (string) ('Website Content'))

@section('content')
<x-page-header title="Website content" subtitle="About, policy and custom pages.">
    <a href="{{ route('admin.pages.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New Page</a>
</x-page-header>
@include('admin.cms.nav')

<div class="table-card">
    <table class="table table-hover table-stack">
        <thead><tr><th>Title</th><th>URL</th><th>Updated</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach ($pages as $page)
            <tr>
                <td data-label="Title" class="fw-semibold text-navy">{{ $page->title }} @if (in_array($page->slug, $system, true))<span class="badge badge-soft-secondary">System</span>@endif</td>
                <td data-label="URL"><code>/{{ $page->slug }}</code></td>
                <td data-label="Updated">{{ $page->updated_at->format('d M Y') }}</td>
                <td data-label="Status"><span class="badge badge-soft-{{ $page->status ? 'success' : 'secondary' }}">{{ $page->status ? 'Published' : 'Draft' }}</span></td>
                <td class="td-actions text-end text-nowrap">
                    <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                    @unless (in_array($page->slug, $system, true))
                        <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="d-inline" data-confirm="Delete this page?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                    @endunless
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
