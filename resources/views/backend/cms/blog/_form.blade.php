{{-- Shared Blog form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $statuses. --}}
@php
    $publishedAt = old('published_at', $item && $item->published_at ? $item->published_at->format('Y-m-d') : '');
@endphp

<div class="form-row">

    <div class="form-group col-md-6">
        <label class="label-style-1" for="title">{{ ___('label.title') }} <span class="text-danger">*</span></label>
        <input type="text" id="title" name="title" class="form-control input-style-1" placeholder="{{ ___('label.title') }}" value="{{ old('title', $item->title ?? '') }}">
        @error('title') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="slug">{{ ___('label.slug') }} <span class="text-danger">*</span></label>
        <input type="text" id="slug" name="slug" class="form-control input-style-1" placeholder="{{ ___('label.slug') }}" value="{{ old('slug', $item->slug ?? '') }}">
        @error('slug') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="author">{{ ___('label.author') }}</label>
        <input type="text" id="author" name="author" class="form-control input-style-1" placeholder="{{ ___('label.author') }}" value="{{ old('author', $item->author ?? '') }}">
        @error('author') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $item->status ?? 'published') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="published_at">{{ ___('label.published_at') }}</label>
        <input type="date" id="published_at" name="published_at" class="form-control input-style-1" value="{{ $publishedAt }}">
        @error('published_at') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="category">{{ ___('label.category') }}</label>
        <input type="text" id="category" name="category" class="form-control input-style-1" placeholder="Visa, Beach, Tips…" value="{{ old('category', $item->category ?? '') }}">
        @error('category') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-2">
        <label class="label-style-1" for="read_minutes">{{ ___('label.read_time_min') }}</label>
        <input type="number" min="1" max="120" id="read_minutes" name="read_minutes" class="form-control input-style-1" value="{{ old('read_minutes', $item->read_minutes ?? 4) }}">
        @error('read_minutes') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        @include('backend.components.image-field', [
            'name'    => 'image',
            'label'   => ___('label.cover_image'),
            'current' => $item->image ?? null,
            'folder'  => 'blog',
        ])
    </div>

    <div class="form-group col-md-12">
        <label class="label-style-1" for="excerpt">{{ ___('label.excerpt') }} <small class="text-muted">{{ ___('label.shown_on_listing_cards') }}</small></label>
        <textarea id="excerpt" name="excerpt" rows="2" class="form-control input-style-1" placeholder="{{ ___('label.summarize_article_hint') }}">{{ old('excerpt', $item->excerpt ?? '') }}</textarea>
        @error('excerpt') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-12">
        <label class="label-style-1" for="body">{{ ___('label.article_body') }} <small class="text-muted">{{ ___('label.markdown_hint') }}</small></label>
        <textarea id="body" name="body" rows="14" class="form-control input-style-1" placeholder="## Why it matters&#10;&#10;Your article content…">{{ old('body', $item->body ?? '') }}</textarea>
        @error('body') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('cms.blog.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
