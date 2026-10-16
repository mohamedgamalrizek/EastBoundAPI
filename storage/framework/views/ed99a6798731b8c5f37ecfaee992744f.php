<?php $lead = $lead ?? null; ?>

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control input-style-1" placeholder="Lead name" maxlength="255" required value="<?php echo e(old('name', $lead->name ?? '')); ?>">
        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="pt-2 text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="form-group col-md-6">
        <label>Phone</label>
        <input type="text" name="phone" class="form-control input-style-1" placeholder="+880 17..." value="<?php echo e(old('phone', $lead->phone ?? '')); ?>">
        <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="pt-2 text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="form-group col-md-6">
        <label>Email</label>
        <input type="email" name="email" class="form-control input-style-1" placeholder="you@email.com" value="<?php echo e(old('email', $lead->email ?? '')); ?>">
        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="pt-2 text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="form-group col-md-6">
        <label>Interested In</label>
        <input type="text" name="interest" class="form-control input-style-1" placeholder="e.g. Maldives Tour" value="<?php echo e(old('interest', $lead->interest ?? '')); ?>">
        <?php $__errorArgs = ['interest'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="pt-2 text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="form-group col-md-4">
        <label>Source <span class="text-danger">*</span></label>
        <?php $selSource = old('source', $lead->source ?? 'Website'); ?>
        <select name="source" class="form-control input-style-1 select2">
            <?php $__currentLoopData = ['Facebook','Website','Referral','WhatsApp','Instagram','Walk-in']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($s); ?>" <?php if($selSource === $s): echo 'selected'; endif; ?>><?php echo e($s); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php $__errorArgs = ['source'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="pt-2 text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="form-group col-md-4">
        <label>Website</label>
        <input type="text" name="website" class="form-control input-style-1" placeholder="e.g. Egypt Tours" maxlength="255" value="<?php echo e(old('website', $lead->website ?? '')); ?>">
        <?php $__errorArgs = ['website'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="pt-2 text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="form-group col-md-4">
        <label>Language</label>
        <select name="language" class="form-control input-style-1 select2">
            <option value="">Not specified</option>
            <?php $__currentLoopData = \App\Models\Lead::LANGUAGES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($language); ?>" <?php if(old('language', $lead->language ?? '') === $language): echo 'selected'; endif; ?>><?php echo e($language); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php $__errorArgs = ['language'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="pt-2 text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="form-group col-md-4">
        <label>Country</label>
        <input type="text" name="country" class="form-control input-style-1" placeholder="e.g. Egypt" maxlength="255" value="<?php echo e(old('country', $lead->country ?? '')); ?>">
        <?php $__errorArgs = ['country'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="pt-2 text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="form-group col-md-4">
        <label>Campaign Source</label>
        <select name="campaign_source" class="form-control input-style-1 select2">
            <option value="">Not specified</option>
            <?php $__currentLoopData = \App\Models\Lead::CAMPAIGN_SOURCES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaignSource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($campaignSource); ?>" <?php if(old('campaign_source', $lead->campaign_source ?? '') === $campaignSource): echo 'selected'; endif; ?>><?php echo e($campaignSource); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php $__errorArgs = ['campaign_source'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="pt-2 text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="form-group col-md-4">
        <label>Estimated Value (<?php echo e(currency_symbol()); ?>)</label>
        <input type="number" min="0" step="0.01" name="value" class="form-control input-style-1" placeholder="2500" value="<?php echo e(old('value', $lead->value ?? 0)); ?>">
        <?php $__errorArgs = ['value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="pt-2 text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="form-group col-md-4">
        <label>Stage <span class="text-danger">*</span></label>
        <?php $selStage = old('stage', $lead->stage ?? 'New'); ?>
        <select name="stage" class="form-control input-style-1 select2">
            <?php $__currentLoopData = ['New','Contacted','Proposal','Negotiation','Won','Lost']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($st); ?>" <?php if($selStage === $st): echo 'selected'; endif; ?>><?php echo e($st); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php $__errorArgs = ['stage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="pt-2 text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="form-group col-md-6">
        <label for="owner">Assign To (Owner)</label>
        <?php
            $selOwner = old('owner', $lead->owner ?? '');
            // Legacy leads may hold a free-text owner that no longer matches a
            // user account; keep it selectable so editing doesn't drop it.
            $ownerIsKnown = $selOwner === '' || $users->contains('name', $selOwner);
        ?>
        <select id="owner" name="owner" class="form-control input-style-1 select2" placeholder="Assign To (Owner)">
            <option value=""></option>
            <?php if (! ($ownerIsKnown)): ?>
            <option value="<?php echo e($selOwner); ?>" selected><?php echo e($selOwner); ?></option>
            <?php endif; ?>
            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($user->name); ?>" <?php if($selOwner === $user->name): echo 'selected'; endif; ?>><?php echo e($user->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php $__errorArgs = ['owner'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="pt-2 text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="form-group col-md-12">
        <h5 class="mt-3">Trip Details</h5>
    </div>
    <div class="form-group col-md-6">
        <label>Subject</label>
        <input type="text" name="subject" class="form-control input-style-1" maxlength="255" placeholder="e.g. Tour packages" value="<?php echo e(old('subject', $lead->subject ?? '')); ?>">
        <?php $__errorArgs = ['subject'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="pt-2 text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="form-group col-md-6">
        <label>Destinations</label>
        <input type="text" name="destinations" class="form-control input-style-1" maxlength="255" placeholder="e.g. Cairo, Luxor" value="<?php echo e(old('destinations', $lead->destinations ?? '')); ?>">
        <?php $__errorArgs = ['destinations'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="pt-2 text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="form-group col-md-4">
        <label>From Date</label>
        <input type="date" name="from_date" class="form-control input-style-1" value="<?php echo e(old('from_date', $lead?->from_date?->format('Y-m-d') ?? '')); ?>">
        <?php $__errorArgs = ['from_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="pt-2 text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="form-group col-md-4">
        <label>To Date</label>
        <input type="date" name="to_date" class="form-control input-style-1" value="<?php echo e(old('to_date', $lead?->to_date?->format('Y-m-d') ?? '')); ?>">
        <?php $__errorArgs = ['to_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="pt-2 text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="form-group col-md-4">
        <label>Duration (Days)</label>
        <input type="number" name="duration" min="0" step="1" class="form-control input-style-1" value="<?php echo e(old('duration', $lead->duration ?? '')); ?>">
        <?php $__errorArgs = ['duration'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="pt-2 text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="form-group col-md-4">
        <label>Adults</label>
        <input type="number" name="adults" min="0" step="1" class="form-control input-style-1" value="<?php echo e(old('adults', $lead->adults ?? '')); ?>">
        <?php $__errorArgs = ['adults'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="pt-2 text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="form-group col-md-4">
        <label>Children</label>
        <input type="number" name="children" min="0" step="1" class="form-control input-style-1" value="<?php echo e(old('children', $lead->children ?? '')); ?>">
        <?php $__errorArgs = ['children'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="pt-2 text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="form-group col-md-4">
        <label>Budget</label>
        <input type="text" name="budget" maxlength="100" class="form-control input-style-1" placeholder="e.g. high" value="<?php echo e(old('budget', $lead->budget ?? '')); ?>">
        <?php $__errorArgs = ['budget'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="pt-2 text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="form-group col-md-6">
        <label>Created At</label>
        <input type="datetime-local" name="created_at" step="1" class="form-control input-style-1" value="<?php echo e(old('created_at', $lead?->created_at?->format('Y-m-d\TH:i:s') ?? '')); ?>">
        <small class="text-muted">Leave empty to use the current time for a new lead.</small>
        <?php $__errorArgs = ['created_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="pt-2 text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="form-group col-md-12">
        <label>Customer Comments</label>
        <textarea name="comments" rows="3" maxlength="5000" class="form-control input-style-1" dir="auto"><?php echo e(old('comments', $lead->comments ?? '')); ?></textarea>
        <?php $__errorArgs = ['comments'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="pt-2 text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="form-group col-md-12">
        <label>Notes / Original Submission</label>
        <textarea name="notes" rows="3" class="form-control input-style-1" placeholder="Lead details…"><?php echo e(old('notes', $lead->notes ?? '')); ?></textarea>
        <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="pt-2 text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
</div>
<?php /**PATH /Volumes/Home/Personal Work/EastBound/Platform/WebSourceCode/resources/views/backend/crm/_lead-form.blade.php ENDPATH**/ ?>