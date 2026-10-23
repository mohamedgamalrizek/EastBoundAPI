@extends('backend.partials.master')
@section('title') {{ $definition['title'] }} @endsection
@section('maincontent')
<x-page :title="($record->exists ? 'Edit ' : 'Create ').$definition['title']" :breadcrumb="['DMC', $definition['title']]">
    @if($errors->any())<div class="alert alert-danger" role="alert"><ul>@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif
    <div class="tv-card"><div class="tv-card-body">
        <form method="POST" action="{{ $record->exists ? route('dmc.'.$module.'.update', $record->id) : route('dmc.'.$module.'.store') }}">
            @csrf
            @if($record->exists) @method('PUT') @endif
            <div class="form-row">
                @foreach($definition['fields'] as $name => $field)
                    @continue(isset($field['permission']) && !hasPermission($field['permission']))
                    @php
                        $value = old($name, $record->{$name} ?? ($field['type'] === 'hidden' ? $paymentKey : request($name, '')));
                        if ($value instanceof \DateTimeInterface) $value = $value->format('Y-m-d');
                    @endphp
                    @if($field['type'] === 'hidden')
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @else
                        <div class="form-group col-md-6">
                            <label for="dmc_{{ $name }}">{{ $field['label'] }} @if(in_array('required', $field['rules'], true))<span class="text-danger">*</span>@endif</label>
                            @if(in_array($field['type'], ['select', 'relation']))
                                <select id="dmc_{{ $name }}" name="{{ $name }}" class="form-control input-style-1 select2">
                                    <option value="">Select...</option>
                                    @foreach($options[$name] as $key => $label)<option value="{{ $key }}" @selected((string) $value === (string) $key)>{{ $label }}</option>@endforeach
                                </select>
                            @elseif($field['type'] === 'textarea')
                                <textarea id="dmc_{{ $name }}" name="{{ $name }}" class="form-control input-style-1" rows="4" maxlength="10000">{{ $value }}</textarea>
                            @else
                                <input id="dmc_{{ $name }}" name="{{ $name }}" type="{{ $field['type'] }}" value="{{ $value }}" class="form-control input-style-1" @if($field['type'] === 'number') step="{{ in_array('integer', $field['rules'], true) ? '1' : '0.01' }}" @endif>
                            @endif
                            @error($name)<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    @endif
                @endforeach
            </div>
            @if($module === 'quotations') @include('backend.dmc.quotation-items') @endif
            @if($module === 'approval-policies')
                <p class="text-muted">All three limits must be configured to permit Team Leader approval. Otherwise every quotation requires General Manager approval. A different employee must approve the quotation.</p>
            @endif
            <button class="btn btn-primary" type="submit">Save</button>
            <a class="btn btn-light" href="{{ route('dmc.'.$module.'.index') }}">Cancel</a>
        </form>
    </div></div>
</x-page>
@endsection
