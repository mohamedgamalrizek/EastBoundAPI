@extends('backend.partials.master')
@section('title') Job Applications @endsection
@section('maincontent')
<x-page title="Job Applications" :breadcrumb="['CRM','Job Applications']">

    <x-data-table :headers="['Applicant','Contact','Position','Resume','Status','Submitted','Action']">
        @forelse($applications as $application)
            <tr id="row_{{ $application->id }}">
                <td><b>{{ $application->name }}</b></td>
                <td>
                    @if($application->email)
                        <div><a href="mailto:{{ $application->email }}">{{ $application->email }}</a></div>
                    @endif
                    @if($application->phone)
                        <div><a href="tel:{{ preg_replace('/[^\d+]/', '', $application->phone) }}">{{ $application->phone }}</a></div>
                    @endif
                    @if(!$application->email && !$application->phone)
                        <span class="text-muted">No contact</span>
                    @endif
                </td>
                <td>{{ $application->jobOpening?->title ?: 'Deleted role' }}</td>
                <td>
                    @if($application->resume_path)
                        <a href="{{ route('crm.job-applications.resume', $application->id) }}">Download</a>
                    @else
                        <span class="text-muted">No file</span>
                    @endif
                </td>
                <td>{!! $application->statusBadge() !!}</td>
                <td>{{ $application->created_at?->format('d M Y, h:i A') }}</td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        <a href="{{ route('crm.job-applications.show', $application->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.view') }}"><i class="fa fa-eye"></i></a>
                        @if(hasPermission('crm_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('crm.job-applications.delete', $application->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $application->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
        @endforelse
    </x-data-table>

    @if($applications->count())
        <x-paginate-show :items="$applications" />
    @endif

</x-page>
@endsection
