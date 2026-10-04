@props(['title' => '', 'breadcrumb' => [], 'action' => null])

{{-- Reusable admin page wrapper: breadcrumb header + content slot.
     Usage: <x-page title="Leads" :breadcrumb="['CRM','Leads']"> ... </x-page> --}}
<div class="container-fluid dashboard-content">
    <div class="row">
        <div class="col-12">
            <div class="page-header d-flex justify-content-between align-items-center flex-wrap">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a></li>
                            @foreach($breadcrumb as $crumb)
                            <li class="breadcrumb-item"><a href="" class="breadcrumb-link active">{{ $crumb }}</a></li>
                            @endforeach
                        </ol>
                    </nav>
                </div>
                <x-how-it-works />
                @isset($action)
                <div>{{ $action }}</div>
                @endisset
            </div>
        </div>
    </div>

    {{ $slot }}
</div>
