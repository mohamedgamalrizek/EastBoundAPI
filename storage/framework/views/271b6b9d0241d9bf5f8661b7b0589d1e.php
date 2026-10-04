<?php $__env->startSection('title'); ?>
<?php echo e(___('menus.general_settings')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('maincontent'); ?>
<div class="container-fluid dashboard-content">
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#" class="breadcrumb-link"><?php echo e(___('menus.settings')); ?></a></li>
                            <li class="breadcrumb-item"><a href="<?php echo e(route('settings.general.index')); ?>" class="breadcrumb-link active"><?php echo e(___('menus.general_settings')); ?></a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="card-header">
                    <h4 class="title-site"><?php echo e(___('menus.general_settings')); ?></h4>
                    <?php if (isset($component)) { $__componentOriginal94c28860a1a2364f6fd1663082f160f8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal94c28860a1a2364f6fd1663082f160f8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.how-it-works','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('how-it-works'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal94c28860a1a2364f6fd1663082f160f8)): ?>
<?php $attributes = $__attributesOriginal94c28860a1a2364f6fd1663082f160f8; ?>
<?php unset($__attributesOriginal94c28860a1a2364f6fd1663082f160f8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal94c28860a1a2364f6fd1663082f160f8)): ?>
<?php $component = $__componentOriginal94c28860a1a2364f6fd1663082f160f8; ?>
<?php unset($__componentOriginal94c28860a1a2364f6fd1663082f160f8); ?>
<?php endif; ?>
                </div>
                <div class="tv-card-body">

                    <div class="settings-index">
                        <a href="#basic-settings">Basic Agency</a>
                        <a href="#localization-settings">Defaults</a>
                        <a href="#booking-policy-settings">Booking Policy</a>
                        <a href="#public-contact-settings">Public Contact</a>
                        <a href="#asset-settings">Logos & Favicon</a>
                    </div>

                    <form action="<?php echo e(route('settings.update')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <div class="settings-form-row">
                            <section class="settings-section" id="basic-settings">
                                <div class="settings-section__head">
                                    <div>
                                        <h5>Basic Agency</h5>
                                        <p>Main business identity used across the admin panel and public website.</p>
                                    </div>
                                    <span class="settings-section__tag">Core</span>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="name"><?php echo e(___('label.name')); ?></label>
                                        <input id="name" type="text" name="name" placeholder="<?php echo e(___('placeholder.enter_name')); ?>" class="form-control input-style-1" value="<?php echo e(old('name', settings('name'))); ?>">
                                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger mt-2"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="phone"><?php echo e(___('label.phone')); ?></label>
                                        <input id="phone" type="text" name="phone" placeholder="<?php echo e(___('placeholder.enter_phone')); ?>" class="form-control input-style-1" value="<?php echo e(old('phone', settings('phone'))); ?>">
                                        <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger mt-2"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="email"><?php echo e(___('label.email')); ?></label>
                                        <input id="email" type="text" name="email" placeholder="<?php echo e(___('placeholder.enter_email')); ?>" class="form-control input-style-1" value="<?php echo e(old('email', settings('email'))); ?>">
                                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger mt-2"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="form-group col-md-8">
                                        <label class="label-style-1" for="copyright"><?php echo e(___('label.copyright')); ?></label>
                                        <input id="copyright" type="text" name="copyright" placeholder="<?php echo e(___('placeholder.enter_copyright')); ?>" class="form-control input-style-1" value="<?php echo e(old('copyright', settings('copyright'))); ?>">
                                        <?php $__errorArgs = ['copyright'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger mt-2"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                            </section>


                            <section class="settings-section" id="localization-settings">
                                <div class="settings-section__head">
                                    <div>
                                        <h5>Defaults</h5>
                                        <p>System-wide display defaults for lists, dates, time, language, and currency.</p>
                                    </div>
                                    <span class="settings-section__tag">System</span>
                                </div>
                                <div class="form-row">
                                    
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="vat_rate">VAT rate (%)</label>
                                        <input id="vat_rate" type="number" step="0.01" min="0" name="vat_rate" placeholder="15" class="form-control input-style-1" value="<?php echo e(old('vat_rate', settings('vat_rate'))); ?>">
                                        <?php $__errorArgs = ['vat_rate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger mt-2"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="ait_rate">AIT rate (%)</label>
                                        <input id="ait_rate" type="number" step="0.01" min="0" name="ait_rate" placeholder="5" class="form-control input-style-1" value="<?php echo e(old('ait_rate', settings('ait_rate'))); ?>">
                                        <?php $__errorArgs = ['ait_rate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger mt-2"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="paginate_value"><?php echo e(___('label.paginate_value')); ?></label>
                                        <input id="paginate_value" type="number" name="paginate_value" placeholder="<?php echo e(___('placeholder.paginate_value')); ?>" class="form-control input-style-1" value="<?php echo e(old('paginate_value', settings('paginate_value'))); ?>">
                                        <?php $__errorArgs = ['paginate_value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger mt-2"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="date_format"><?php echo e(___('label.date_format')); ?></label>
                                        <select id="date_format" class="form-control input-style-1 select2" name="date_format">
                                            <?php $__currentLoopData = config('site.date_format'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $format): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($format); ?>" <?php if(old('date_format', settings('date_format')) == $format): echo 'selected'; endif; ?>><?php echo e(today()->format($format)); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <?php $__errorArgs = ['date_format'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger mt-2"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="time_format"><?php echo e(___('label.time_format')); ?></label>
                                        <select id="time_format" class="form-control input-style-1 select2" name="time_format">
                                            <?php $__currentLoopData = config('site.time_format'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $format): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($format); ?>" <?php if(old('time_format', settings('time_format')) == $format): echo 'selected'; endif; ?>><?php echo e(now()->format($format)); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <?php $__errorArgs = ['time_format'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger mt-2"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="currency_code"><?php echo e(___('label.currency')); ?></label>
                                        <select class="form-control input-style-1 select2" id="currency_code" name="currency_code" required>
                                            <option></option>
                                            <?php $__currentLoopData = $currencies ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $currency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($currency->code); ?>" <?php if(old('currency_code', settings('currency_code')) == $currency->code): echo 'selected'; endif; ?>><?php echo e($currency->name . ' - ' . $currency->symbol); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <?php $__errorArgs = ['currency_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger mt-2"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="language"><?php echo e(___('label.default language')); ?></label>
                                        <select name="language" id="language" class="form-control input-style-1 select2">
                                            <option></option>
                                            <?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($row->code); ?>" <?php if(old('language', settings('language')) == $row->code): echo 'selected'; endif; ?>><?php echo e($row->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <?php $__errorArgs = ['language'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger mt-2"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                            </section>

                            <section class="settings-section" id="booking-policy-settings">
                                <div class="settings-section__head">
                                    <div>
                                        <h5>Booking Policy</h5>
                                        <p>Controls when a customer may cancel their own paid tour booking, and what the agency keeps if they do.</p>
                                    </div>
                                    <span class="settings-section__tag">Bookings</span>
                                </div>
                                <div class="form-row">
                                    
                                    <div class="form-group col-md-6">
                                        <label class="label-style-1" for="booking_cancellation_window_hours">Cancellation window (hours)</label>
                                        <input id="booking_cancellation_window_hours" type="number" step="1" min="0" name="booking_cancellation_window_hours" placeholder="24" class="form-control input-style-1" value="<?php echo e(old('booking_cancellation_window_hours', settings('booking_cancellation_window_hours'))); ?>">
                                        <small class="text-muted d-block mt-2">A customer can only cancel a booking online if the travel date is at least this many hours away.</small>
                                        <?php $__errorArgs = ['booking_cancellation_window_hours'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger mt-2"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="label-style-1" for="booking_cancellation_penalty_percent">Cancellation penalty (%)</label>
                                        <input id="booking_cancellation_penalty_percent" type="number" step="1" min="0" max="100" name="booking_cancellation_penalty_percent" placeholder="10" class="form-control input-style-1" value="<?php echo e(old('booking_cancellation_penalty_percent', settings('booking_cancellation_penalty_percent'))); ?>">
                                        <small class="text-muted d-block mt-2">Percentage of the paid amount the agency keeps when a customer cancels a paid booking; the rest is refunded to their wallet.</small>
                                        <?php $__errorArgs = ['booking_cancellation_penalty_percent'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger mt-2"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                            </section>

                            <section class="settings-section" id="public-contact-settings">
                                <div class="settings-section__head">
                                    <div>
                                        <h5>Public Contact</h5>
                                        <p>Contact information shown in the website header, footer, and contact page.</p>
                                    </div>
                                    <span class="settings-section__tag">Website</span>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="phone_secondary">Secondary phone</label>
                                        <input id="phone_secondary" type="text" name="phone_secondary" placeholder="+880 2 5500-0000" class="form-control input-style-1" value="<?php echo e(old('phone_secondary', settings('phone_secondary'))); ?>">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="whatsapp">WhatsApp number</label>
                                        <input id="whatsapp" type="text" name="whatsapp" placeholder="+880 1700-000000" class="form-control input-style-1" value="<?php echo e(old('whatsapp', settings('whatsapp'))); ?>">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="address_short">Short address <small class="text-muted">(top bar)</small></label>
                                        <input id="address_short" type="text" name="address_short" placeholder="Gulshan, Dhaka, Bangladesh" class="form-control input-style-1" value="<?php echo e(old('address_short', settings('address_short'))); ?>">
                                    </div>
                                    <div class="form-group col-md-8">
                                        <label class="label-style-1" for="address">Full address <small class="text-muted">(footer and contact page)</small></label>
                                        <input id="address" type="text" name="address" placeholder="House 42, Road 11, Gulshan-1, Dhaka 1212, Bangladesh" class="form-control input-style-1" value="<?php echo e(old('address', settings('address'))); ?>">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1" for="site_tagline">Footer tagline</label>
                                        <input id="site_tagline" type="text" name="site_tagline" placeholder="Your trusted travel partner" class="form-control input-style-1" value="<?php echo e(old('site_tagline', settings('site_tagline'))); ?>">
                                    </div>
                                </div>
                            </section>

                            <section class="settings-section" id="asset-settings">
                                <div class="settings-section__head">
                                    <div>
                                        <h5>Logos & Favicon</h5>
                                        <p>Brand assets used in the admin panel, public website, browser tab, and login pages.</p>
                                    </div>
                                    <span class="settings-section__tag">Assets</span>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label class="label-style-1"><?php echo e(___('label.light_theme_logo')); ?><span class="fillable"></span></label>
                                        <div class="ot_fileUploader left-side mb-3">
                                            <input class="form-control input-style-1 placeholder" type="text" placeholder="Attach File" readonly>
                                            <button class="primary-btn-small-input" type="button">
                                                <label class="j-td-btn" for="light_theme_logo"><?php echo e(___('label.browse')); ?></label>
                                                <input type="file" class="d-none form-control" name="light_theme_logo" id="light_theme_logo" accept="image/jpeg, image/jpg, image/png, image/webp">
                                            </button>
                                        </div>
                                        <div class="col-6 text-center p-1">
                                            <img src="<?php echo e(logo(settings('light_theme_logo'))); ?>" alt="logo" height="50" class="obj-fit-contain">
                                        </div>
                                        <small class="text-muted d-block mt-2"><?php echo e(___('label.light_theme_logo_help')); ?></small>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label class="label-style-1"><?php echo e(___('label.dark_theme_logo')); ?> <span class="fillable"></span></label>
                                        <div class="ot_fileUploader left-side mb-3">
                                            <input class="form-control input-style-1 placeholder" type="text" placeholder="Attach File" readonly>
                                            <button class="primary-btn-small-input" type="button">
                                                <label class="j-td-btn" for="dark_theme_logo"><?php echo e(___('label.browse')); ?></label>
                                                <input type="file" class="d-none form-control" name="dark_theme_logo" id="dark_theme_logo" accept="image/jpeg, image/jpg, image/png, image/webp">
                                            </button>
                                        </div>
                                        <div class="text-center bg-dark p-1">
                                            <img src="<?php echo e(logo(settings('dark_theme_logo'))); ?>" alt="Logo" height="50" class="obj-fit-contain">
                                        </div>
                                        <small class="text-muted d-block mt-2"><?php echo e(___('label.dark_theme_logo_help')); ?></small>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label class="label-style-1"><?php echo e(___('label.favicon')); ?><span class="fillable"></span></label>
                                        <div class="ot_fileUploader left-side mb-3">
                                            <input class="form-control input-style-1 placeholder" type="text" placeholder="Image" readonly>
                                            <button class="primary-btn-small-input" type="button">
                                                <label class="j-td-btn" for="fileBrouse2"><?php echo e(___('label.Browse')); ?></label>
                                                <input type="file" class="d-none form-control" name="favicon" id="fileBrouse2" accept="image/jpg, image/jpeg, image/png">
                                            </button>
                                        </div>
                                        <div class="text-center">
                                            <img src="<?php echo e(favicon(settings('favicon'))); ?>" alt="favicon" class="rounded mt-3" width="50">
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <div class="j-create-btns mt-4">
                            <div class="drp-btns">
                                <button type="submit" class="j-td-btn"><?php echo e(___('label.save_change')); ?></button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.partials.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Volumes/Home/Personal Work/EastBound/Platform/WebSourceCode/resources/views/backend/settings/general_settings/index.blade.php ENDPATH**/ ?>