<aside class="tv-sidebar j-custom-style" id="backendSidebar" aria-label="{{ ___('menus.main') }}">
    @php
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
    @endphp
    <div class="tv-sidebar-scroll">
        <ul class="tv-sidebar-menu" id="backendSidebarMenu">

            @if($mainVisible)
            <li class="tv-nav-section"><span>{{ ___('menus.main') }}</span></li>
            @endif

            @if( hasPermission('dashboard_read'))
            <li> <a href="{{route('dashboard')}}" aria-expanded="true"> <i class="icon-chart"></i> <span class="nav-text">{{___('menus.dashboard') }}</span> </a> </li>
            @endif

            @if(hasPermission('user_read') || hasPermission('role_read'))
            <li class="{{ (request()->is('*user*','*role*')) ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="true"> <i class="icon-people"></i> <span class="nav-text">{{___('menus.user_roles')}}</span> </a>
                <ul aria-expanded="false">
                    @if(hasPermission('user_read'))
                    <li> <a href="{{ route('user.index') }}" class="{{ (request()->is('*user*')) ? 'mm-active' : '' }}">{{___('menus.users')}}</a> </li>
                    @endif
                    @if(hasPermission('role_read'))
                    <li> <a href="{{ route('role.index') }}" class="{{ (request()->is('*role*')) ? 'mm-active' : '' }}">{{___('menus.roles')}}</a> </li>
                    @endif
                </ul>
            </li>
            @endif

            @if(hasPermission('todo_read'))
            <li> <a href="{{ route('todo.index') }}" aria-expanded="true"> <i class="icon-notebook"></i> <span class="nav-text">{{___('menus.todo_list')}}</span> </a> </li>
            @endif

            @if($bookingsCrmVisible)
            <li class="tv-nav-section"><span>{{ ___('menus.bookings_crm') }}</span></li>
            @endif

            @if(hasPermission('customer_read'))
            {{-- Customers (new module) --}}
            <li> <a href="{{ route('customer.index') }}" class="{{ request()->is('customers*') ? 'mm-active' : '' }}" aria-expanded="true"> <i class="icon-user-following"></i> <span class="nav-text">{{ ___('menus.customers') }}</span> </a> </li>
            @endif

            {{-- Customer Records (travelers + passports) --}}
            @if(hasPermission('customer_read'))
            <li class="{{ request()->is('customer/travelers*') || request()->is('customer/passports*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-docs"></i> <span class="nav-text">{{ ___('menus.customer_records') }}</span> </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('customer.traveler.index') }}">{{ ___('menus.travelers') }}</a></li>
                    <li><a href="{{ route('customer.passport.index') }}">{{ ___('menus.passports') }}</a></li>
                </ul>
            </li>
            @endif

            {{-- ============ Travel ERP (dummy UI pages) ============ --}}

            @if(hasPermission('crm_read'))
            {{-- CRM --}}
            <li class="{{ request()->is('crm*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-bubbles"></i> <span class="nav-text">{{ ___('menus.crm') }}</span> </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('crm.leads') }}" class="{{ request()->is('crm/leads') ? 'mm-active' : '' }}">{{ ___('menus.leads') }}</a></li>
                    <li><a href="{{ route('crm.contact-messages.index') }}" class="{{ request()->is('crm/contact-messages*') ? 'mm-active' : '' }}">{{ ___('menus.contact_messages') }}</a></li>
                    <li><a href="{{ route('crm.job-applications.index') }}" class="{{ request()->is('crm/job-applications*') ? 'mm-active' : '' }}">{{ ___('menus.job_applications') }}</a></li>
                    <li><a href="{{ route('crm.activity.index') }}">{{ ___('menus.manage_activities') }}</a></li>
                    <li><a href="{{ route('crm.followup') }}">{{ ___('menus.followup_calendar') }}</a></li>
                    <li><a href="{{ route('crm.activity') }}">{{ ___('menus.activity_timeline') }}</a></li>
                    <li><a href="{{ route('crm.notes') }}">{{ ___('menus.customer_notes') }}</a></li>
                    <li><a href="{{ route('crm.communication') }}">{{ ___('menus.communication_history') }}</a></li>
                </ul>
            </li>
            @endif

            @if($marketingVisible)
            <li class="tv-nav-section"><span>{{ ___('menus.marketing') }}</span></li>
            @endif

            @if(hasPermission('coupon_read'))
            <li> <a href="{{ route('coupon.index') }}" class="{{ request()->is('coupons*') ? 'mm-active' : '' }}" aria-expanded="true"> <i class="icon-tag"></i> <span class="nav-text">{{ ___('menus.coupons') }}</span> </a> </li>
            @endif

            @if(hasPermission('review_read'))
            <li> <a href="{{ route('review.index') }}" class="{{ request()->is('reviews*') ? 'mm-active' : '' }}" aria-expanded="true"> <i class="icon-star"></i> <span class="nav-text">Reviews</span> </a> </li>
            @endif

            @if(hasPermission('campaign_read'))
            <li> <a href="{{ route('campaign.index') }}" class="{{ request()->is('campaigns*') ? 'mm-active' : '' }}" aria-expanded="true"> <i class="icon-speech"></i> <span class="nav-text">{{ ___('menus.campaigns') }}</span> </a> </li>
            <li> <a href="{{ route('newsletter-subscriber.index') }}" class="{{ request()->is('marketing/newsletter-subscribers*') ? 'mm-active' : '' }}" aria-expanded="true"> <i class="icon-envelope"></i> <span class="nav-text">{{ ___('menus.newsletter_subscribers') }}</span> </a> </li>
            @endif

            @if($operationsVisible)
            <li class="tv-nav-section"><span>{{ ___('menus.operations') }}</span></li>
            @endif

            @if(hasPermission('tour_read'))
            {{-- Tour Management --}}
            <li class="{{ request()->is('tour*','packages*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-map"></i> <span class="nav-text">{{ ___('menus.tour_management') }}</span> </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('package.index') }}">{{ ___('menus.package_list') }}</a></li>
                    <li><a href="{{ route('tour.category') }}">{{ ___('menus.package_category') }}</a></li>
                    <li><a href="{{ route('booking.index') }}">{{ ___('menus.package_booking') }}</a></li>
                    <li><a href="{{ route('tour.schedule') }}">{{ ___('menus.tour_schedule') }}</a></li>
                    <li><a href="{{ route('tour.schedule.index') }}">{{ ___('menus.manage_schedules') }}</a></li>
                    <li><a href="{{ route('tour.guides') }}">{{ ___('menus.tour_guides') }}</a></li>
                    <li><a href="{{ route('tour.guideAssignments') }}">{{ ___('menus.guide_assignments') }}</a></li>
                    <li><a href="{{ route('tour.reports') }}">{{ ___('menus.package_reports') }}</a></li>
                </ul>
            </li>
            @endif

            @if(hasPermission('visa_read'))
            {{-- Visa Management --}}
            <li class="{{ request()->is('visa*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-doc"></i> <span class="nav-text">{{ ___('menus.visa_management') }}</span> </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('visa.dashboard') }}">{{ ___('menus.visa_dashboard') }}</a></li>
                    <li><a href="{{ route('visa.applications') }}">{{ ___('menus.applications') }}</a></li>
                    <li><a href="{{ route('visa.appointment') }}">{{ ___('menus.embassy_appointment') }}</a></li>
                    <li><a href="{{ route('visa.documents') }}">{{ ___('menus.visa_documents') }}</a></li>
                    <li><a href="{{ route('visa.tracking') }}">{{ ___('menus.status_tracking') }}</a></li>
                    <li><a href="{{ route('visa.expiry') }}">{{ ___('menus.expiry_management') }}</a></li>
                    <li><a href="{{ route('visa.reports') }}">{{ ___('menus.visa_reports') }}</a></li>
                </ul>
            </li>
            @endif

            @if(hasPermission('hajj_read'))
            {{-- Hajj & Umrah --}}
            <li class="{{ request()->is('hajj*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-compass"></i> <span class="nav-text">{{ ___('menus.hajj_umrah') }}</span> </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('hajj.index') }}">{{ ___('menus.manage_packages') }}</a></li>
                    <li><a href="{{ route('hajj.pilgrim.index') }}">{{ ___('menus.manage_pilgrims') }}</a></li>
                    <li><a href="{{ route('hajj.hotel') }}">{{ ___('menus.hotel_allocation') }}</a></li>
                    <li><a href="{{ route('hajj.flight') }}">{{ ___('menus.flight_allocation') }}</a></li>
                    <li><a href="{{ route('hajj.groups') }}">{{ ___('menus.group_management') }}</a></li>
                    <li><a href="{{ route('hajj.payments') }}">{{ ___('menus.payment_management') }}</a></li>
                    <li><a href="{{ route('hajj.documents') }}">{{ ___('menus.document_verification') }}</a></li>
                    <li><a href="{{ route('hajj.reports') }}">{{ ___('menus.reports') }}</a></li>
                </ul>
            </li>
            @endif

            @if(hasPermission('hotel_read'))
            {{-- Hotel Management --}}
            <li class="{{ request()->is('hotel*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-home"></i> <span class="nav-text">{{ ___('menus.hotel_management') }}</span> </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('hotel.index') }}">{{ ___('menus.hotels') }}</a></li>
                    <li><a href="{{ route('hotel.room.index') }}">{{ ___('menus.manage_rooms') }}</a></li>
                    <li><a href="{{ route('hotel.booking.index') }}">{{ ___('menus.manage_bookings') }}</a></li>
                    <li><a href="{{ route('hotel.availability') }}">{{ ___('menus.availability') }}</a></li>
                    <li><a href="{{ route('hotel.vouchers') }}">{{ ___('menus.vouchers') }}</a></li>
                    <li><a href="{{ route('hotel.reports') }}">{{ ___('menus.reports') }}</a></li>
                </ul>
            </li>
            @endif

            @if(hasPermission('transport_read'))
            {{-- Transport Management --}}
            <li class="{{ request()->is('transport*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-rocket"></i> <span class="nav-text">{{ ___('menus.transport') }}</span> </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('transport.index') }}">{{ ___('menus.manage_bookings') }}</a></li>
                    <li><a href="{{ route('transport.bus') }}">{{ ___('menus.bus_booking') }}</a></li>
                    <li><a href="{{ route('transport.train') }}">{{ ___('menus.train_booking') }}</a></li>
                    <li><a href="{{ route('transport.launch') }}">{{ ___('menus.launch_booking') }}</a></li>
                    <li><a href="{{ route('transport.car') }}">{{ ___('menus.car_rental') }}</a></li>
                    <li><a href="{{ route('transport.airport') }}">{{ ___('menus.airport_transfer') }}</a></li>
                    <li><a href="{{ route('transport.driver.index') }}">{{ ___('menus.manage_drivers') }}</a></li>
                    <li><a href="{{ route('transport.vehicle-category.index') }}">{{ ___('menus.vehicle_categories') }}</a></li>
                    <li><a href="{{ route('transport.reports') }}">{{ ___('menus.transport_reports') }}</a></li>
                </ul>
            </li>
            @endif

            @if(hasPermission('flight_read'))
            {{-- Flight Management --}}
            <li class="{{ request()->is('flight*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-plane"></i> <span class="nav-text">{{ ___('menus.flight_management') }}</span> </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('flight.index') }}">{{ ___('menus.flight_bookings') }}</a></li>
                    <li><a href="{{ route('flight.reissue') }}">{{ ___('menus.reissue_requests') }}</a></li>
                    <li><a href="{{ route('flight.cancellation') }}">{{ ___('menus.cancellation_requests') }}</a></li>
                    <li><a href="{{ route('flight.refund') }}">{{ ___('menus.refund_tracking') }}</a></li>
                    <li><a href="{{ route('flight.reports') }}">{{ ___('menus.reports') }}</a></li>
                </ul>
            </li>
            @endif

            @if($servicesVisible)
            <li class="tv-nav-section"><span>{{ ___('menus.services') }}</span></li>
            @endif

            @if(hasPermission('insurance_read'))
            <li> <a href="{{ route('insurance.index') }}" class="{{ request()->is('insurances*') ? 'mm-active' : '' }}" aria-expanded="true"> <i class="icon-shield"></i> <span class="nav-text">{{ ___('menus.travel_insurance') }}</span> </a> </li>
            @endif
            @if(hasPermission('student_service_read'))
            <li> <a href="{{ route('student-service.index') }}" class="{{ request()->is('student-services*') ? 'mm-active' : '' }}" aria-expanded="true"> <i class="icon-graduation"></i> <span class="nav-text">{{ ___('menus.student_consultancy') }}</span> </a> </li>
            @endif
            @if(hasPermission('medical_tour_read'))
            <li> <a href="{{ route('medical-tour.index') }}" class="{{ request()->is('medical-tours*') ? 'mm-active' : '' }}" aria-expanded="true"> <i class="icon-heart"></i> <span class="nav-text">{{ ___('menus.medical_tourism') }}</span> </a> </li>
            @endif
            @if(hasPermission('corporate_travel_read'))
            <li> <a href="{{ route('corporate-travel.index') }}" class="{{ request()->is('corporate-travels*') ? 'mm-active' : '' }}" aria-expanded="true"> <i class="icon-briefcase"></i> <span class="nav-text">{{ ___('menus.corporate_travel') }}</span> </a> </li>
            @endif
            @if(hasPermission('event_tour_read'))
            <li> <a href="{{ route('event-tour.index') }}" class="{{ request()->is('event-tours*') && ! request()->is('event-tours/bookings*') ? 'mm-active' : '' }}" aria-expanded="true"> <i class="icon-calender"></i> <span class="nav-text">{{ ___('menus.events_conference') }}</span> </a> </li>
            <li> <a href="{{ route('event-tour.booking.index') }}" class="{{ request()->is('event-tours/bookings*') ? 'mm-active' : '' }}" aria-expanded="true"> <i class="fa-solid fa-calendar-check"></i> <span class="nav-text">{{ ___('label.event_bookings') }}</span> </a> </li>
            @endif

            @if($financeVisible)
            <li class="tv-nav-section"><span>{{ ___('menus.finance') }}</span></li>
            @endif

            @if(hasPermission('accounting_read'))
            {{-- Accounting --}}
            <li class="{{ request()->is('accounting*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-calculator"></i> <span class="nav-text">{{ ___('menus.accounting') }}</span> </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('acc.dashboard') }}">{{ ___('menus.dashboard') }}</a></li>
                    <li><a href="{{ route('acc.index') }}">{{ ___('menus.manage_accounts') }}</a></li>
                    <li><a href="{{ route('acc.txn.index') }}">{{ ___('menus.manage_transactions') }}</a></li>
                    <li><a href="{{ route('acc.income') }}">{{ ___('menus.income') }}</a></li>
                    <li><a href="{{ route('acc.expenses') }}">{{ ___('menus.expenses') }}</a></li>
                    <li><a href="{{ route('acc.journal') }}">{{ ___('menus.journal_entries') }}</a></li>
                    <li><a href="{{ route('acc.cashbook') }}">{{ ___('menus.cash_book') }}</a></li>
                    <li><a href="{{ route('acc.bankbook') }}">{{ ___('menus.bank_book') }}</a></li>
                    <li><a href="{{ route('acc.ledger') }}">{{ ___('menus.ledger') }}</a></li>
                    <li><a href="{{ route('acc.trial') }}">{{ ___('menus.trial_balance') }}</a></li>
                    <li><a href="{{ route('acc.pl') }}">{{ ___('menus.profit_loss') }}</a></li>
                    <li><a href="{{ route('acc.balance') }}">{{ ___('menus.balance_sheet') }}</a></li>
                    <li><a href="{{ route('acc.invoice.index') }}">{{ ___('menus.manage_invoices') }}</a></li>
                    <li><a href="{{ route('acc.receipt.index') }}">{{ ___('menus.manage_receipts') }}</a></li>
                    <li><a href="{{ route('acc.refunds') }}">{{ ___('menus.refunds') }}</a></li>
                    <li><a href="{{ route('acc.tax') }}">{{ ___('menus.tax_reports') }}</a></li>
                </ul>
            </li>
            @endif

            @if(hasPermission('supplier_read'))
            {{-- Supplier Management --}}
            <li class="{{ request()->is('supplier*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-briefcase"></i> <span class="nav-text">{{ ___('menus.suppliers') }}</span> </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('supplier.index') }}">{{ ___('menus.manage_suppliers') }}</a></li>
                    <li><a href="{{ route('sup.airlines') }}">{{ ___('menus.airlines') }}</a></li>
                    <li><a href="{{ route('sup.hotels') }}">{{ ___('menus.hotels') }}</a></li>
                    <li><a href="{{ route('sup.transport') }}">{{ ___('menus.transport_vendors') }}</a></li>
                    <li><a href="{{ route('sup.visa') }}">{{ ___('menus.visa_partners') }}</a></li>
                    <li><a href="{{ route('sup.contracts') }}">{{ ___('menus.contracts') }}</a></li>
                    <li><a href="{{ route('sup.ledger') }}">{{ ___('menus.supplier_ledger') }}</a></li>
                    <li><a href="{{ route('sup.reports') }}">{{ ___('menus.supplier_reports') }}</a></li>
                </ul>
            </li>
            @endif

            {{-- Agent Finance (back-office) --}}
            @if(hasPermission('agent_finance_read'))
            <li class="{{ request()->is('agent-finance*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-wallet"></i> <span class="nav-text">{{ ___('menus.agent_finance') }}</span> </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('agent.list') }}">{{ ___('menus.agents') }}</a></li>
                    <li><a href="{{ route('agent.commission.index') }}">{{ ___('menus.commissions') }}</a></li>
                    <li><a href="{{ route('agent.invoice.index') }}">{{ ___('menus.agent_invoices') }}</a></li>
                    <li><a href="{{ route('agent.withdrawal.index') }}">Payouts</a></li>
                </ul>
            </li>
            @endif

            @if($workVisible)
            <li class="tv-nav-section"><span>{{ ___('menus.work') }}</span></li>
            @endif

            @if(hasPermission('task_read'))
            {{-- Task Management --}}
            <li class="{{ request()->is('task*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-check"></i> <span class="nav-text">{{ ___('menus.task_management') }}</span> </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('task.index') }}">{{ ___('menus.manage_tasks') }}</a></li>
                    <li><a href="{{ route('task.projects') }}">{{ ___('menus.projects') }}</a></li>
                    <li><a href="{{ route('task.assignments') }}">{{ ___('menus.assignments') }}</a></li>
                    <li><a href="{{ route('task.deadlines') }}">{{ ___('menus.deadlines') }}</a></li>
                    <li><a href="{{ route('task.calendar') }}">{{ ___('menus.calendar_view') }}</a></li>
                    <li><a href="{{ route('task.kanban') }}">{{ ___('menus.kanban_board') }}</a></li>
                </ul>
            </li>
            @endif

            @if(hasPermission('support_read'))
            {{-- Support Center --}}
            <li class="{{ request()->is('support*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-support"></i> <span class="nav-text">{{ ___('menus.support_center') }}</span> </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('support.index') }}">{{ ___('menus.manage_tickets') }}</a></li>
                    <li><a href="{{ route('support.announcement.index') }}">{{ ___('menus.manage_announcements') }}</a></li>
                    <li><a href="{{ route('support.kb') }}">{{ ___('menus.knowledge_base') }}</a></li>
                    <li><a href="{{ route('support.kb.index') }}">{{ ___('menus.manage_kb_articles') }}</a></li>
                </ul>
            </li>
            @endif

            @if($reportsVisible)
            <li class="tv-nav-section"><span>{{ ___('menus.reports') }}</span></li>
            @endif

            @if(hasPermission('report_read'))
            {{-- Reporting Center --}}
            <li class="{{ request()->is('reports-center*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-graph"></i> <span class="nav-text">{{ ___('menus.reporting_center') }}</span> </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('report.sales') }}">{{ ___('menus.sales_reports') }}</a></li>
                    <li><a href="{{ route('report.visa') }}">{{ ___('menus.visa_reports') }}</a></li>
                    <li><a href="{{ route('report.package') }}">{{ ___('menus.package_reports') }}</a></li>
                    <li><a href="{{ route('report.flight') }}">{{ ___('menus.flight_reports') }}</a></li>
                    <li><a href="{{ route('report.hotel') }}">{{ ___('menus.hotel_reports') }}</a></li>
                    <li><a href="{{ route('report.agent') }}">{{ ___('menus.agent_reports') }}</a></li>
                    <li><a href="{{ route('report.customer') }}">{{ ___('menus.customer_reports') }}</a></li>
                    <li><a href="{{ route('report.financial') }}">{{ ___('menus.financial_reports') }}</a></li>
                    <li><a href="{{ route('report.custom') }}">{{ ___('menus.custom_reports') }}</a></li>
                </ul>
            </li>
            @endif

            @if($contentHrVisible)
            <li class="tv-nav-section"><span>{{ ___('menus.content_hr') }}</span></li>
            @endif

            @if(hasPermission('cms_read') || hasPermission('general_settings_read'))
            {{-- CMS & Website --}}
            <li class="{{ request()->is('cms*') || request()->routeIs('settings.appearance.index') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-screen-desktop"></i> <span class="nav-text">{{ ___('menus.cms') }}</span> </a>
                <ul aria-expanded="false">
                    @if(hasPermission('cms_read'))
                    <li><a href="{{ route('cms.index') }}">{{ ___('menus.manage_pages') }}</a></li>
                    <li><a href="{{ route('cms.blog.index') }}">{{ ___('menus.manage_blogs') }}</a></li>
                    <li><a href="{{ route('cms.slider.index') }}">{{ ___('menus.manage_sliders') }}</a></li>
                    <li><a href="{{ route('cms.testimonial.index') }}">{{ ___('menus.manage_testimonials') }}</a></li>
                    <li><a href="{{ route('cms.gallery.index') }}">{{ ___('menus.manage_gallery') }}</a></li>
                    <li><a href="{{ route('cms.faq.index') }}">{{ ___('menus.manage_faqs') }}</a></li>
                    <li><a href="{{ route('cms.menu.index') }}">{{ ___('menus.manage_menus') }}</a></li>
                    {{-- Catalogues rendered by the public marketing site --}}
                    <li><a href="{{ route('cms.visa-service.index') }}">{{ ___('menus.visa_services') }}</a></li>
                    <li><a href="{{ route('cms.flight-route.index') }}">{{ ___('menus.flight_fare_deals') }}</a></li>
                    <li><a href="{{ route('cms.transport-service.index') }}">{{ ___('menus.transport_services') }}</a></li>
                    <li><a href="{{ route('cms.job-opening.index') }}">{{ ___('menus.job_openings') }}</a></li>
                    <li><a href="{{ route('cms.content-block.index') }}">{{ ___('menus.content_blocks') }}</a></li>
                    <li><a href="{{ route('cms.seo') }}">{{ ___('menus.seo_settings') }}</a></li>
                    @endif
                    @if(hasPermission('general_settings_read'))
                    <li><a href="{{ route('settings.appearance.index') }}">{{ ___('menus.appearance') }}</a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- Human Resources (back-office) --}}
            @if(hasPermission('hr_read'))
            <li class="{{ request()->is('hr*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"> <i class="icon-badge"></i> <span class="nav-text">{{ ___('menus.human_resources') }}</span> </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('hr.attendance.index') }}">{{ ___('menus.staff_attendance') }}</a></li>
                    <li><a href="{{ route('hr.leave.index') }}">{{ ___('menus.leave_requests') }}</a></li>
                    <li><a href="{{ route('hr.payslip.index') }}">{{ ___('menus.payslips') }}</a></li>
                </ul>
            </li>
            @endif

            {{-- Customer / Agent / Staff portals are SEPARATE end-user panels, and
                 the SaaS Super Admin is a SEPARATE platform-owner panel — each has its
                 own login + sidebar. They are intentionally NOT in the company admin sidebar. --}}

            @if($systemVisible)
            <li class="tv-nav-section"><span>{{ ___('menus.system') }}</span></li>
            @endif

            @if(hasPermission('branch_read'))
            <li> <a href="{{ route('branch.index') }}" class="{{ request()->is('branches*') ? 'mm-active' : '' }}" aria-expanded="true"> <i class="icon-location-pin"></i> <span class="nav-text">{{ ___('menus.branches') }}</span> </a> </li>
            @endif



            @if(hasPermission('activity_logs_read'))
            <li> <a href="{{route('activity.logs.index')}}" aria-expanded="true"> <i class="icon-list"></i> <span class="nav-text">{{___('menus.activity_logs')}}</span> </a> </li>
            @endif

            @if(hasPermission('login_activity_read'))
            <li> <a href="{{route('login.activity.index')}}" aria-expanded="false"> <i class="icon-list"></i> <span class="nav-text">{{ ___('menus.login_activity') }}</span> </a> </li>
            @endif

            @if(hasPermission('language_read'))
            <li> <a href="{{route('language.index')}}" aria-expanded="true"> <i class="icon-flag"></i> <span class="nav-text">{{___('menus.language')}}</span> </a> </li>
            @endif

            @if($settingsVisible)
            <li>
                <a class="has-arrow" href="javascript:void()" aria-expanded="false"><i class="icon-wrench"></i><span class="nav-text">{{ ___('menus.settings') }}</span></a>
                <ul aria-expanded="false">

                    @if(hasPermission('general_settings_read'))
                    <li> <a href="{{route('settings.general.index')}}">{{ ___('menus.general_settings') }}</a> </li>
                    <li> <a href="{{ route('settings.ai.index') }}">{{ ___('menus.ai_settings') }}</a> </li>
                    <li> <a href="{{ route('settings.flight-api.index') }}">{{ ___('menus.flight_api') }}</a> </li>
                    <li> <a href="{{ route('settings.whatsapp.index') }}">{{ ___('menus.whatsapp_settings') }}</a> </li>
                    <li> <a href="{{ route('settings.loyalty.index') }}">{{ ___('menus.loyalty_settings') }}</a> </li>
                    @endif

                    @if(hasPermission('mail_settings_read'))
                    <li> <a href="{{route('settings.mail')}}">{{ ___('menus.mail_setting') }}</a> </li>
                    @endif

                    @if(hasPermission('payment_settings_read'))
                    <li> <a href="{{route('settings.payment.index')}}">{{ ___('menus.payment_gateways') }}</a> </li>
                    @endif

                    @if(hasPermission('sms_settings_read'))
                    <li> <a href="{{route('settings.sms.index')}}">{{ ___('menus.sms_settings') }}</a> </li>
                    @endif

                    @if(hasPermission('general_settings_read'))
                    <li> <a href="{{route('settings.push.index')}}">{{ ___('menus.push_notifications') }}</a> </li>
                    @endif

                    @if(hasPermission('general_settings_read'))
                    <li> <a href="{{route('settings.api.security.index')}}">{{ ___('menus.api_security') }}</a> </li>
                    @endif

                    @if(hasPermission('general_settings_read'))
                    <li> <a href="{{ route('settings.social-links.index') }}">Social links</a> </li>
                    @endif

                    @if(hasPermission('recaptcha_settings_read'))
                    <li> <a href="{{route('settings.recaptcha.index')}}">{{ ___('menus.recaptcha') }}</a> </li>
                    @endif

                    @if(hasPermission('general_settings_read'))
                    <li> <a href="{{ route('settings.social.login.index') }}">{{ ___('menus.social_login_settings') }}</a> </li>
                    @endif

                </ul>
            </li>
            @endif

        </ul>
    </div>
</aside>
