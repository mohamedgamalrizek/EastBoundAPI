@props([
    'name'    => 'phone',
    'dialName'=> 'dial_code',
    'value'   => null,
    'dial'    => null,
    'required'=> false,
    'label'   => null,
    'id'      => null,
])

@php
    use App\Services\Contact\PhoneNumber;

    $codes    = config('dial_codes', []);
    $fieldId  = $id ?: $name;
    // Preselect: whatever was submitted, else the number's own country, else
    // the install's default. A stored E.164 value keeps its country on edit.
    $selected = $dial
        ?: old($dialName, PhoneNumber::dialCodeOf($value) ?: PhoneNumber::defaultDialCode());
    $national = PhoneNumber::nationalPart(old($name, $value), $selected);
@endphp

<div class="phone-input">
    @if($label)
        <label class="form-label" for="{{ $fieldId }}">
            {{ $label }} @if($required)<span class="text-danger">*</span>@endif
        </label>
    @endif

    <div class="phone-input__row d-flex" style="gap:8px;">
        <select name="{{ $dialName }}" class="form-control phone-input__dial" style="max-width:9.5rem;flex:0 0 auto;">
            @foreach($codes as $iso => $country)
                <option value="{{ $country['dial'] }}" @selected((string) $selected === (string) $country['dial'])>
                    {{ $iso }} +{{ $country['dial'] }}
                </option>
            @endforeach
        </select>

        <input type="tel" id="{{ $fieldId }}" name="{{ $name }}" value="{{ $national }}"
               class="form-control" style="flex:1 1 auto;"
               inputmode="tel" autocomplete="tel-national"
               {{ $required ? 'required' : '' }}
               {{ $attributes->except(['class', 'style']) }}>
    </div>

    @error($name) <span class="text-danger small">{{ $message }}</span> @enderror
    @error($dialName) <span class="text-danger small">{{ $message }}</span> @enderror
</div>
