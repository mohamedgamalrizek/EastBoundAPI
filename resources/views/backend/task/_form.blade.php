{{-- Shared Task form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $users, $priorities, $statuses. --}}
@php
    $app = $item ?? null;
    $dueDate = old('due_date', $app && $app->due_date ? $app->due_date->format('Y-m-d') : '');
@endphp

<div class="form-row">

    <div class="form-group col-md-6">
        <label class="label-style-1" for="title">{{ ___('label.title') }} <span class="text-danger">*</span></label>
        <input type="text" id="title" name="title" class="form-control input-style-1" placeholder="{{ ___('label.title') }}" value="{{ old('title', $app->title ?? '') }}">
        @error('title') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="assigned_to">{{ ___('label.assigned_to') }}</label>
        <select id="assigned_to" name="assigned_to" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" @selected(old('assigned_to', $app->assigned_to ?? '') == $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
        @error('assigned_to') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="project">{{ ___('label.project') }}</label>
        <input type="text" id="project" name="project" class="form-control input-style-1" placeholder="{{ ___('label.project') }}" value="{{ old('project', $app->project ?? '') }}">
        @error('project') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="priority">{{ ___('label.priority') }} <span class="text-danger">*</span></label>
        <select id="priority" name="priority" class="form-control input-style-1 select2">
            @foreach($priorities as $pr)
                <option value="{{ $pr }}" @selected(old('priority', $app->priority ?? 'Medium') === $pr)>{{ $pr }}</option>
            @endforeach
        </select>
        @error('priority') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="due_date">{{ ___('label.due_date') }}</label>
        <input type="date" id="due_date" name="due_date" class="form-control input-style-1" value="{{ $dueDate }}">
        @error('due_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $app->status ?? 'Todo') === $st)>{{ $st }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $app ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('task.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
