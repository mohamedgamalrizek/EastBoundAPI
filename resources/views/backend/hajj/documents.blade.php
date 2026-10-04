@extends('backend.partials.master')
@section('title') Document Verification @endsection
@section('maincontent')
<x-page title="Document Verification" :breadcrumb="['Hajj & Umrah','Documents']">

    {{-- The verification work happens here, so the status is set here too —
         not only inside the full pilgrim edit form. --}}
    <x-data-table :headers="['ID','Pilgrim','Passport','Package','Document Status','Action']">
        @forelse($pilgrims as $p)
            @php
                $c = match ($p->document_status) {
                    'Verified'  => 'success',
                    'Submitted' => 'info',
                    default     => 'warning',
                };
            @endphp
            <tr>
                <td><b>{{ $p->pilgrim_no }}</b></td>
                <td>{{ $p->name }}</td>
                <td>{{ $p->passport_no }}</td>
                <td>{{ $p->package_title }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $p->document_status }}</span></td>
                <td>
                    @if(hasPermission('hajj_update'))
                        <form method="POST" action="{{ route('hajj.documents.status', $p->id) }}"
                              class="d-flex align-items-center tv-choice-gap">
                            @csrf
                            <select name="document_status" class="form-control input-style-1 tv-max-w-150">
                                @foreach($documentStatuses as $ds)
                                    <option value="{{ $ds }}" @selected($p->document_status === $ds)>{{ $ds }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
                        </form>
                    @else
                        —
                    @endif
                </td>
            </tr>
        @empty
            <x-nodata-found :colspan="6" />
        @endforelse
    </x-data-table>

</x-page>
@endsection
