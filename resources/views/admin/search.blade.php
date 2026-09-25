@extends('layouts.admin')
@section('title', (string) ('Search'))

@section('content')
<x-page-header title="Search results" :subtitle="'For “'.$q.'”'" />

@php($total = $applications->count() + $customers->count() + $invoices->count() + $leads->count())
@if ($total === 0)
    <div class="card"><x-empty-state icon="bi-search" title="No results found" text="Try an application number, customer name, email, mobile or invoice number." /></div>
@endif

<div class="row g-4">
    @if ($applications->isNotEmpty())
        <div class="col-lg-6"><div class="card">
            <div class="card-header">Applications</div>
            @foreach ($applications as $a)
                <a href="{{ route('admin.applications.show', $a) }}" class="list-row">
                    <div class="flex-grow-1 min-w-0"><div class="title">{{ $a->application_no }} · {{ $a->service->name }}</div><div class="meta">{{ $a->customer->user->name }}</div></div>
                    <x-status-badge :status="$a->status" />
                </a>
            @endforeach
        </div></div>
    @endif
    @if ($customers->isNotEmpty())
        <div class="col-lg-6"><div class="card">
            <div class="card-header">Customers</div>
            @foreach ($customers as $c)
                <a href="{{ route('admin.customers.show', $c) }}" class="list-row">
                    <span class="avatar sm">{{ $c->user->initials() }}</span>
                    <div class="flex-grow-1 min-w-0"><div class="title">{{ $c->user->name }}</div><div class="meta">{{ $c->customer_code }} · {{ $c->user->email }} · {{ $c->user->mobile }}</div></div>
                </a>
            @endforeach
        </div></div>
    @endif
    @if ($invoices->isNotEmpty())
        <div class="col-lg-6"><div class="card">
            <div class="card-header">Invoices</div>
            @foreach ($invoices as $i)
                <a href="{{ route('admin.invoices.show', $i) }}" class="list-row">
                    <div class="flex-grow-1"><div class="title">{{ $i->invoice_no }}</div><div class="meta">{{ $i->billing_name }} · {{ $i->invoice_date->format('d M Y') }}</div></div>
                    <strong>{{ money($i->total) }}</strong>
                </a>
            @endforeach
        </div></div>
    @endif
    @if ($leads->isNotEmpty())
        <div class="col-lg-6"><div class="card">
            <div class="card-header">Leads</div>
            @foreach ($leads as $l)
                <a href="{{ route('admin.leads.show', $l) }}" class="list-row">
                    <div class="flex-grow-1 min-w-0"><div class="title">{{ $l->name }}</div><div class="meta">{{ $l->company }} · {{ $l->phone }}</div></div>
                    <x-status-badge :status="$l->status" />
                </a>
            @endforeach
        </div></div>
    @endif
</div>
@endsection
