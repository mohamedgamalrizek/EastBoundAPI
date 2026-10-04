@props([
    'id'      => 'dt_' . uniqid(),
    'headers' => [],
    'pageLength' => 10,
    'order'   => '[]',
    'card'    => true,
])

{{-- Shared DataTable used by every panel. Pass :headers and the <tr> rows as the slot.
     Usage:
       <x-data-table :headers="['Name','Email','Status']">
           @foreach($rows as $r) <tr>...</tr> @endforeach
       </x-data-table>
--}}

@if($card)
<div class="tv-card tv-list-card">
    <div class="tv-card-body">
@endif

<table id="{{ $id }}" class="table table-hover table-striped dt-table tv-table w-100"
        data-dt-page-length="{{ (int) $pageLength }}"
        data-dt-order='{!! str_replace(["'", '"'], '&quot;', $order) !!}'>
    <thead class="bg">
        <tr>
            @foreach($headers as $h)
                <th>{{ $h }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        {{ $slot }}
    </tbody>
</table>

@if($card)
    </div>
</div>
@endif
