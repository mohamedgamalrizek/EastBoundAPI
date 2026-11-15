@props(['title' => '', 'breadcrumb' => [], 'action' => null])

{{-- Reusable admin page wrapper: breadcrumb header + content slot.
     Usage: <x-page title="Leads" :breadcrumb="['CRM','Leads']"> ... </x-page> --}}
<div class="container-fluid dashboard-content">
    <div class="row">
        <div class="col-12">
            <div class="page-header d-flex justify-content-between align-items-center flex-wrap">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <x-breadcrumbs :items="$breadcrumb" :title="$title" />
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
