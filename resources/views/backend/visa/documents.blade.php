@extends('backend.partials.master')
@section('title') Visa Documents @endsection

@section('maincontent')
<x-page title="Visa Documents" :breadcrumb="['Visa', 'Documents']">
    @if(hasPermission('visa_update'))
    <x-slot:action>
        <a href="{{ route('visa.documents.create') }}" class="j-td-btn {{ $applications->isEmpty() ? 'disabled' : '' }}" @if($applications->isEmpty()) aria-disabled="true" onclick="return false;" @endif>
            <i class="fa fa-upload mr-1"></i> <span>Upload Document</span>
        </a>
    </x-slot:action>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($applications->isEmpty())
                    <div class="alert alert-warning">
                        Create a visa application before uploading documents.
                    </div>
                    @endif

                    <div class="table-responsive visa-crud-table-wrap">
                        <table class="table table-responsive-sm">
                            <thead class="bg">
                                <tr>
                                    <th>Application</th>
                                    <th>Applicant</th>
                                    <th>Document</th>
                                    <th>File</th>
                                    <th>Download</th>
                                    <th>Uploaded</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documents as $document)
                                @php $statusClass = $document->status === 'Verified' ? 'success' : 'warning'; @endphp
                                <tr id="document_row_{{ $document->id }}">
                                    <td>{{ $document->visaApplication?->application_no ?? '—' }}</td>
                                    <td>{{ $document->visaApplication?->applicant_name ?? '—' }}</td>
                                    <td><i class="fa fa-file mr-2 text-muted"></i>{{ $document->document_type }}</td>
                                    <td>{{ $document->file_name }}</td>
                                    <td>
                                        <a href="{{ route('visa.documents.download', $document->id) }}">
                                            <i class="fa fa-download mr-1"></i> Download
                                        </a>
                                    </td>
                                    <td>{{ optional($document->uploaded_at)->format('d M Y, h:i A') }}</td>
                                    <td>
                                        <span class="bullet-badge bullet-badge-{{ $statusClass }}">
                                            {{ $document->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <div class="input-group-prepend be-addon">
                                                <div class="d-flex align-items-center flex-wrap tv-action-gap">
                                                    @if(hasPermission('visa_update'))
                                                    <a href="{{ route('visa.documents.edit', $document->id) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>
                                                    @endif
                                                    @if(hasPermission('visa_delete'))
                                                    <a class="btn btn-sm btn-outline-danger" href="{{ route('visa.documents.delete', $document->id) }}" onclick="tryDelete(event)" data-remove-id="document_row_{{ $document->id }}" data-title="Delete document?" data-text="The uploaded file will also be permanently removed." data-confirm-button-text="Delete" data-cancel-button-text="Cancel"><i class="fa fa-trash"></i></a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <x-nodata-found :colspan="8" />
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection

@push@endpush
