@foreach($items as $item)
    <li @class(['mm-active' => $item['active']])>
        @if(isset($item['children']))
            <a class="has-arrow" href="javascript:void(0)" aria-expanded="{{ $item['active'] ? 'true' : 'false' }}">
                <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
                <span class="nav-text">{{ $item['label'] }}</span>
            </a>
            <ul aria-hidden="{{ $item['active'] ? 'false' : 'true' }}">
                @include('backend.partials.sidebar-items', ['items' => $item['children']])
            </ul>
        @elseif($item['pending'])
            <span class="d-block text-muted px-4 py-2" aria-disabled="true" title="Planned for the next phase">
                <span class="nav-text">{{ $item['label'] }}</span>
                <small class="badge badge-light">Next phase</small>
            </span>
        @else
            <a href="{{ route($item['route'], $item['query'] ?? []) }}" @if($item['current']) aria-current="page" @endif>
                <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
                <span class="nav-text">{{ $item['label'] }}</span>
            </a>
        @endif
    </li>
@endforeach
