@extends('layouts.admin')
@section('title', (string) ('Customer Reviews'))

@section('content')
<x-page-header title="Website content" subtitle="Customer reviews shown on the homepage and About page.">
    <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New Review</a>
</x-page-header>
@include('admin.cms.nav')

<div class="row g-3">
    @forelse ($testimonials as $t)
        <div class="col-md-6 col-xl-4">
            <div class="card h-100"><div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-warning">{!! str_repeat('<i class="bi bi-star-fill"></i>', $t->rating) !!}</span>
                    <span class="badge badge-soft-{{ $t->status ? 'success' : 'secondary' }}">{{ $t->status ? 'Visible' : 'Hidden' }}</span>
                </div>
                <p class="small flex-grow-1">“{{ $t->message }}”</p>
                <div class="fw-semibold text-navy small">{{ $t->name }}</div>
                <div class="small text-muted mb-3">{{ collect([$t->designation, $t->company, $t->city])->filter()->implode(', ') }}</div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.testimonials.edit', $t) }}" class="btn btn-sm btn-light">Edit</a>
                    <form method="POST" action="{{ route('admin.testimonials.destroy', $t) }}" data-confirm="Delete?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger">Delete</button></form>
                </div>
            </div></div>
        </div>
    @empty
        <div class="col-12"><div class="card"><x-empty-state title="No reviews yet" /></div></div>
    @endforelse
</div>
@endsection
