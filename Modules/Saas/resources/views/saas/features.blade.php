@extends('backend.partials.master')
@section('title') Feature Management @endsection
@section('maincontent')
<x-page title="Feature Management" :breadcrumb="['Super Admin','Features']">

    <p class="text-muted">A live matrix of every feature defined across plans (edit features on each <a href="{{ route('saas.plans') }}">Plan</a>).</p>

    <div class="card"><div class="card-body"><div class="table-responsive">
        <table class="table table-responsive-sm table-striped">
            <thead class="bg">
                <tr>
                    <th>Feature</th>
                    @foreach($plans as $plan)<th class="text-center">{{ $plan->name }}</th>@endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($features as $f)
                    <tr>
                        <td>{{ $f->feature }}</td>
                        @foreach($plans as $plan)
                            <td class="text-center">
                                @if(in_array($plan->name, $f->plans)) <span class="text-success"><i class="fa fa-check"></i></span>
                                @else <span class="text-muted">—</span> @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ $plans->count() + 1 }}" class="text-center text-muted">No features defined on any plan yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div></div></div>

</x-page>
@endsection
