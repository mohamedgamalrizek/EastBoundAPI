<?php

return [
    ['label' => 'Dashboard', 'items' => [['label' => 'General Manager Dashboard', 'icon' => 'fa-solid fa-gauge-high', 'route' => 'dashboard', 'permission' => 'dashboard_read']]],
    [
        'label' => 'CRM & Leads',
        'items' => [
            ['label' => 'Leads', 'icon' => 'fa-solid fa-bullseye', 'route' => 'crm.leads', 'permission' => 'crm_read', 'active' => ['crm.leads*']],
            ['label' => 'Opportunities / Deals', 'icon' => 'fa-solid fa-handshake', 'permission' => 'crm_read', 'route' => 'dmc.opportunities.index', 'active' => ['dmc.opportunities.*']],
            [
                'label' => 'Activities & Follow-ups', 'icon' => 'fa-solid fa-arrows-spin',
                'permission' => 'crm_read',
                'children' => [
                    ['label' => 'Activities', 'icon' => 'fa-solid fa-bolt', 'route' => 'crm.activity.index', 'active' => ['crm.activity.*']],
                    ['label' => 'Follow-up Calendar', 'icon' => 'fa-solid fa-calendar-check', 'route' => 'crm.followup'],
                    ['label' => 'Activity Timeline', 'icon' => 'fa-solid fa-timeline', 'route' => 'crm.activity'],
                    ['label' => 'Customer Notes', 'icon' => 'fa-solid fa-note-sticky', 'route' => 'crm.notes'],
                    ['label' => 'Communication History', 'icon' => 'fa-solid fa-comments', 'route' => 'crm.communication'],
                    ['label' => 'Contact Messages', 'icon' => 'fa-solid fa-envelope-open-text', 'route' => 'crm.contact-messages.index', 'active' => ['crm.contact-messages.*']],
                ],
            ],
            [
                'label' => 'Customers', 'icon' => 'fa-solid fa-users',
                'permission' => 'customer_read',
                'children' => [
                    ['label' => '360° Customer Profiles', 'icon' => 'fa-solid fa-user-check', 'route' => 'customer.index', 'active' => ['customer.index', 'customer.show', 'customer.create', 'customer.edit']],
                    ['label' => 'Travelers', 'icon' => 'fa-solid fa-person-walking-luggage', 'route' => 'customer.traveler.index', 'active' => ['customer.traveler.*']],
                    ['label' => 'Passports', 'icon' => 'fa-solid fa-passport', 'route' => 'customer.passport.index', 'active' => ['customer.passport.*']],
                ],
            ],
            ['label' => 'Contacts', 'icon' => 'fa-solid fa-address-book', 'permission' => 'crm_read', 'route' => 'dmc.contacts.index', 'active' => ['dmc.contacts.*']],
            ['label' => 'Lost Leads', 'icon' => 'fa-solid fa-user-xmark', 'permission' => 'crm_read', 'route' => 'dmc.leads.lost', 'active' => ['dmc.leads.lost']],
            ['label' => 'Campaigns', 'icon' => 'fa-solid fa-bullhorn', 'route' => 'campaign.index', 'permission' => 'campaign_read', 'active' => ['campaign.*']],
        ],
    ],
    [
        'label' => 'Sales',
        'items' => [
            ['label' => 'All Opportunities', 'icon' => 'fa-solid fa-filter-circle-dollar', 'permission' => 'crm_read', 'route' => 'dmc.opportunities.index', 'active' => ['dmc.opportunities.index'], 'query' => ['view' => 'active']],
            ['label' => 'Quotations', 'icon' => 'fa-solid fa-file-pen', 'permission' => 'dmc_sales_read', 'route' => 'dmc.quotations.index', 'active' => ['dmc.quotations.*']],
            ['label' => 'Pending Approval', 'icon' => 'fa-solid fa-hourglass-half', 'permission' => 'dmc_sales_read', 'route' => 'dmc.quotations.index', 'active' => ['dmc.quotations.index'], 'query' => ['view' => 'pending']],
            ['label' => 'Sales Orders', 'icon' => 'fa-solid fa-file-signature', 'permission' => 'dmc_sales_read', 'route' => 'dmc.sales-orders.index', 'active' => ['dmc.sales-orders.*']],
            ['label' => 'Confirmed Trips', 'icon' => 'fa-solid fa-circle-check', 'permission' => 'dmc_operations_read', 'route' => 'dmc.trips.index', 'active' => ['dmc.trips.index'], 'query' => ['view' => 'confirmed']],
            ['label' => 'Sales Reports', 'icon' => 'fa-solid fa-chart-line', 'route' => 'dmc.reports.sales', 'permission' => 'dmc_sales_read', 'active' => ['dmc.reports.sales']],
        ],
    ],
    [
        'label' => 'Operations & Trips',
        'items' => [
            ['label' => 'All Trips / Trip Projects', 'icon' => 'fa-solid fa-route', 'permission' => 'dmc_operations_read', 'route' => 'dmc.trips.index', 'active' => ['dmc.trips.*']],
            ['label' => 'Upcoming Trips', 'icon' => 'fa-solid fa-calendar-days', 'permission' => 'dmc_operations_read', 'route' => 'dmc.trips.index', 'active' => ['dmc.trips.index'], 'query' => ['view' => 'upcoming']],
            ['label' => 'Today\'s Operations', 'icon' => 'fa-solid fa-sun', 'permission' => 'dmc_operations_read', 'route' => 'dmc.trip-items.index', 'active' => ['dmc.trip-items.index'], 'query' => ['view' => 'today']],
            [
                'label' => 'Resource Management', 'icon' => 'fa-solid fa-cubes',
                'children' => [
                    [
                        'label' => 'Hotels', 'icon' => 'fa-solid fa-building',
                        'permission' => 'hotel_read',
                        'children' => [
                            ['label' => 'Hotels', 'icon' => 'fa-solid fa-hotel', 'route' => 'hotel.index', 'active' => ['hotel.index', 'hotel.create', 'hotel.edit']],
                            ['label' => 'Rooms', 'icon' => 'fa-solid fa-bed', 'route' => 'hotel.room.index', 'active' => ['hotel.room.*']],
                            ['label' => 'Hotel Bookings', 'icon' => 'fa-solid fa-bell-concierge', 'route' => 'hotel.booking.index', 'active' => ['hotel.booking.*']],
                            ['label' => 'Availability', 'icon' => 'fa-solid fa-calendar-plus', 'route' => 'hotel.availability'],
                        ],
                    ],
                    [
                        'label' => 'Transportation & Fleet', 'icon' => 'fa-solid fa-truck-front',
                        'permission' => 'transport_read',
                        'children' => [
                            ['label' => 'Transport Bookings', 'icon' => 'fa-solid fa-car-side', 'route' => 'transport.index', 'active' => ['transport.index', 'transport.create', 'transport.edit']],
                            ['label' => 'Airport Transfers', 'icon' => 'fa-solid fa-plane-arrival', 'route' => 'transport.airport'],
                            ['label' => 'Car Rental', 'icon' => 'fa-solid fa-car', 'route' => 'transport.car'],
                            ['label' => 'Bus Bookings', 'icon' => 'fa-solid fa-bus', 'route' => 'transport.bus'],
                            ['label' => 'Train Bookings', 'icon' => 'fa-solid fa-train', 'route' => 'transport.train'],
                            ['label' => 'Boat Bookings', 'icon' => 'fa-solid fa-ship', 'route' => 'transport.launch'],
                            ['label' => 'Drivers', 'icon' => 'fa-solid fa-id-card', 'route' => 'transport.driver.index', 'active' => ['transport.driver.*']],
                            ['label' => 'Vehicle Categories', 'icon' => 'fa-solid fa-shapes', 'route' => 'transport.vehicle-category.index', 'active' => ['transport.vehicle-category.*']],
                        ],
                    ],
                    [
                        'label' => 'Tour Guides', 'icon' => 'fa-solid fa-person-hiking',
                        'permission' => 'tour_read',
                        'children' => [['label' => 'Guides', 'icon' => 'fa-solid fa-user-tie', 'route' => 'tour.guides'], ['label' => 'Guide Assignments', 'icon' => 'fa-solid fa-map-location-dot', 'route' => 'tour.guideAssignments']],
                    ],
                    ['label' => 'Activities & Tickets', 'icon' => 'fa-solid fa-ticket', 'permission' => 'dmc_operations_read', 'route' => 'dmc.activities.index', 'active' => ['dmc.activities.*']],
                ],
            ],
            [
                'label' => 'Vouchers & Documents', 'icon' => 'fa-solid fa-folder-open',
                'permission' => 'hotel_read',
                'children' => [
                    ['label' => 'Hotel Vouchers', 'icon' => 'fa-solid fa-receipt', 'route' => 'hotel.vouchers'],
                    ['label' => 'Trip Document Center', 'icon' => 'fa-solid fa-file-circle-check', 'route' => 'dmc.documents.index', 'permission' => 'dmc_operations_read', 'active' => ['dmc.documents.*']],
                ],
            ],
            [
                'label' => 'Operational Tasks & Alerts', 'icon' => 'fa-solid fa-list-check',
                'children' => [
                    ['label' => 'Tasks', 'icon' => 'fa-solid fa-square-check', 'route' => 'task.index', 'permission' => 'task_read', 'active' => ['task.index', 'task.create', 'task.edit']],
                    ['label' => 'Assignments', 'icon' => 'fa-solid fa-user-tag', 'route' => 'task.assignments', 'permission' => 'task_read'],
                    ['label' => 'Deadlines', 'icon' => 'fa-solid fa-stopwatch', 'route' => 'task.deadlines', 'permission' => 'task_read'],
                    ['label' => 'Task Calendar', 'icon' => 'fa-solid fa-calendar-week', 'route' => 'task.calendar', 'permission' => 'task_read'],
                    ['label' => 'Kanban', 'icon' => 'fa-solid fa-table-columns', 'route' => 'task.kanban', 'permission' => 'task_read'],
                    ['label' => 'Automatic Trip Alerts', 'icon' => 'fa-solid fa-bell', 'permission' => 'task_read', 'route' => 'dmc.alerts', 'active' => ['dmc.alerts']],
                    ['label' => 'Personal To-do List', 'icon' => 'fa-solid fa-clipboard-check', 'route' => 'todo.index', 'permission' => 'todo_read', 'active' => ['todo.*']],
                ],
            ],
            [
                'label' => 'Tour Programmes & Schedules', 'icon' => 'fa-solid fa-map',
                'permission' => 'tour_read',
                'children' => [
                    ['label' => 'Tour Programmes', 'icon' => 'fa-solid fa-compass', 'route' => 'package.index', 'active' => ['package.*']],
                    ['label' => 'Programme Categories', 'icon' => 'fa-solid fa-tags', 'route' => 'tour.category'],
                    ['label' => 'Existing Package Bookings', 'icon' => 'fa-solid fa-suitcase-rolling', 'route' => 'booking.index', 'active' => ['booking.*']],
                    ['label' => 'Tour Schedule', 'icon' => 'fa-solid fa-clock', 'route' => 'tour.schedule'],
                    ['label' => 'Manage Schedules', 'icon' => 'fa-solid fa-calendar', 'route' => 'tour.schedule.index', 'active' => ['tour.schedule.*']],
                ],
            ],
        ],
    ],
    [
        'label' => 'Projects & Purchase',
        'items' => [
            [
                'label' => 'Projects', 'icon' => 'fa-solid fa-diagram-project',
                'permission' => 'task_read',
                'children' => [
                    ['label' => 'Task Projects', 'icon' => 'fa-solid fa-folder-tree', 'route' => 'task.projects'],
                    ['label' => 'Operational Project Lifecycle', 'icon' => 'fa-solid fa-rotate', 'route' => 'dmc.trips.index', 'permission' => 'dmc_operations_read', 'active' => ['dmc.trips.*']],
                ],
            ],
            ['label' => 'Purchase Requests', 'icon' => 'fa-solid fa-cart-plus', 'permission' => 'dmc_procurement_read', 'route' => 'dmc.purchase-requests.index', 'active' => ['dmc.purchase-requests.*']],
            ['label' => 'RFQs (Requests for Quotation)', 'icon' => 'fa-solid fa-file-circle-question', 'permission' => 'dmc_procurement_read', 'route' => 'dmc.rfqs.index', 'active' => ['dmc.rfqs.*']],
            ['label' => 'Vendor Orders', 'icon' => 'fa-solid fa-cart-shopping', 'permission' => 'dmc_procurement_read', 'route' => 'dmc.vendor-orders.index', 'active' => ['dmc.vendor-orders.*']],
            [
                'label' => 'Vendors Database', 'icon' => 'fa-solid fa-database',
                'permission' => 'supplier_read',
                'children' => [
                    ['label' => 'Vendor Profiles & Payment Terms', 'icon' => 'fa-solid fa-address-card', 'route' => 'dmc.vendors.index', 'permission' => 'supplier_read', 'active' => ['dmc.vendors.*']],
                    ['label' => 'All Vendors', 'icon' => 'fa-solid fa-store', 'route' => 'supplier.index', 'active' => ['supplier.*']],
                    ['label' => 'Hotel Vendors', 'icon' => 'fa-solid fa-building-user', 'route' => 'sup.hotels'],
                    ['label' => 'Transport Vendors', 'icon' => 'fa-solid fa-truck-moving', 'route' => 'sup.transport'],
                    ['label' => 'Airline Vendors', 'icon' => 'fa-solid fa-plane', 'route' => 'sup.airlines'],
                    ['label' => 'Vendor Contracts', 'icon' => 'fa-solid fa-file-contract', 'route' => 'sup.contracts', 'active' => ['sup.contracts', 'sup.contract.*']],
                ],
            ],
            [
                'label' => 'Vendor Bills & Payments', 'icon' => 'fa-solid fa-money-check-dollar',
                'permission' => 'supplier_read',
                'children' => [
                    ['label' => 'Vendor Ledger (Bills & Payments)', 'icon' => 'fa-solid fa-book', 'route' => 'sup.ledger'],
                    ['label' => 'Vendor Bill Documents', 'icon' => 'fa-solid fa-file-invoice-dollar', 'route' => 'dmc.vendor-bills.index', 'permission' => 'accounting_read', 'active' => ['dmc.vendor-bills.*']],
                ],
            ],
        ],
    ],
    [
        'label' => 'Finance & Accounting',
        'items' => [
            ['label' => 'Customer Invoices', 'icon' => 'fa-solid fa-file-invoice', 'route' => 'acc.invoice.index', 'permission' => 'accounting_read', 'active' => ['acc.invoice.*']],
            ['label' => 'Customer Payments', 'icon' => 'fa-solid fa-credit-card', 'route' => 'acc.receipt.index', 'permission' => 'accounting_read', 'active' => ['acc.receipt.*']],
            ['label' => 'Vendor Bills', 'icon' => 'fa-solid fa-file-circle-exclamation', 'permission' => 'accounting_read', 'route' => 'dmc.vendor-bills.index', 'active' => ['dmc.vendor-bills.*']],
            ['label' => 'Vendor Payments', 'icon' => 'fa-solid fa-money-bill-transfer', 'permission' => 'accounting_read', 'route' => 'dmc.vendor-payments.index', 'active' => ['dmc.vendor-payments.*']],
            ['label' => 'Expenses', 'icon' => 'fa-solid fa-wallet', 'route' => 'acc.expenses', 'permission' => 'accounting_read'],
            ['label' => 'Trip Expenses', 'icon' => 'fa-solid fa-money-bill-wave', 'route' => 'dmc.trip-expenses.index', 'permission' => 'accounting_read', 'active' => ['dmc.trip-expenses.*']],
            ['label' => 'Receivables', 'icon' => 'fa-solid fa-hand-holding-dollar', 'permission' => 'accounting_read', 'route' => 'dmc.reports.receivables', 'active' => ['dmc.reports.receivables']],
            ['label' => 'Payables', 'icon' => 'fa-solid fa-money-check', 'route' => 'sup.reports', 'permission' => 'supplier_read'],
            [
                'label' => 'Profitability', 'icon' => 'fa-solid fa-chart-pie',
                'permission' => 'accounting_read',
                'children' => [
                    ['label' => 'Company Profit & Loss', 'icon' => 'fa-solid fa-scale-balanced', 'route' => 'acc.pl'],
                    ['label' => 'Trip Profitability', 'icon' => 'fa-solid fa-sack-dollar', 'route' => 'dmc.reports.profitability', 'permission' => 'accounting_read', 'active' => ['dmc.reports.profitability']],
                ],
            ],
            [
                'label' => 'Cash Flow', 'icon' => 'fa-solid fa-arrows-left-right',
                'permission' => 'accounting_read',
                'children' => [
                    ['label' => 'Cash Book', 'icon' => 'fa-solid fa-money-bill', 'route' => 'acc.cashbook'],
                    ['label' => 'Bank Book', 'icon' => 'fa-solid fa-building-columns', 'route' => 'acc.bankbook'],
                    ['label' => 'Cash Flow Forecast', 'icon' => 'fa-solid fa-chart-area', 'route' => 'dmc.reports.cashflow', 'permission' => 'accounting_read', 'active' => ['dmc.reports.cashflow']],
                ],
            ],
            [
                'label' => 'Reports', 'icon' => 'fa-solid fa-chart-simple',
                'children' => [
                    ['label' => 'Financial Reports', 'icon' => 'fa-solid fa-file-lines', 'route' => 'report.financial', 'permission' => 'report_read'],
                    ['label' => 'Accounting Overview', 'icon' => 'fa-solid fa-calculator', 'route' => 'acc.dashboard', 'permission' => 'accounting_read'],
                    ['label' => 'Income', 'icon' => 'fa-solid fa-coins', 'route' => 'acc.income', 'permission' => 'accounting_read'],
                    ['label' => 'Journal Entries', 'icon' => 'fa-solid fa-pen-to-square', 'route' => 'acc.journal', 'permission' => 'accounting_read'],
                    ['label' => 'Ledger', 'icon' => 'fa-solid fa-book-open', 'route' => 'acc.ledger', 'permission' => 'accounting_read'],
                    ['label' => 'Trial Balance', 'icon' => 'fa-solid fa-scale-unbalanced-flip', 'route' => 'acc.trial', 'permission' => 'accounting_read'],
                    ['label' => 'Balance Sheet', 'icon' => 'fa-solid fa-table-list', 'route' => 'acc.balance', 'permission' => 'accounting_read'],
                    ['label' => 'Tax Reports', 'icon' => 'fa-solid fa-percent', 'route' => 'acc.tax', 'permission' => 'accounting_read'],
                    ['label' => 'Refunds', 'icon' => 'fa-solid fa-rotate-left', 'route' => 'acc.refunds', 'permission' => 'accounting_read'],
                    ['label' => 'Package Reports', 'icon' => 'fa-solid fa-box-open', 'route' => 'report.package', 'permission' => 'report_read'],
                    ['label' => 'Hotel Reports', 'icon' => 'fa-solid fa-building-circle-check', 'route' => 'report.hotel', 'permission' => 'report_read'],
                    ['label' => 'Transport Reports', 'icon' => 'fa-solid fa-truck-fast', 'route' => 'transport.reports', 'permission' => 'transport_read'],
                    ['label' => 'Tour Reports', 'icon' => 'fa-solid fa-earth-africa', 'route' => 'tour.reports', 'permission' => 'tour_read'],
                    ['label' => 'Customer Reports', 'icon' => 'fa-solid fa-users-viewfinder', 'route' => 'report.customer', 'permission' => 'report_read'],
                    ['label' => 'Agent Reports', 'icon' => 'fa-solid fa-user-group', 'route' => 'report.agent', 'permission' => 'report_read'],
                    ['label' => 'Custom Reports', 'icon' => 'fa-solid fa-sliders', 'route' => 'report.custom', 'permission' => 'report_read'],
                    ['label' => 'Legacy Lead Attribution', 'icon' => 'fa-solid fa-clock-rotate-left', 'route' => 'report.leads', 'permission' => 'report_read'],
                    ['label' => 'Legacy Booking Sales', 'icon' => 'fa-solid fa-box-archive', 'route' => 'report.sales', 'permission' => 'report_read'],
                ],
            ],
        ],
    ],
    [
        'label' => 'Marketing Analysis (websites)',
        'items' => [
            [
                'label' => 'Websites Analytics', 'icon' => 'fa-solid fa-chart-column',
                'permission' => 'dmc_marketing_read',
                'children' => [
                    ['label' => 'Website & Language Performance', 'icon' => 'fa-solid fa-language', 'route' => 'dmc.lead-performance', 'active' => ['dmc.lead-performance']],
                    ['label' => 'ROAS & CAC', 'icon' => 'fa-solid fa-arrow-trend-up', 'route' => 'dmc.reports.marketing', 'permission' => 'dmc_marketing_read', 'active' => ['dmc.reports.marketing']],
                    ['label' => 'Actual Campaign Spend', 'icon' => 'fa-solid fa-magnifying-glass-dollar', 'route' => 'dmc.campaign-spends.index', 'permission' => 'campaign_read', 'active' => ['dmc.campaign-spends.*']],
                ],
            ],
        ],
    ],
    [
        'label' => 'Websites & Lead Tracking Integration',
        'items' => [
            ['label' => 'Company Websites Management', 'icon' => 'fa-solid fa-globe', 'permission' => 'crm_read', 'route' => 'dmc.websites.index', 'active' => ['dmc.websites.*']],
            ['label' => 'Mandatory Lead Attribution Data', 'icon' => 'fa-solid fa-fingerprint', 'permission' => 'crm_read', 'route' => 'dmc.attribution', 'active' => ['dmc.attribution']],
            ['label' => 'Campaigns Tracking', 'icon' => 'fa-solid fa-crosshairs', 'permission' => 'dmc_marketing_read', 'route' => 'dmc.reports.marketing', 'active' => ['dmc.reports.marketing']],
        ],
    ],
    [
        'label' => 'System & Administration',
        'items' => [
            [
                'label' => 'User Roles & Permissions', 'icon' => 'fa-solid fa-users-gear',
                'children' => [
                    ['label' => 'Users', 'icon' => 'fa-solid fa-user', 'route' => 'user.index', 'permission' => 'user_read', 'active' => ['user.*']],
                    ['label' => 'Roles & Permissions', 'icon' => 'fa-solid fa-user-shield', 'route' => 'role.index', 'permission' => 'role_read', 'active' => ['role.*']],
                ],
            ],
            ['label' => 'Branches', 'icon' => 'fa-solid fa-sitemap', 'route' => 'branch.index', 'permission' => 'branch_read', 'active' => ['branch.*']],
            [
                'label' => 'Activity & Login Logs', 'icon' => 'fa-solid fa-clipboard-list',
                'children' => [
                    ['label' => 'Activity Logs', 'icon' => 'fa-solid fa-list-ul', 'route' => 'activity.logs.index', 'permission' => 'activity_logs_read'],
                    ['label' => 'Login Logs', 'icon' => 'fa-solid fa-right-to-bracket', 'route' => 'login.activity.index', 'permission' => 'login_activity_read'],
                ],
            ],
            [
                'label' => 'System Settings', 'icon' => 'fa-solid fa-gears',
                'children' => [
                    ['label' => 'Quotation Approval Policy', 'icon' => 'fa-solid fa-stamp', 'route' => 'dmc.approval-policies.index', 'permission' => 'general_settings_read', 'active' => ['dmc.approval-policies.*']],
                    ['label' => 'General Settings', 'icon' => 'fa-solid fa-gear', 'route' => 'settings.general.index', 'permission' => 'general_settings_read'],
                    ['label' => 'Appearance', 'icon' => 'fa-solid fa-palette', 'route' => 'settings.appearance.index', 'permission' => 'general_settings_read'],
                    ['label' => 'AI Settings', 'icon' => 'fa-solid fa-robot', 'route' => 'settings.ai.index', 'permission' => 'general_settings_read'],
                    ['label' => 'WhatsApp', 'icon' => 'fa-brands fa-whatsapp', 'route' => 'settings.whatsapp.index', 'permission' => 'general_settings_read'],
                    ['label' => 'Email', 'icon' => 'fa-solid fa-envelope', 'route' => 'settings.mail', 'permission' => 'mail_settings_read'],
                    ['label' => 'SMS', 'icon' => 'fa-solid fa-comment-sms', 'route' => 'settings.sms.index', 'permission' => 'sms_settings_read'],
                    ['label' => 'Push Notifications', 'icon' => 'fa-solid fa-tower-broadcast', 'route' => 'settings.push.index', 'permission' => 'general_settings_read'],
                    ['label' => 'API Security', 'icon' => 'fa-solid fa-shield-halved', 'route' => 'settings.api.security.index', 'permission' => 'general_settings_read'],
                    ['label' => 'Payment Gateways', 'icon' => 'fa-solid fa-cash-register', 'route' => 'settings.payment.index', 'permission' => 'payment_settings_read'],
                    ['label' => 'Social Links', 'icon' => 'fa-solid fa-share-nodes', 'route' => 'settings.social-links.index', 'permission' => 'general_settings_read'],
                    ['label' => 'Social Login Settings', 'icon' => 'fa-solid fa-user-lock', 'route' => 'settings.social.login.index', 'permission' => 'general_settings_read'],
                    ['label' => 'Recaptcha', 'icon' => 'fa-solid fa-shield-virus', 'route' => 'settings.recaptcha.index', 'permission' => 'recaptcha_settings_read'],
                    ['label' => 'Interface Languages', 'icon' => 'fa-solid fa-earth-europe', 'route' => 'language.index', 'permission' => 'language_read', 'active' => ['language.*']],
                    ['label' => 'Chart of Accounts', 'icon' => 'fa-solid fa-list-ol', 'route' => 'acc.index', 'permission' => 'accounting_read', 'active' => ['acc.index', 'acc.create', 'acc.edit']],
                    [
                        'label' => 'Website Content', 'icon' => 'fa-solid fa-laptop-code',
                        'permission' => 'cms_read',
                        'children' => [
                            ['label' => 'Pages', 'icon' => 'fa-solid fa-file', 'route' => 'cms.index', 'active' => ['cms.index', 'cms.create', 'cms.edit']],
                            ['label' => 'Blogs', 'icon' => 'fa-solid fa-blog', 'route' => 'cms.blog.index', 'active' => ['cms.blog.*']],
                            ['label' => 'Sliders', 'icon' => 'fa-solid fa-images', 'route' => 'cms.slider.index', 'active' => ['cms.slider.*']],
                            ['label' => 'Testimonials', 'icon' => 'fa-solid fa-quote-left', 'route' => 'cms.testimonial.index', 'active' => ['cms.testimonial.*']],
                            ['label' => 'Gallery', 'icon' => 'fa-solid fa-camera', 'route' => 'cms.gallery.index', 'active' => ['cms.gallery.*']],
                            ['label' => 'FAQs', 'icon' => 'fa-solid fa-circle-question', 'route' => 'cms.faq.index', 'active' => ['cms.faq.*']],
                            ['label' => 'Website Menus', 'icon' => 'fa-solid fa-bars', 'route' => 'cms.menu.index', 'active' => ['cms.menu.*']],
                            ['label' => 'Transport Services', 'icon' => 'fa-solid fa-van-shuttle', 'route' => 'cms.transport-service.index', 'active' => ['cms.transport-service.*']],
                            ['label' => 'Job Openings', 'icon' => 'fa-solid fa-briefcase', 'route' => 'cms.job-opening.index', 'active' => ['cms.job-opening.*']],
                            ['label' => 'Content Blocks', 'icon' => 'fa-solid fa-table-cells-large', 'route' => 'cms.content-block.index', 'active' => ['cms.content-block.*']],
                            ['label' => 'SEO', 'icon' => 'fa-solid fa-magnifying-glass-chart', 'route' => 'cms.seo'],
                        ],
                    ],
                    [
                        'label' => 'Internal Administration', 'icon' => 'fa-solid fa-building-shield',
                        'children' => [
                            ['label' => 'Job Applications', 'icon' => 'fa-solid fa-file-arrow-up', 'route' => 'crm.job-applications.index', 'permission' => 'crm_read', 'active' => ['crm.job-applications.*']],
                            ['label' => 'Staff Attendance', 'icon' => 'fa-solid fa-user-clock', 'route' => 'hr.attendance.index', 'permission' => 'hr_read', 'active' => ['hr.attendance.*']],
                            ['label' => 'Leave Requests', 'icon' => 'fa-solid fa-calendar-minus', 'route' => 'hr.leave.index', 'permission' => 'hr_read', 'active' => ['hr.leave.*']],
                            ['label' => 'Payslips', 'icon' => 'fa-solid fa-money-bill-1', 'route' => 'hr.payslip.index', 'permission' => 'hr_read', 'active' => ['hr.payslip.*']],
                            ['label' => 'Support Tickets', 'icon' => 'fa-solid fa-headset', 'route' => 'support.index', 'permission' => 'support_read', 'active' => ['support.index', 'support.create', 'support.edit', 'support.ticket']],
                            ['label' => 'Announcements', 'icon' => 'fa-solid fa-circle-info', 'route' => 'support.announcement.index', 'permission' => 'support_read', 'active' => ['support.announcement.*']],
                            ['label' => 'Knowledge Base', 'icon' => 'fa-solid fa-book-open-reader', 'route' => 'support.kb', 'permission' => 'support_read'],
                            ['label' => 'Manage Knowledge Base', 'icon' => 'fa-solid fa-book-atlas', 'route' => 'support.kb.index', 'permission' => 'support_read', 'active' => ['support.kb.*']],
                            ['label' => 'Agents', 'icon' => 'fa-solid fa-people-arrows', 'route' => 'agent.list', 'permission' => 'agent_finance_read'],
                            ['label' => 'Agent Commissions', 'icon' => 'fa-solid fa-award', 'route' => 'agent.commission.index', 'permission' => 'agent_finance_read', 'active' => ['agent.commission.*']],
                            ['label' => 'Agent Invoices', 'icon' => 'fa-solid fa-file-export', 'route' => 'agent.invoice.index', 'permission' => 'agent_finance_read', 'active' => ['agent.invoice.*']],
                            ['label' => 'Agent Payouts', 'icon' => 'fa-solid fa-money-bill-trend-up', 'route' => 'agent.withdrawal.index', 'permission' => 'agent_finance_read', 'active' => ['agent.withdrawal.*']],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
