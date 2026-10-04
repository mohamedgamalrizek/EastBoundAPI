<aside class="tv-sidebar j-custom-style" id="backendSidebar" aria-label="<?php echo e(___('menus.main')); ?>">
    <?php
        $settingsVisible = hasPermission('general_settings_read')
            || hasPermission('mail_settings_read')
            || hasPermission('sms_settings_read')
            || hasPermission('payment_settings_read')
            || hasPermission('recaptcha_settings_read')
            || hasPermission('social_login_settings_update')
            || hasPermission('payout_setup_settings_read')
            || hasPermission('general_settings_update');

        $mainVisible        = hasPermission('dashboard_read') || hasPermission('user_read') || hasPermission('role_read') || hasPermission('todo_read');
        $bookingsCrmVisible = hasPermission('customer_read') || hasPermission('crm_read');
        $marketingVisible   = hasPermission('coupon_read') || hasPermission('campaign_read') || hasPermission('review_read');
        $operationsVisible  = hasPermission('tour_read') || hasPermission('visa_read') || hasPermission('hajj_read') || hasPermission('hotel_read') || hasPermission('transport_read') || hasPermission('flight_read');
        $servicesVisible    = hasPermission('insurance_read') || hasPermission('student_service_read') || hasPermission('medical_tour_read') || hasPermission('corporate_travel_read') || hasPermission('event_tour_read');
        $financeVisible     = hasPermission('accounting_read') || hasPermission('supplier_read') || hasPermission('agent_finance_read');
        $workVisible        = hasPermission('task_read') || hasPermission('support_read');
        $reportsVisible     = hasPermission('report_read');
        $contentHrVisible   = hasPermission('cms_read') || hasPermission('general_settings_read') || hasPermission('hr_read');
        $systemVisible      = hasPermission('branch_read') || hasPermission('activity_logs_read') || hasPermission('login_activity_read') || hasPermission('language_read') || $settingsVisible;
    ?>
    <div class="tv-sidebar-scroll">
        <ul class="tv-sidebar-menu" id="backendSidebarMenu">

            <?php if($mainVisible): ?>
            <li class="tv-nav-section"><span><?php echo e(___('menus.main')); ?></span></li>
            <?php endif; ?>

            <?php if( hasPermission('dashboard_read')): ?>
            <li> <a href="<?php echo e(route('dashboard')); ?>" aria-expanded="true"> <i class="icon-chart"></i> <span class="nav-text"><?php echo e(___('menus.dashboard')); ?></span> </a> </li>
            <?php endif; ?>

            <?php if(hasPermission('user_read') || hasPermission('role_read')): ?>
            <li class="<?php echo e((request()->is('*user*','*role*')) ? 'mm-active' : ''); ?>">
                <a class="has-arrow" href="javascript:void()" aria-expanded="true"> <i class="icon-people"></i> <span class="nav-text"><?php echo e(___('menus.user_roles')); ?></span> </a>
                <ul aria-expanded="false">
                    <?php if(hasPermission('user_read')): ?>
                    <li> <a href="<?php echo e(route('user.index')); ?>" class="<?php echo e((request()->is('*user*')) ? 'mm-active' : ''); ?>"><?php echo e(___('menus.users')); ?></a> </li>
                    <?php endif; ?>
                    <?php if(hasPermission('role_read')): ?>
                    <li> <a href="<?php echo e(route('role.index')); ?>" class="<?php echo e((request()->is('*role*')) ? 'mm-active' : ''); ?>"><?php echo e(___('menus.roles')); ?></a> </li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>

            <?php if(hasPermission('todo_read')): ?>
            <li> <a href="<?php echo e(route('todo.index')); ?>" aria-expanded="true"> <i class="icon-notebook"></i> <span class="nav-text"><?php echo e(___('menus.todo_list')); ?></span> </a> </li>
            <?php endif; ?>

            <?php if($bookingsCrmVisible): ?>
            <li class="tv-nav-section"><span><?php echo e(___('menus.bookings_crm')); ?></span></li>
            <?php endif; ?>

            <?php if(hasPermission('customer_read')): ?>
            
            <li> <a href="<?php echo e(route('customer.index')); ?>" class="<?php echo e(request()->is('customers*') ? 'mm-active' : ''); ?>" aria-expanded="true"> <i class="icon-user-following"></i> <span class="nav-text"><?php echo e(___('menus.customers')); ?></span> </a> </li>
            <?php endif; ?>

            
            <?php if(hasPermission('customer_read')): ?>
            <li class="<?php echo e(request()->is('customer/travelers*') || request()->is('customer/passports*') ? 'mm-active' : ''); ?>">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-docs"></i> <span class="nav-text"><?php echo e(___('menus.customer_records')); ?></span> </a>
                <ul aria-expanded="false">
                    <li><a href="<?php echo e(route('customer.traveler.index')); ?>"><?php echo e(___('menus.travelers')); ?></a></li>
                    <li><a href="<?php echo e(route('customer.passport.index')); ?>"><?php echo e(___('menus.passports')); ?></a></li>
                </ul>
            </li>
            <?php endif; ?>

            

            <?php if(hasPermission('crm_read')): ?>
            
            <li class="<?php echo e(request()->is('crm*') ? 'mm-active' : ''); ?>">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-bubbles"></i> <span class="nav-text"><?php echo e(___('menus.crm')); ?></span> </a>
                <ul aria-expanded="false">
                    <li><a href="<?php echo e(route('crm.leads')); ?>" class="<?php echo e(request()->is('crm/leads') ? 'mm-active' : ''); ?>"><?php echo e(___('menus.leads')); ?></a></li>
                    <li><a href="<?php echo e(route('crm.contact-messages.index')); ?>" class="<?php echo e(request()->is('crm/contact-messages*') ? 'mm-active' : ''); ?>"><?php echo e(___('menus.contact_messages')); ?></a></li>
                    <li><a href="<?php echo e(route('crm.job-applications.index')); ?>" class="<?php echo e(request()->is('crm/job-applications*') ? 'mm-active' : ''); ?>"><?php echo e(___('menus.job_applications')); ?></a></li>
                    <li><a href="<?php echo e(route('crm.activity.index')); ?>"><?php echo e(___('menus.manage_activities')); ?></a></li>
                    <li><a href="<?php echo e(route('crm.followup')); ?>"><?php echo e(___('menus.followup_calendar')); ?></a></li>
                    <li><a href="<?php echo e(route('crm.activity')); ?>"><?php echo e(___('menus.activity_timeline')); ?></a></li>
                    <li><a href="<?php echo e(route('crm.notes')); ?>"><?php echo e(___('menus.customer_notes')); ?></a></li>
                    <li><a href="<?php echo e(route('crm.communication')); ?>"><?php echo e(___('menus.communication_history')); ?></a></li>
                </ul>
            </li>
            <?php endif; ?>

            <?php if($marketingVisible): ?>
            <li class="tv-nav-section"><span><?php echo e(___('menus.marketing')); ?></span></li>
            <?php endif; ?>

            <?php if(hasPermission('coupon_read')): ?>
            <li> <a href="<?php echo e(route('coupon.index')); ?>" class="<?php echo e(request()->is('coupons*') ? 'mm-active' : ''); ?>" aria-expanded="true"> <i class="icon-tag"></i> <span class="nav-text"><?php echo e(___('menus.coupons')); ?></span> </a> </li>
            <?php endif; ?>

            <?php if(hasPermission('review_read')): ?>
            <li> <a href="<?php echo e(route('review.index')); ?>" class="<?php echo e(request()->is('reviews*') ? 'mm-active' : ''); ?>" aria-expanded="true"> <i class="icon-star"></i> <span class="nav-text">Reviews</span> </a> </li>
            <?php endif; ?>

            <?php if(hasPermission('campaign_read')): ?>
            <li> <a href="<?php echo e(route('campaign.index')); ?>" class="<?php echo e(request()->is('campaigns*') ? 'mm-active' : ''); ?>" aria-expanded="true"> <i class="icon-speech"></i> <span class="nav-text"><?php echo e(___('menus.campaigns')); ?></span> </a> </li>
            <li> <a href="<?php echo e(route('newsletter-subscriber.index')); ?>" class="<?php echo e(request()->is('marketing/newsletter-subscribers*') ? 'mm-active' : ''); ?>" aria-expanded="true"> <i class="icon-envelope"></i> <span class="nav-text"><?php echo e(___('menus.newsletter_subscribers')); ?></span> </a> </li>
            <?php endif; ?>

            <?php if($operationsVisible): ?>
            <li class="tv-nav-section"><span><?php echo e(___('menus.operations')); ?></span></li>
            <?php endif; ?>

            <?php if(hasPermission('tour_read')): ?>
            
            <li class="<?php echo e(request()->is('tour*','packages*') ? 'mm-active' : ''); ?>">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-map"></i> <span class="nav-text"><?php echo e(___('menus.tour_management')); ?></span> </a>
                <ul aria-expanded="false">
                    <li><a href="<?php echo e(route('package.index')); ?>"><?php echo e(___('menus.package_list')); ?></a></li>
                    <li><a href="<?php echo e(route('tour.category')); ?>"><?php echo e(___('menus.package_category')); ?></a></li>
                    <li><a href="<?php echo e(route('booking.index')); ?>"><?php echo e(___('menus.package_booking')); ?></a></li>
                    <li><a href="<?php echo e(route('tour.schedule')); ?>"><?php echo e(___('menus.tour_schedule')); ?></a></li>
                    <li><a href="<?php echo e(route('tour.schedule.index')); ?>"><?php echo e(___('menus.manage_schedules')); ?></a></li>
                    <li><a href="<?php echo e(route('tour.guides')); ?>"><?php echo e(___('menus.tour_guides')); ?></a></li>
                    <li><a href="<?php echo e(route('tour.guideAssignments')); ?>"><?php echo e(___('menus.guide_assignments')); ?></a></li>
                    <li><a href="<?php echo e(route('tour.reports')); ?>"><?php echo e(___('menus.package_reports')); ?></a></li>
                </ul>
            </li>
            <?php endif; ?>

            <?php if(hasPermission('visa_read')): ?>
            
            <li class="<?php echo e(request()->is('visa*') ? 'mm-active' : ''); ?>">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-doc"></i> <span class="nav-text"><?php echo e(___('menus.visa_management')); ?></span> </a>
                <ul aria-expanded="false">
                    <li><a href="<?php echo e(route('visa.dashboard')); ?>"><?php echo e(___('menus.visa_dashboard')); ?></a></li>
                    <li><a href="<?php echo e(route('visa.applications')); ?>"><?php echo e(___('menus.applications')); ?></a></li>
                    <li><a href="<?php echo e(route('visa.appointment')); ?>"><?php echo e(___('menus.embassy_appointment')); ?></a></li>
                    <li><a href="<?php echo e(route('visa.documents')); ?>"><?php echo e(___('menus.visa_documents')); ?></a></li>
                    <li><a href="<?php echo e(route('visa.tracking')); ?>"><?php echo e(___('menus.status_tracking')); ?></a></li>
                    <li><a href="<?php echo e(route('visa.expiry')); ?>"><?php echo e(___('menus.expiry_management')); ?></a></li>
                    <li><a href="<?php echo e(route('visa.reports')); ?>"><?php echo e(___('menus.visa_reports')); ?></a></li>
                </ul>
            </li>
            <?php endif; ?>

            <?php if(hasPermission('hajj_read')): ?>
            
            <li class="<?php echo e(request()->is('hajj*') ? 'mm-active' : ''); ?>">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-compass"></i> <span class="nav-text"><?php echo e(___('menus.hajj_umrah')); ?></span> </a>
                <ul aria-expanded="false">
                    <li><a href="<?php echo e(route('hajj.index')); ?>"><?php echo e(___('menus.manage_packages')); ?></a></li>
                    <li><a href="<?php echo e(route('hajj.pilgrim.index')); ?>"><?php echo e(___('menus.manage_pilgrims')); ?></a></li>
                    <li><a href="<?php echo e(route('hajj.hotel')); ?>"><?php echo e(___('menus.hotel_allocation')); ?></a></li>
                    <li><a href="<?php echo e(route('hajj.flight')); ?>"><?php echo e(___('menus.flight_allocation')); ?></a></li>
                    <li><a href="<?php echo e(route('hajj.groups')); ?>"><?php echo e(___('menus.group_management')); ?></a></li>
                    <li><a href="<?php echo e(route('hajj.payments')); ?>"><?php echo e(___('menus.payment_management')); ?></a></li>
                    <li><a href="<?php echo e(route('hajj.documents')); ?>"><?php echo e(___('menus.document_verification')); ?></a></li>
                    <li><a href="<?php echo e(route('hajj.reports')); ?>"><?php echo e(___('menus.reports')); ?></a></li>
                </ul>
            </li>
            <?php endif; ?>

            <?php if(hasPermission('hotel_read')): ?>
            
            <li class="<?php echo e(request()->is('hotel*') ? 'mm-active' : ''); ?>">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-home"></i> <span class="nav-text"><?php echo e(___('menus.hotel_management')); ?></span> </a>
                <ul aria-expanded="false">
                    <li><a href="<?php echo e(route('hotel.index')); ?>"><?php echo e(___('menus.hotels')); ?></a></li>
                    <li><a href="<?php echo e(route('hotel.room.index')); ?>"><?php echo e(___('menus.manage_rooms')); ?></a></li>
                    <li><a href="<?php echo e(route('hotel.booking.index')); ?>"><?php echo e(___('menus.manage_bookings')); ?></a></li>
                    <li><a href="<?php echo e(route('hotel.availability')); ?>"><?php echo e(___('menus.availability')); ?></a></li>
                    <li><a href="<?php echo e(route('hotel.vouchers')); ?>"><?php echo e(___('menus.vouchers')); ?></a></li>
                    <li><a href="<?php echo e(route('hotel.reports')); ?>"><?php echo e(___('menus.reports')); ?></a></li>
                </ul>
            </li>
            <?php endif; ?>

            <?php if(hasPermission('transport_read')): ?>
            
            <li class="<?php echo e(request()->is('transport*') ? 'mm-active' : ''); ?>">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-rocket"></i> <span class="nav-text"><?php echo e(___('menus.transport')); ?></span> </a>
                <ul aria-expanded="false">
                    <li><a href="<?php echo e(route('transport.index')); ?>"><?php echo e(___('menus.manage_bookings')); ?></a></li>
                    <li><a href="<?php echo e(route('transport.bus')); ?>"><?php echo e(___('menus.bus_booking')); ?></a></li>
                    <li><a href="<?php echo e(route('transport.train')); ?>"><?php echo e(___('menus.train_booking')); ?></a></li>
                    <li><a href="<?php echo e(route('transport.launch')); ?>"><?php echo e(___('menus.launch_booking')); ?></a></li>
                    <li><a href="<?php echo e(route('transport.car')); ?>"><?php echo e(___('menus.car_rental')); ?></a></li>
                    <li><a href="<?php echo e(route('transport.airport')); ?>"><?php echo e(___('menus.airport_transfer')); ?></a></li>
                    <li><a href="<?php echo e(route('transport.driver.index')); ?>"><?php echo e(___('menus.manage_drivers')); ?></a></li>
                    <li><a href="<?php echo e(route('transport.vehicle-category.index')); ?>"><?php echo e(___('menus.vehicle_categories')); ?></a></li>
                    <li><a href="<?php echo e(route('transport.reports')); ?>"><?php echo e(___('menus.transport_reports')); ?></a></li>
                </ul>
            </li>
            <?php endif; ?>

            <?php if(hasPermission('flight_read')): ?>
            
            <li class="<?php echo e(request()->is('flight*') ? 'mm-active' : ''); ?>">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-plane"></i> <span class="nav-text"><?php echo e(___('menus.flight_management')); ?></span> </a>
                <ul aria-expanded="false">
                    <li><a href="<?php echo e(route('flight.index')); ?>"><?php echo e(___('menus.flight_bookings')); ?></a></li>
                    <li><a href="<?php echo e(route('flight.reissue')); ?>"><?php echo e(___('menus.reissue_requests')); ?></a></li>
                    <li><a href="<?php echo e(route('flight.cancellation')); ?>"><?php echo e(___('menus.cancellation_requests')); ?></a></li>
                    <li><a href="<?php echo e(route('flight.refund')); ?>"><?php echo e(___('menus.refund_tracking')); ?></a></li>
                    <li><a href="<?php echo e(route('flight.reports')); ?>"><?php echo e(___('menus.reports')); ?></a></li>
                </ul>
            </li>
            <?php endif; ?>

            <?php if($servicesVisible): ?>
            <li class="tv-nav-section"><span><?php echo e(___('menus.services')); ?></span></li>
            <?php endif; ?>

            <?php if(hasPermission('insurance_read')): ?>
            <li> <a href="<?php echo e(route('insurance.index')); ?>" class="<?php echo e(request()->is('insurances*') ? 'mm-active' : ''); ?>" aria-expanded="true"> <i class="icon-shield"></i> <span class="nav-text"><?php echo e(___('menus.travel_insurance')); ?></span> </a> </li>
            <?php endif; ?>
            <?php if(hasPermission('student_service_read')): ?>
            <li> <a href="<?php echo e(route('student-service.index')); ?>" class="<?php echo e(request()->is('student-services*') ? 'mm-active' : ''); ?>" aria-expanded="true"> <i class="icon-graduation"></i> <span class="nav-text"><?php echo e(___('menus.student_consultancy')); ?></span> </a> </li>
            <?php endif; ?>
            <?php if(hasPermission('medical_tour_read')): ?>
            <li> <a href="<?php echo e(route('medical-tour.index')); ?>" class="<?php echo e(request()->is('medical-tours*') ? 'mm-active' : ''); ?>" aria-expanded="true"> <i class="icon-heart"></i> <span class="nav-text"><?php echo e(___('menus.medical_tourism')); ?></span> </a> </li>
            <?php endif; ?>
            <?php if(hasPermission('corporate_travel_read')): ?>
            <li> <a href="<?php echo e(route('corporate-travel.index')); ?>" class="<?php echo e(request()->is('corporate-travels*') ? 'mm-active' : ''); ?>" aria-expanded="true"> <i class="icon-briefcase"></i> <span class="nav-text"><?php echo e(___('menus.corporate_travel')); ?></span> </a> </li>
            <?php endif; ?>
            <?php if(hasPermission('event_tour_read')): ?>
            <li> <a href="<?php echo e(route('event-tour.index')); ?>" class="<?php echo e(request()->is('event-tours*') && ! request()->is('event-tours/bookings*') ? 'mm-active' : ''); ?>" aria-expanded="true"> <i class="icon-calender"></i> <span class="nav-text"><?php echo e(___('menus.events_conference')); ?></span> </a> </li>
            <li> <a href="<?php echo e(route('event-tour.booking.index')); ?>" class="<?php echo e(request()->is('event-tours/bookings*') ? 'mm-active' : ''); ?>" aria-expanded="true"> <i class="fa-solid fa-calendar-check"></i> <span class="nav-text"><?php echo e(___('label.event_bookings')); ?></span> </a> </li>
            <?php endif; ?>

            <?php if($financeVisible): ?>
            <li class="tv-nav-section"><span><?php echo e(___('menus.finance')); ?></span></li>
            <?php endif; ?>

            <?php if(hasPermission('accounting_read')): ?>
            
            <li class="<?php echo e(request()->is('accounting*') ? 'mm-active' : ''); ?>">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-calculator"></i> <span class="nav-text"><?php echo e(___('menus.accounting')); ?></span> </a>
                <ul aria-expanded="false">
                    <li><a href="<?php echo e(route('acc.dashboard')); ?>"><?php echo e(___('menus.dashboard')); ?></a></li>
                    <li><a href="<?php echo e(route('acc.index')); ?>"><?php echo e(___('menus.manage_accounts')); ?></a></li>
                    <li><a href="<?php echo e(route('acc.txn.index')); ?>"><?php echo e(___('menus.manage_transactions')); ?></a></li>
                    <li><a href="<?php echo e(route('acc.income')); ?>"><?php echo e(___('menus.income')); ?></a></li>
                    <li><a href="<?php echo e(route('acc.expenses')); ?>"><?php echo e(___('menus.expenses')); ?></a></li>
                    <li><a href="<?php echo e(route('acc.journal')); ?>"><?php echo e(___('menus.journal_entries')); ?></a></li>
                    <li><a href="<?php echo e(route('acc.cashbook')); ?>"><?php echo e(___('menus.cash_book')); ?></a></li>
                    <li><a href="<?php echo e(route('acc.bankbook')); ?>"><?php echo e(___('menus.bank_book')); ?></a></li>
                    <li><a href="<?php echo e(route('acc.ledger')); ?>"><?php echo e(___('menus.ledger')); ?></a></li>
                    <li><a href="<?php echo e(route('acc.trial')); ?>"><?php echo e(___('menus.trial_balance')); ?></a></li>
                    <li><a href="<?php echo e(route('acc.pl')); ?>"><?php echo e(___('menus.profit_loss')); ?></a></li>
                    <li><a href="<?php echo e(route('acc.balance')); ?>"><?php echo e(___('menus.balance_sheet')); ?></a></li>
                    <li><a href="<?php echo e(route('acc.invoice.index')); ?>"><?php echo e(___('menus.manage_invoices')); ?></a></li>
                    <li><a href="<?php echo e(route('acc.receipt.index')); ?>"><?php echo e(___('menus.manage_receipts')); ?></a></li>
                    <li><a href="<?php echo e(route('acc.refunds')); ?>"><?php echo e(___('menus.refunds')); ?></a></li>
                    <li><a href="<?php echo e(route('acc.tax')); ?>"><?php echo e(___('menus.tax_reports')); ?></a></li>
                </ul>
            </li>
            <?php endif; ?>

            <?php if(hasPermission('supplier_read')): ?>
            
            <li class="<?php echo e(request()->is('supplier*') ? 'mm-active' : ''); ?>">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-briefcase"></i> <span class="nav-text"><?php echo e(___('menus.suppliers')); ?></span> </a>
                <ul aria-expanded="false">
                    <li><a href="<?php echo e(route('supplier.index')); ?>"><?php echo e(___('menus.manage_suppliers')); ?></a></li>
                    <li><a href="<?php echo e(route('sup.airlines')); ?>"><?php echo e(___('menus.airlines')); ?></a></li>
                    <li><a href="<?php echo e(route('sup.hotels')); ?>"><?php echo e(___('menus.hotels')); ?></a></li>
                    <li><a href="<?php echo e(route('sup.transport')); ?>"><?php echo e(___('menus.transport_vendors')); ?></a></li>
                    <li><a href="<?php echo e(route('sup.visa')); ?>"><?php echo e(___('menus.visa_partners')); ?></a></li>
                    <li><a href="<?php echo e(route('sup.contracts')); ?>"><?php echo e(___('menus.contracts')); ?></a></li>
                    <li><a href="<?php echo e(route('sup.ledger')); ?>"><?php echo e(___('menus.supplier_ledger')); ?></a></li>
                    <li><a href="<?php echo e(route('sup.reports')); ?>"><?php echo e(___('menus.supplier_reports')); ?></a></li>
                </ul>
            </li>
            <?php endif; ?>

            
            <?php if(hasPermission('agent_finance_read')): ?>
            <li class="<?php echo e(request()->is('agent-finance*') ? 'mm-active' : ''); ?>">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-wallet"></i> <span class="nav-text"><?php echo e(___('menus.agent_finance')); ?></span> </a>
                <ul aria-expanded="false">
                    <li><a href="<?php echo e(route('agent.list')); ?>"><?php echo e(___('menus.agents')); ?></a></li>
                    <li><a href="<?php echo e(route('agent.commission.index')); ?>"><?php echo e(___('menus.commissions')); ?></a></li>
                    <li><a href="<?php echo e(route('agent.invoice.index')); ?>"><?php echo e(___('menus.agent_invoices')); ?></a></li>
                    <li><a href="<?php echo e(route('agent.withdrawal.index')); ?>">Payouts</a></li>
                </ul>
            </li>
            <?php endif; ?>

            <?php if($workVisible): ?>
            <li class="tv-nav-section"><span><?php echo e(___('menus.work')); ?></span></li>
            <?php endif; ?>

            <?php if(hasPermission('task_read')): ?>
            
            <li class="<?php echo e(request()->is('task*') ? 'mm-active' : ''); ?>">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-check"></i> <span class="nav-text"><?php echo e(___('menus.task_management')); ?></span> </a>
                <ul aria-expanded="false">
                    <li><a href="<?php echo e(route('task.index')); ?>"><?php echo e(___('menus.manage_tasks')); ?></a></li>
                    <li><a href="<?php echo e(route('task.projects')); ?>"><?php echo e(___('menus.projects')); ?></a></li>
                    <li><a href="<?php echo e(route('task.assignments')); ?>"><?php echo e(___('menus.assignments')); ?></a></li>
                    <li><a href="<?php echo e(route('task.deadlines')); ?>"><?php echo e(___('menus.deadlines')); ?></a></li>
                    <li><a href="<?php echo e(route('task.calendar')); ?>"><?php echo e(___('menus.calendar_view')); ?></a></li>
                    <li><a href="<?php echo e(route('task.kanban')); ?>"><?php echo e(___('menus.kanban_board')); ?></a></li>
                </ul>
            </li>
            <?php endif; ?>

            <?php if(hasPermission('support_read')): ?>
            
            <li class="<?php echo e(request()->is('support*') ? 'mm-active' : ''); ?>">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-support"></i> <span class="nav-text"><?php echo e(___('menus.support_center')); ?></span> </a>
                <ul aria-expanded="false">
                    <li><a href="<?php echo e(route('support.index')); ?>"><?php echo e(___('menus.manage_tickets')); ?></a></li>
                    <li><a href="<?php echo e(route('support.announcement.index')); ?>"><?php echo e(___('menus.manage_announcements')); ?></a></li>
                    <li><a href="<?php echo e(route('support.kb')); ?>"><?php echo e(___('menus.knowledge_base')); ?></a></li>
                    <li><a href="<?php echo e(route('support.kb.index')); ?>"><?php echo e(___('menus.manage_kb_articles')); ?></a></li>
                </ul>
            </li>
            <?php endif; ?>

            <?php if($reportsVisible): ?>
            <li class="tv-nav-section"><span><?php echo e(___('menus.reports')); ?></span></li>
            <?php endif; ?>

            <?php if(hasPermission('report_read')): ?>
            
            <li class="<?php echo e(request()->is('reports-center*') ? 'mm-active' : ''); ?>">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-graph"></i> <span class="nav-text"><?php echo e(___('menus.reporting_center')); ?></span> </a>
                <ul aria-expanded="false">
                    <li><a href="<?php echo e(route('report.sales')); ?>"><?php echo e(___('menus.sales_reports')); ?></a></li>
                    <li><a href="<?php echo e(route('report.visa')); ?>"><?php echo e(___('menus.visa_reports')); ?></a></li>
                    <li><a href="<?php echo e(route('report.package')); ?>"><?php echo e(___('menus.package_reports')); ?></a></li>
                    <li><a href="<?php echo e(route('report.flight')); ?>"><?php echo e(___('menus.flight_reports')); ?></a></li>
                    <li><a href="<?php echo e(route('report.hotel')); ?>"><?php echo e(___('menus.hotel_reports')); ?></a></li>
                    <li><a href="<?php echo e(route('report.agent')); ?>"><?php echo e(___('menus.agent_reports')); ?></a></li>
                    <li><a href="<?php echo e(route('report.customer')); ?>"><?php echo e(___('menus.customer_reports')); ?></a></li>
                    <li><a href="<?php echo e(route('report.financial')); ?>"><?php echo e(___('menus.financial_reports')); ?></a></li>
                    <li><a href="<?php echo e(route('report.custom')); ?>"><?php echo e(___('menus.custom_reports')); ?></a></li>
                </ul>
            </li>
            <?php endif; ?>

            <?php if($contentHrVisible): ?>
            <li class="tv-nav-section"><span><?php echo e(___('menus.content_hr')); ?></span></li>
            <?php endif; ?>

            <?php if(hasPermission('cms_read') || hasPermission('general_settings_read')): ?>
            
            <li class="<?php echo e(request()->is('cms*') || request()->routeIs('settings.appearance.index') ? 'mm-active' : ''); ?>">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-screen-desktop"></i> <span class="nav-text"><?php echo e(___('menus.cms')); ?></span> </a>
                <ul aria-expanded="false">
                    <?php if(hasPermission('cms_read')): ?>
                    <li><a href="<?php echo e(route('cms.index')); ?>"><?php echo e(___('menus.manage_pages')); ?></a></li>
                    <li><a href="<?php echo e(route('cms.blog.index')); ?>"><?php echo e(___('menus.manage_blogs')); ?></a></li>
                    <li><a href="<?php echo e(route('cms.slider.index')); ?>"><?php echo e(___('menus.manage_sliders')); ?></a></li>
                    <li><a href="<?php echo e(route('cms.testimonial.index')); ?>"><?php echo e(___('menus.manage_testimonials')); ?></a></li>
                    <li><a href="<?php echo e(route('cms.gallery.index')); ?>"><?php echo e(___('menus.manage_gallery')); ?></a></li>
                    <li><a href="<?php echo e(route('cms.faq.index')); ?>"><?php echo e(___('menus.manage_faqs')); ?></a></li>
                    <li><a href="<?php echo e(route('cms.menu.index')); ?>"><?php echo e(___('menus.manage_menus')); ?></a></li>
                    
                    <li><a href="<?php echo e(route('cms.visa-service.index')); ?>"><?php echo e(___('menus.visa_services')); ?></a></li>
                    <li><a href="<?php echo e(route('cms.flight-route.index')); ?>"><?php echo e(___('menus.flight_fare_deals')); ?></a></li>
                    <li><a href="<?php echo e(route('cms.transport-service.index')); ?>"><?php echo e(___('menus.transport_services')); ?></a></li>
                    <li><a href="<?php echo e(route('cms.job-opening.index')); ?>"><?php echo e(___('menus.job_openings')); ?></a></li>
                    <li><a href="<?php echo e(route('cms.content-block.index')); ?>"><?php echo e(___('menus.content_blocks')); ?></a></li>
                    <li><a href="<?php echo e(route('cms.seo')); ?>"><?php echo e(___('menus.seo_settings')); ?></a></li>
                    <?php endif; ?>
                    <?php if(hasPermission('general_settings_read')): ?>
                    <li><a href="<?php echo e(route('settings.appearance.index')); ?>"><?php echo e(___('menus.appearance')); ?></a></li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>

            
            <?php if(hasPermission('hr_read')): ?>
            <li class="<?php echo e(request()->is('hr*') ? 'mm-active' : ''); ?>">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-badge"></i> <span class="nav-text"><?php echo e(___('menus.human_resources')); ?></span> </a>
                <ul aria-expanded="false">
                    <li><a href="<?php echo e(route('hr.attendance.index')); ?>"><?php echo e(___('menus.staff_attendance')); ?></a></li>
                    <li><a href="<?php echo e(route('hr.leave.index')); ?>"><?php echo e(___('menus.leave_requests')); ?></a></li>
                    <li><a href="<?php echo e(route('hr.payslip.index')); ?>"><?php echo e(___('menus.payslips')); ?></a></li>
                </ul>
            </li>
            <?php endif; ?>

            

            <?php if($systemVisible): ?>
            <li class="tv-nav-section"><span><?php echo e(___('menus.system')); ?></span></li>
            <?php endif; ?>

            <?php if(hasPermission('branch_read')): ?>
            <li> <a href="<?php echo e(route('branch.index')); ?>" class="<?php echo e(request()->is('branches*') ? 'mm-active' : ''); ?>" aria-expanded="true"> <i class="icon-location-pin"></i> <span class="nav-text"><?php echo e(___('menus.branches')); ?></span> </a> </li>
            <?php endif; ?>



            <?php if(hasPermission('activity_logs_read')): ?>
            <li> <a href="<?php echo e(route('activity.logs.index')); ?>" aria-expanded="true"> <i class="icon-list"></i> <span class="nav-text"><?php echo e(___('menus.activity_logs')); ?></span> </a> </li>
            <?php endif; ?>

            <?php if(hasPermission('login_activity_read')): ?>
            <li> <a href="<?php echo e(route('login.activity.index')); ?>" aria-expanded="false"> <i class="icon-list"></i> <span class="nav-text"><?php echo e(___('menus.login_activity')); ?></span> </a> </li>
            <?php endif; ?>

            <?php if(hasPermission('language_read')): ?>
            <li> <a href="<?php echo e(route('language.index')); ?>" aria-expanded="true"> <i class="icon-flag"></i> <span class="nav-text"><?php echo e(___('menus.language')); ?></span> </a> </li>
            <?php endif; ?>

            <?php if($settingsVisible): ?>
            <li>
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"><i class="icon-wrench"></i><span class="nav-text"><?php echo e(___('menus.settings')); ?></span></a>
                <ul aria-expanded="false">

                    <?php if(hasPermission('general_settings_read')): ?>
                    <li> <a href="<?php echo e(route('settings.general.index')); ?>"><?php echo e(___('menus.general_settings')); ?></a> </li>
                    <li> <a href="<?php echo e(route('settings.ai.index')); ?>"><?php echo e(___('menus.ai_settings')); ?></a> </li>
                    <li> <a href="<?php echo e(route('settings.flight-api.index')); ?>"><?php echo e(___('menus.flight_api')); ?></a> </li>
                    <li> <a href="<?php echo e(route('settings.whatsapp.index')); ?>"><?php echo e(___('menus.whatsapp_settings')); ?></a> </li>
                    <li> <a href="<?php echo e(route('settings.loyalty.index')); ?>"><?php echo e(___('menus.loyalty_settings')); ?></a> </li>
                    <?php endif; ?>

                    <?php if(hasPermission('mail_settings_read')): ?>
                    <li> <a href="<?php echo e(route('settings.mail')); ?>"><?php echo e(___('menus.mail_setting')); ?></a> </li>
                    <?php endif; ?>

                    <?php if(hasPermission('payment_settings_read')): ?>
                    <li> <a href="<?php echo e(route('settings.payment.index')); ?>"><?php echo e(___('menus.payment_gateways')); ?></a> </li>
                    <?php endif; ?>

                    <?php if(hasPermission('sms_settings_read')): ?>
                    <li> <a href="<?php echo e(route('settings.sms.index')); ?>"><?php echo e(___('menus.sms_settings')); ?></a> </li>
                    <?php endif; ?>

                    <?php if(hasPermission('general_settings_read')): ?>
                    <li> <a href="<?php echo e(route('settings.push.index')); ?>"><?php echo e(___('menus.push_notifications')); ?></a> </li>
                    <?php endif; ?>

                    <?php if(hasPermission('general_settings_read')): ?>
                    <li> <a href="<?php echo e(route('settings.api.security.index')); ?>"><?php echo e(___('menus.api_security')); ?></a> </li>
                    <?php endif; ?>

                    <?php if(hasPermission('general_settings_read')): ?>
                    <li> <a href="<?php echo e(route('settings.social-links.index')); ?>">Social links</a> </li>
                    <?php endif; ?>

                    <?php if(hasPermission('recaptcha_settings_read')): ?>
                    <li> <a href="<?php echo e(route('settings.recaptcha.index')); ?>"><?php echo e(___('menus.recaptcha')); ?></a> </li>
                    <?php endif; ?>

                    <?php if(hasPermission('general_settings_read')): ?>
                    <li> <a href="<?php echo e(route('settings.social.login.index')); ?>"><?php echo e(___('menus.social_login_settings')); ?></a> </li>
                    <?php endif; ?>

                </ul>
            </li>
            <?php endif; ?>

        </ul>
    </div>
</aside>
<?php /**PATH /Volumes/Home/Personal Work/EastBound/Platform/WebSourceCode/resources/views/backend/partials/sidebar.blade.php ENDPATH**/ ?>