<!-- Responsive Dashboard Table -->
<div class="table-responsive">
    <table class="table table-responsive-sm">
        <thead>
            <tr class="border-bottom">
                <th>{{ ___('label.log_name') }}</th>
                <th>{{ ___('label.new') }}</th>
                <th>{{ ___('label.old') }}</th>
            </tr>
        </thead>
        <tbody>

            @if (!isset($logDetails->properties['attributes']) && $logDetails->properties['old'] )
            @foreach ($logDetails->properties['old'] as $key => $value )
            <tr>
                <td>{{ ___("label.{$key}") }}</td>
                <td></td>
                <td>{!! $value !!}</td>
            </tr>
            @endforeach
            @else

            @foreach ( $logDetails->properties['attributes'] ?? [] as $key=>$value )
            <tr>
                <td>{{ ___("label.{$key}") }}</td>
                <td>{!! $value !!}</td>
                <td>{!! data_get($logDetails->properties['old'] ?? [], $key) !!}</td>
            </tr>
            @endforeach
            @endif


        </tbody>
    </table>
</div>
<!-- Responsive Dashboard Table -->
