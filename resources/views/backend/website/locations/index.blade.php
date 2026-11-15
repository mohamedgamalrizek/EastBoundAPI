@extends('backend.partials.master')
@section('title', ucfirst($type))
@section('maincontent')
<x-page :title="ucfirst($type)" :breadcrumb="['Website Frontend', ucfirst($type)]">
    <x-slot:action>@if(hasPermission('cms_create'))<a class="btn btn-primary" href="{{ route('website.'.$type.'.create') }}">Add {{ $type === 'cities' ? 'City' : 'Destination' }}</a>@endif</x-slot:action>
    <div class="tv-card"><div class="tv-card-body">
        <p class="text-muted">Only published locations appear on the website. Unpublish a location to hide it without removing its tour links.</p>
        <table class="table"><thead><tr><th>Name</th><th>Slug</th><th>Status</th><th>Order</th><th>Action</th></tr></thead><tbody>
        @forelse($items as $item)
            <tr><td>{{ $item->name }}</td><td>{{ $item->slug }}</td><td>{{ $item->active ? 'Published' : 'Draft' }}</td><td>{{ $item->sort_order }}</td><td>@if(hasPermission('cms_update'))<a class="btn btn-sm btn-outline-primary" href="{{ route('website.'.$type.'.edit', $item->id) }}">Edit</a>@endif</td></tr>
        @empty <x-nodata-found :colspan="5" /> @endforelse
        </tbody></table><x-paginate-show :items="$items" />
    </div></div>
</x-page>
@endsection
