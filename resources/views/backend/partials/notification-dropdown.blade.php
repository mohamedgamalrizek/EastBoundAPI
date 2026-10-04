@forelse($notifications as $n)
    <li class="media dropdown-item {{ $n->is_read ? '' : 'font-weight-bold' }}" data-id="{{ $n->id }}">
        <span class="{{ $n->is_read ? 'secondary' : 'primary' }}"><i class="ti-bell"></i></span>
        <div class="media-body">
            <a href="#" class="js-mark-read" data-id="{{ $n->id }}">
                <p><strong>{{ $n->title }}</strong> {{ Str::limit($n->body, 60) }}</p>
            </a>
        </div>
        <span class="notify-time">{{ $n->created_at?->diffForHumans() }}</span>
    </li>
@empty
    <li class="media dropdown-item text-center text-muted">No notifications yet.</li>
@endforelse
