@props(['items' => [], 'title' => ''])
<ol class="breadcrumb">
    @foreach(\App\Support\Breadcrumbs::build($items, $title) as $crumb)
        <li class="breadcrumb-item{{ $loop->last ? ' active' : '' }}" @if($loop->last) aria-current="page" @endif>
            @if($crumb['url'] && !$loop->last)
                <a href="{{ $crumb['url'] }}" class="breadcrumb-link">{{ $crumb['label'] }}</a>
            @else
                <span>{{ $crumb['label'] }}</span>
            @endif
        </li>
    @endforeach
</ol>
