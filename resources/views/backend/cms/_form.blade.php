{{-- Shared CMS Page form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $statuses. --}}
@php
    $slug = trim((string) old('slug', $item->slug ?? ''));
    $legalSlugs = ['privacy-policy', 'terms-conditions', 'refund-policy', 'cancellation-policy'];

    $isHome = $slug === '/';
    $isAbout = $slug === 'about-us';
    $isBlog = $slug === 'blog';
    $isContact = $slug === 'contact-us';
    $isAgent = $slug === 'become-an-agent';
    $isLegal = in_array($slug, $legalSlugs, true);
    $isCreate = ! $item;
    $isGeneric = ! $isHome && ! $isAbout && ! $isBlog && ! $isContact && ! $isAgent;
    $showHeader = $isAbout || $isBlog || $isContact || $isAgent;
@endphp

<div class="form-row">

    <div class="form-group col-md-6">
        <label class="label-style-1" for="title">{{ ___('label.title') }} <span class="text-danger">*</span></label>
        <input type="text" id="title" name="title" class="form-control input-style-1" placeholder="{{ ___('label.title') }}" value="{{ old('title', $item->title ?? '') }}">
        @error('title') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="slug">{{ ___('label.slug') }} <span class="text-danger">*</span></label>
        <input type="text" id="slug" name="slug" class="form-control input-style-1" placeholder="{{ ___('label.slug') }}" value="{{ $slug }}">
        @error('slug') <small class="text-danger mt-2">{{ $message }}</small> @enderror
        @if($isCreate)
            <small class="text-muted d-block mt-1">{{ ___('label.save_once_hint') }}</small>
        @endif
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $item->status ?? 'published') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="meta_description">{{ ___('label.meta_description') }} <small class="text-muted">{{ ___('label.seo_hint') }}</small></label>
        <input type="text" id="meta_description" name="meta_description" class="form-control input-style-1" value="{{ old('meta_description', $item->meta_description ?? '') }}">
        @error('meta_description') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    @if($showHeader)
        <div class="col-12"><hr><h6 class="mb-3 mt-2">{{ ___('label.page_header') }}</h6></div>

        <div class="form-group col-md-6">
            <label class="label-style-1" for="breadcrumb_label">{{ ___('label.breadcrumb_label') }}</label>
            <input type="text" id="breadcrumb_label" name="breadcrumb_label" class="form-control input-style-1" placeholder="{{ $item?->title ?: ___('label.page_label') }}" value="{{ old('breadcrumb_label', $item->breadcrumb_label ?? '') }}">
            @error('breadcrumb_label') <small class="text-danger mt-2">{{ $message }}</small> @enderror
        </div>

        <div class="form-group col-md-6">
            <label class="label-style-1" for="hero_subtitle">{{ ___('label.hero_subtitle') }}</label>
            <input type="text" id="hero_subtitle" name="hero_subtitle" class="form-control input-style-1" value="{{ old('hero_subtitle', $item->hero_subtitle ?? '') }}">
            @error('hero_subtitle') <small class="text-danger mt-2">{{ $message }}</small> @enderror
        </div>
    @endif

    @if($isGeneric || $isLegal)
        <div class="col-12"><hr><h6 class="mb-3 mt-2">{{ ___('label.page_content') }}</h6></div>

        <div class="form-group col-md-12">
            <label class="label-style-1" for="body">{{ ___('label.page_content') }} <small class="text-muted">{{ ___('label.markdown_hint') }}</small></label>
            <textarea id="body" name="body" rows="16" class="form-control input-style-1" placeholder="## Section heading&#10;&#10;Your page content...">{{ old('body', $item->body ?? '') }}</textarea>
            @error('body') <small class="text-danger mt-2">{{ $message }}</small> @enderror
        </div>
    @endif

    @if($isHome)
        <div class="col-12"><hr><h6 class="mb-3 mt-2">{{ ___('label.marketing_stats') }}</h6></div>

        @foreach([
            'stat_travelers' => [___('label.happy_travelers'), '12K+'],
            'stat_destinations' => [___('label.destinations'), '220+'],
            'stat_visa_success' => [___('label.visa_success'), '98%'],
            'stat_experience' => [___('label.experience'), '11 yrs'],
        ] as $field => [$label, $placeholder])
            <div class="form-group col-md-3">
                <label class="label-style-1" for="{{ $field }}">{{ $label }}</label>
                <input type="text" id="{{ $field }}" name="{{ $field }}" class="form-control input-style-1" placeholder="{{ $placeholder }}" value="{{ old($field, $item->{$field} ?? '') }}">
                @error($field) <small class="text-danger mt-2">{{ $message }}</small> @enderror
            </div>
        @endforeach

        <div class="col-12"><hr><h6 class="mb-3 mt-2">{{ ___('label.header_promo') }} <small class="text-muted fw-normal">{{ ___('label.blank_title_hides_panel') }}</small></h6></div>

        <div class="form-group col-md-2">
            <label class="label-style-1" for="promo_badge">{{ ___('label.promo_badge') }}</label>
            <input type="text" id="promo_badge" name="promo_badge" class="form-control input-style-1" placeholder="Hot Deal" value="{{ old('promo_badge', $item->promo_badge ?? '') }}">
            @error('promo_badge') <small class="text-danger mt-2">{{ $message }}</small> @enderror
        </div>
        <div class="form-group col-md-3">
            <label class="label-style-1" for="promo_title">{{ ___('label.promo_title') }}</label>
            <input type="text" id="promo_title" name="promo_title" class="form-control input-style-1" placeholder="Umrah Packages" value="{{ old('promo_title', $item->promo_title ?? '') }}">
            @error('promo_title') <small class="text-danger mt-2">{{ $message }}</small> @enderror
        </div>
        <div class="form-group col-md-4">
            <label class="label-style-1" for="promo_text">{{ ___('label.promo_text') }}</label>
            <input type="text" id="promo_text" name="promo_text" class="form-control input-style-1" placeholder="Premium packages from BDT 145,000" value="{{ old('promo_text', $item->promo_text ?? '') }}">
            @error('promo_text') <small class="text-danger mt-2">{{ $message }}</small> @enderror
        </div>
        <div class="form-group col-md-3">
            <label class="label-style-1" for="promo_link">{{ ___('label.promo_link') }}</label>
            <input type="text" id="promo_link" name="promo_link" class="form-control input-style-1" placeholder="/umrah" value="{{ old('promo_link', $item->promo_link ?? '') }}">
            @error('promo_link') <small class="text-danger mt-2">{{ $message }}</small> @enderror
        </div>
    @endif

    @if($isAbout)
        <div class="col-12"><hr><h6 class="mb-3 mt-2">{{ ___('label.story_block') }}</h6></div>

        <div class="form-group col-md-6">
            <label class="label-style-1" for="story_heading">{{ ___('label.story_heading') }}</label>
            <input type="text" id="story_heading" name="story_heading" class="form-control input-style-1" placeholder="Built by travel people, for travelers" value="{{ old('story_heading', $item->story_heading ?? '') }}">
            @error('story_heading') <small class="text-danger mt-2">{{ $message }}</small> @enderror
        </div>

        <div class="form-group col-md-6">
            <label class="label-style-1" for="story_image">{{ ___('label.story_image') }} <small class="text-muted">{{ ___('label.url_or_path_hint') }}</small></label>
            <input type="text" id="story_image" name="story_image" class="form-control input-style-1" placeholder="https://..." value="{{ old('story_image', $item->story_image ?? '') }}">
            @error('story_image') <small class="text-danger mt-2">{{ $message }}</small> @enderror
        </div>

        <div class="form-group col-md-4">
            <label class="label-style-1" for="story_eyebrow">{{ ___('label.story_eyebrow') }}</label>
            <input type="text" id="story_eyebrow" name="story_eyebrow" class="form-control input-style-1" placeholder="Our story" value="{{ old('story_eyebrow', $item->story_eyebrow ?? '') }}">
            @error('story_eyebrow') <small class="text-danger mt-2">{{ $message }}</small> @enderror
        </div>

        <div class="form-group col-md-8">
            <label class="label-style-1" for="story_badges">{{ ___('label.story_badges') }} <small class="text-muted">{{ ___('label.comma_separated') }}</small></label>
            <input type="text" id="story_badges" name="story_badges" class="form-control input-style-1" placeholder="IATA accredited, 24/7 support" value="{{ old('story_badges', $item->story_badges ?? '') }}">
            @error('story_badges') <small class="text-danger mt-2">{{ $message }}</small> @enderror
        </div>

        <div class="form-group col-md-12">
            <label class="label-style-1" for="story_lead">{{ ___('label.story_lead_paragraph') }}</label>
            <textarea id="story_lead" name="story_lead" rows="2" class="form-control input-style-1">{{ old('story_lead', $item->story_lead ?? '') }}</textarea>
            @error('story_lead') <small class="text-danger mt-2">{{ $message }}</small> @enderror
        </div>

        <div class="form-group col-md-12">
            <label class="label-style-1" for="story_body">{{ ___('label.story_body_paragraph') }}</label>
            <textarea id="story_body" name="story_body" rows="3" class="form-control input-style-1">{{ old('story_body', $item->story_body ?? '') }}</textarea>
            @error('story_body') <small class="text-danger mt-2">{{ $message }}</small> @enderror
        </div>

        <div class="col-12"><hr><h6 class="mb-3 mt-2">{{ ___('label.marketing_stats') }}</h6></div>

        @foreach([
            'stat_travelers' => [___('label.happy_travelers'), '12K+'],
            'stat_destinations' => [___('label.destinations'), '220+'],
            'stat_visa_success' => [___('label.visa_success'), '98%'],
            'stat_experience' => [___('label.experience'), '11 yrs'],
        ] as $field => [$label, $placeholder])
            <div class="form-group col-md-3">
                <label class="label-style-1" for="{{ $field }}">{{ $label }}</label>
                <input type="text" id="{{ $field }}" name="{{ $field }}" class="form-control input-style-1" placeholder="{{ $placeholder }}" value="{{ old($field, $item->{$field} ?? '') }}">
                @error($field) <small class="text-danger mt-2">{{ $message }}</small> @enderror
            </div>
        @endforeach
    @endif

    @if($isBlog)
        <div class="col-12"><hr><h6 class="mb-3 mt-2">{{ ___('label.blog_listing_text') }}</h6></div>

        @foreach([
            'search_placeholder' => [___('label.search_placeholder_label'), 'Search articles...'],
            'all_label' => [___('label.all_filter_label'), 'All'],
            'read_more_label' => [___('label.read_more_label'), 'Read more'],
        ] as $field => [$label, $placeholder])
            <div class="form-group col-md-4">
                <label class="label-style-1" for="{{ $field }}">{{ $label }}</label>
                <input type="text" id="{{ $field }}" name="{{ $field }}" class="form-control input-style-1" placeholder="{{ $placeholder }}" value="{{ old($field, $item->{$field} ?? '') }}">
                @error($field) <small class="text-danger mt-2">{{ $message }}</small> @enderror
            </div>
        @endforeach

        <div class="form-group col-md-6">
            <label class="label-style-1" for="empty_text">{{ ___('label.empty_text') }}</label>
            <input type="text" id="empty_text" name="empty_text" class="form-control input-style-1" placeholder="No articles published yet." value="{{ old('empty_text', $item->empty_text ?? '') }}">
            @error('empty_text') <small class="text-danger mt-2">{{ $message }}</small> @enderror
        </div>

        <div class="form-group col-md-6">
            <label class="label-style-1" for="filtered_empty_text">{{ ___('label.filtered_empty_text') }}</label>
            <input type="text" id="filtered_empty_text" name="filtered_empty_text" class="form-control input-style-1" placeholder="No articles matched your search." value="{{ old('filtered_empty_text', $item->filtered_empty_text ?? '') }}">
            @error('filtered_empty_text') <small class="text-danger mt-2">{{ $message }}</small> @enderror
        </div>
    @endif

    @if($isContact)
        <div class="col-12"><hr><h6 class="mb-3 mt-2">{{ ___('label.contact_cards') }}</h6></div>

        @foreach([
            'contact_address_label' => [___('label.address_card_label'), 'Visit us'],
            'contact_phone_label' => [___('label.phone_card_label'), 'Call us'],
            'contact_email_label' => [___('label.email_card_label'), 'Email us'],
        ] as $field => [$label, $placeholder])
            <div class="form-group col-md-4">
                <label class="label-style-1" for="{{ $field }}">{{ $label }}</label>
                <input type="text" id="{{ $field }}" name="{{ $field }}" class="form-control input-style-1" placeholder="{{ $placeholder }}" value="{{ old($field, $item->{$field} ?? '') }}">
                @error($field) <small class="text-danger mt-2">{{ $message }}</small> @enderror
            </div>
        @endforeach

        <div class="col-12"><hr><h6 class="mb-3 mt-2">{{ ___('label.contact_form') }}</h6></div>

        @foreach([
            'form_title' => [___('label.form_title'), 'Send us a message'],
            'form_intro' => [___('label.form_intro'), "Fill in the form and we'll respond within one business day."],
            'form_name_label' => [___('label.name_label'), 'Full name'],
            'form_name_placeholder' => [___('label.name_placeholder'), 'Your name'],
            'form_phone_label' => [___('label.phone_label'), 'Phone'],
            'form_phone_placeholder' => [___('label.phone_placeholder'), '+880 17...'],
            'form_email_label' => [___('label.email_label'), 'Email'],
            'form_email_placeholder' => [___('label.email_placeholder'), 'you@email.com'],
            'form_notes_label' => [___('label.subject_label'), 'Subject'],
            'form_notes_placeholder' => [___('label.custom_subject_label_placeholder'), 'Type your subject'],
            'form_message_label' => [___('label.message_label'), 'Message'],
            'form_message_placeholder' => [___('label.message_placeholder'), 'How can we help?'],
            'form_submit_button' => [___('label.submit_button'), 'Send message'],
            'success_message' => [___('label.success_message'), 'Thanks! Your message has been sent.'],
        ] as $field => [$label, $placeholder])
            <div class="form-group col-md-4">
                <label class="label-style-1" for="{{ $field }}">{{ $label }}</label>
                <input type="text" id="{{ $field }}" name="{{ $field }}" class="form-control input-style-1" placeholder="{{ $placeholder }}" value="{{ old($field, $item->{$field} ?? '') }}">
                @error($field) <small class="text-danger mt-2">{{ $message }}</small> @enderror
            </div>
        @endforeach

    @endif

    @if($isAgent)
        <div class="col-12"><hr><h6 class="mb-3 mt-2">{{ ___('label.agent_program_section') }}</h6></div>

        @foreach([
            'section_icon' => [___('label.section_icon'), 'fa-handshake'],
            'section_eyebrow' => [___('label.section_eyebrow'), 'Agent program'],
            'section_heading' => [___('label.section_heading'), 'Why partner with us'],
        ] as $field => [$label, $placeholder])
            <div class="form-group col-md-4">
                <label class="label-style-1" for="{{ $field }}">{{ $label }}</label>
                <input type="text" id="{{ $field }}" name="{{ $field }}" class="form-control input-style-1" placeholder="{{ $placeholder }}" value="{{ old($field, $item->{$field} ?? '') }}">
                @error($field) <small class="text-danger mt-2">{{ $message }}</small> @enderror
            </div>
        @endforeach

        <div class="col-12"><hr><h6 class="mb-3 mt-2">{{ ___('label.agent_application_form') }}</h6></div>

        @foreach([
            'form_title' => [___('label.form_title'), 'Apply to become an agent'],
            'form_intro' => [___('label.form_intro'), 'Fill in your details and our partnerships team will get in touch.'],
            'form_name_label' => [___('label.name_label'), 'Full name / Company'],
            'form_name_placeholder' => [___('label.name_placeholder'), 'Your name or agency'],
            'form_phone_label' => [___('label.phone_label'), 'Phone'],
            'form_phone_placeholder' => [___('label.phone_placeholder'), '+880 17...'],
            'form_email_label' => [___('label.email_label'), 'Email'],
            'form_email_placeholder' => [___('label.email_placeholder'), 'you@email.com'],
            'form_notes_label' => [___('label.notes_label'), 'Tell us about your business'],
            'form_notes_placeholder' => [___('label.notes_placeholder'), 'Location, experience, monthly volume...'],
            'form_submit_button' => [___('label.submit_button'), 'Submit application'],
            'success_message' => [___('label.success_message'), 'Application received! Our partnerships team will reach out to you shortly.'],
        ] as $field => [$label, $placeholder])
            <div class="form-group col-md-4">
                <label class="label-style-1" for="{{ $field }}">{{ $label }}</label>
                <input type="text" id="{{ $field }}" name="{{ $field }}" class="form-control input-style-1" placeholder="{{ $placeholder }}" value="{{ old($field, $item->{$field} ?? '') }}">
                @error($field) <small class="text-danger mt-2">{{ $message }}</small> @enderror
            </div>
        @endforeach
    @endif

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('cms.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
