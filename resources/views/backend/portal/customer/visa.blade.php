@extends('backend.partials.master')
@section('title') {{ ___('label.my_visa_applications') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.my_visa_applications') }}" :breadcrumb="[___('permissions.customer_portal'), ___('label.visa')]">

    <x-data-table :headers="[___('label.application'), ___('label.country'), ___('label.type'), ___('label.submitted'), ___('label.status'), ___('label.documents')]">
        @foreach($applications as $a)
            @php $c = $a->status === 'Approved' ? 'success' : ($a->status === 'Rejected' ? 'danger' : 'warning'); @endphp
            <tr>
                <td><b>{{ $a->application_no }}</b></td>
                <td>{{ $a->country }}</td>
                <td>{{ $a->visa_type }}</td>
                <td>{{ $a->applied_date?->format('d M Y') }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $a->status }}</span></td>
                <td>
                    {{-- Each link goes through the auth'd, ownership-checked download
                         route — the file itself sits on the private disk with no
                         public URL, so this is the only way to fetch it. --}}
                    @forelse($a->documents as $document)
                        <a href="{{ route('cust.visa.document.download', $document->id) }}" class="d-block">
                            <i class="fa fa-download mr-1"></i>{{ $document->document_type }}
                        </a>
                    @empty
                        <span class="text-muted">{{ ___('label.no_documents_yet') }}</span>
                    @endforelse
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
