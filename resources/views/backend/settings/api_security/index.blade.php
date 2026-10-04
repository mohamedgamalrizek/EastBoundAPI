@extends('backend.partials.master')

@section('title', ___('menus.api_security'))

@section('maincontent')

<div class="container-fluid  dashboard-content">
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}" class="breadcrumb-link">{{ ___('menus.dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="#" class="breadcrumb-link active">{{ ___('menus.settings') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{route('settings.api.security.index')}}" class="breadcrumb-link active">{{ ___('menus.api_security') }}</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="tv-card">
                <div class="card-header">
                    <h4 class="title-site"> {{ ___('menus.api_security') }} </h4>
                </div>
                <div class="tv-card-body">

                    <div class="alert {{ $configured ? 'alert-success' : 'alert-info' }}">
                        @if($configured)
                            {{ ___('label.api_security_on') }}
                        @else
                            {{ ___('label.api_security_off') }}
                        @endif
                    </div>

                    <form action="{{route('settings.update')}}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label class="label-style-1" for="app_api_key">{{ ___('label.app_api_key') }}</label>
                            <div class="d-flex flex-wrap" style="gap:8px;">
                                <input id="app_api_key" type="text" name="app_api_key" class="form-control input-style-1" style="flex:1 1 320px;"
                                       value="{{ old('app_api_key', settings('app_api_key')) }}"
                                       placeholder="{{ ___('placeholder.blank_disables_the_check') }}"
                                       autocomplete="off" @disabled(!hasPermission('general_settings_update')) />
                                <button type="button" class="j-td-btn" id="copy_app_api_key">{{ ___('label.copy') }}</button>
                                @if(hasPermission('general_settings_update'))
                                <button type="button" class="j-td-btn" id="generate_app_api_key"
                                        data-has-key="{{ settings('app_api_key') ? '1' : '0' }}">{{ ___('label.generate') }}</button>
                                @endif
                            </div>
                            @error('app_api_key') <small class="text-danger mt-2">{{ $message }}</small> @enderror

                            <small class="text-muted d-block mt-2">{{ ___('label.app_api_key_help') }}</small>

                            @if(settings('app_api_key_generated_at'))
                            <small class="text-muted d-block mt-1">
                                {{ ___('label.last_generated') }}: {{ settings('app_api_key_generated_at') }}
                            </small>
                            @endif
                        </div>

                        <div class="alert alert-warning mt-3 mb-0">
                            {{ ___('label.app_api_key_warning') }}
                            <div class="mt-2">
                                <code>flutter build apk --release --dart-define=APP_API_KEY=&lt;your key&gt;</code>
                            </div>
                        </div>

                        @if(hasPermission('general_settings_update'))
                        <div class="drp-btns mt-3">
                            <button type="submit" class="j-td-btn">{{ ___('label.save_change') }}</button>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@pushOnce('scripts')
<script>
(function () {
    var field    = document.getElementById('app_api_key');
    var generate = document.getElementById('generate_app_api_key');
    var copy     = document.getElementById('copy_app_api_key');

    if (!field) { return; }

    // 40 hex characters from the browser's CSPRNG. Generated client-side, so
    // the value never leaves the page until the admin deliberately saves it.
    function newKey() {
        var bytes = new Uint8Array(20);
        window.crypto.getRandomValues(bytes);
        return Array.from(bytes, function (b) { return b.toString(16).padStart(2, '0'); }).join('');
    }

    function fill() {
        field.value = newKey();
        field.focus();
        field.select();
    }

    if (generate) {
        generate.addEventListener('click', function () {
            // Replacing a live key is destructive: every app build carrying the
            // old one starts failing the moment this is saved. Confirm, and say
            // which of the two situations the admin is actually in.
            var hasKey = generate.getAttribute('data-has-key') === '1' || field.value.trim() !== '';

            Swal.fire({
                title: hasKey
                    ? {!! json_encode(___('label.replace_api_key_title')) !!}
                    : {!! json_encode(___('label.generate_api_key_title')) !!},
                text: hasKey
                    ? {!! json_encode(___('alert.replace_api_key_warning')) !!}
                    : {!! json_encode(___('alert.generate_api_key_note')) !!},
                icon: hasKey ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonText: {!! json_encode(___('label.generate')) !!},
                cancelButtonText: {!! json_encode(___('label.cancel')) !!},
            }).then(function (result) {
                if (result.isConfirmed) { fill(); }
            });
        });
    }

    if (copy) {
        copy.addEventListener('click', function () {
            var value = field.value.trim();

            if (value === '') {
                Swal.fire({
                    text: {!! json_encode(___('alert.nothing_to_copy')) !!},
                    icon: 'info',
                    timer: 1600,
                    showConfirmButton: false,
                });
                return;
            }

            function done() {
                Swal.fire({
                    text: {!! json_encode(___('alert.copied_to_clipboard')) !!},
                    icon: 'success',
                    timer: 1400,
                    showConfirmButton: false,
                });
            }

            // navigator.clipboard needs a secure context; plenty of buyers run
            // the panel over plain http on a LAN, so fall back to execCommand.
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(value).then(done, legacyCopy);
            } else {
                legacyCopy();
            }

            function legacyCopy() {
                field.removeAttribute('disabled');
                field.select();
                field.setSelectionRange(0, 99999);
                try { document.execCommand('copy'); done(); } catch (e) { /* nothing we can do */ }
            }
        });
    }
})();
</script>
@endPushOnce
