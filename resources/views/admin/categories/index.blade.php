@extends('layouts.admin')
@section('title', (string) ('Service Categories'))

@section('content')
<x-page-header title="Service categories" subtitle="Categories drive the website menu, category pages and homepage sections.">
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Category</a>
</x-page-header>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover table-stack">
            <thead><tr><th class="col-sl">#</th><th>Category</th><th>Slug</th><th>Services</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @foreach ($categories as $cat)
                <tr>
                    <td class="col-sl" data-label="#">{{ $categories->firstItem() + $loop->index }}</td>
                    <td data-label="Category"><span class="d-inline-flex align-items-center gap-2"><span class="icon-bubble sm"><i class="bi {{ $cat->icon }}"></i></span><span><span class="fw-semibold text-navy d-block">{{ $cat->name }}</span><small class="text-muted">{{ \Illuminate\Support\Str::limit($cat->tagline, 60) }}</small></span></span></td>
                    <td data-label="Slug"><code>{{ $cat->slug }}</code></td>
                    <td data-label="Services"><a href="{{ route('admin.services.index', ['category' => $cat->id]) }}">{{ $cat->services_count }}</a></td>
                    <td data-label="Status"><span class="badge badge-soft-{{ $cat->status ? 'success' : 'secondary' }}">{{ $cat->status ? 'Active' : 'Hidden' }}</span></td>
                    <td class="td-actions text-end text-nowrap">
                        <a href="{{ route('site.categories.show', $cat->slug) }}" target="_blank" class="btn btn-sm btn-light" title="View on site"><i class="bi bi-box-arrow-up-right"></i></a>
                        <a href="{{ route('admin.categories.edit', $cat) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" class="d-inline" data-confirm="Delete this category?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <x-table-footer :items="$categories" label="categories" />
</div>
@endsection
