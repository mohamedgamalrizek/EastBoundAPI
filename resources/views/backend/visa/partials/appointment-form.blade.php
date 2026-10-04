@php $selected = $selectedApplication ?? null; @endphp

@if($selected)
    <input type="hidden" name="id" value="{{ $selected->id }}">
@endif

<div class="form-row">
    <div class="form-group col-md-6">
        <label class="label-style-1">Visa Application <span class="text-danger">*</span></label>
        @if($selected)
            <input type="text" class="form-control input-style-1"
                value="{{ $selected->application_no }} — {{ $selected->applicant_name }}" disabled>
        @else
            <select name="id" id="appointment_application" class="form-control input-style-1 select2">
                <option value="">Select application</option>
                @foreach($applications as $application)
                    <option value="{{ $application->id }}" data-country="{{ $application->country }}"
                            @selected(old('id') == $application->id)>
                        {{ $application->application_no }} — {{ $application->applicant_name }}
                        @if($application->country) ({{ $application->country }}) @endif
                    </option>
                @endforeach
            </select>
            @error('id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
        @endif
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="embassy_center">Embassy / Center <span class="text-danger">*</span></label>
        @php $selectedCenter = old('embassy_center', $selected?->embassy_center); @endphp
        {{-- Missions are listed per destination country; picking the application
             above narrows this to the country that can issue that visa. --}}
        <select id="embassy_center" name="embassy_center" class="form-control input-style-1"
                data-country="{{ $selected?->country }}" data-selected="{{ $selectedCenter }}">
            <option value="">Select embassy / centre</option>
        </select>
        <small class="text-muted embassy-hint">Pick the visa application first to see its country's centres.</small>
        @error('embassy_center') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="appointment_date">Date <span class="text-danger">*</span></label>
        <input type="date" id="appointment_date" name="appointment_date" class="form-control input-style-1"
            value="{{ old('appointment_date', optional($selected?->appointment_date)->format('Y-m-d')) }}">
        @error('appointment_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="appointment_time">Time <span class="text-danger">*</span></label>
        <input type="time" id="appointment_time" name="appointment_time" class="form-control input-style-1"
            value="{{ old('appointment_time', $selected?->appointment_time ? substr($selected->appointment_time, 0, 5) : '') }}">
        @error('appointment_time') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="appointment_status">Status <span class="text-danger">*</span></label>
        <select id="appointment_status" name="appointment_status" class="form-control input-style-1 select2">
            @foreach($appointmentStatuses as $status)
                <option value="{{ $status }}"
                    @selected(old('appointment_status', $selected?->appointment_status ?? 'Pending') === $status)>
                    {{ $status }}
                </option>
            @endforeach
        </select>
        @error('appointment_status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-12">
        <label class="label-style-1" for="appointment_notes">Notes</label>
        <textarea id="appointment_notes" name="appointment_notes" class="form-control input-style-1" rows="4"
            placeholder="Required documents or instructions">{{ old('appointment_notes', $selected?->appointment_notes) }}</textarea>
        @error('appointment_notes') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
</div>

@push('scripts')
<script>
    // The embassy list follows the application's destination country.
    (function () {
        const centersByCountry = @json($embassyCenters);
        const select   = document.getElementById('embassy_center');
        const appSelect = document.getElementById('appointment_application');
        if (!select) return;

        const hint = select.parentElement.querySelector('.embassy-hint');

        function fill(country) {
            const chosen = select.dataset.selected || '';
            const list   = centersByCountry[country] || [];

            select.innerHTML = '<option value="">Select embassy / centre</option>';
            list.forEach(function (center) {
                const option = document.createElement('option');
                option.value = center;
                option.textContent = center;
                option.selected = center === chosen;
                select.appendChild(option);
            });

            // Never lose a centre already saved for this appointment.
            if (chosen && !list.includes(chosen)) {
                const option = document.createElement('option');
                option.value = chosen;
                option.textContent = chosen;
                option.selected = true;
                select.appendChild(option);
            }

            if (hint) {
                hint.textContent = country
                    ? (list.length ? country + ' — ' + list.length + ' centre(s) listed.'
                                   : 'No centre listed for ' + country + ' yet.')
                    : 'Pick the visa application first to see its country\'s centres.';
            }
        }

        fill(select.dataset.country || '');

        if (appSelect) {
            appSelect.addEventListener('change', function () {
                const option = appSelect.options[appSelect.selectedIndex];
                select.dataset.selected = '';
                fill(option ? (option.dataset.country || '') : '');
            });
        }
    })();
</script>
@endpush
