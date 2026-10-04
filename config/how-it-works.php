<?php

/*
|--------------------------------------------------------------------------
| "How it works" page helpers
|--------------------------------------------------------------------------
| Content for the <x-how-it-works /> component. Keys are route names; the
| component matches the most specific key first, then falls back segment
| by segment (cms.blog.index -> cms.blog -> cms). Each entry supports:
| title, intro, steps[] and tips[]. Pages without an entry show nothing.
*/

return [

    'dashboard' => [
        'title' => 'How the Dashboard works',
        'intro' => 'The dashboard is a live summary of your whole agency — bookings, revenue, customers and pending work are pulled from every module in real time.',
        'steps' => [
            'Check the stat cards at the top for today\'s bookings, revenue and new customers.',
            'Use the charts to spot trends over the last weeks or months.',
            'Follow the quick links on any card to jump straight into the module behind the number.',
        ],
        'tips' => [
            'Numbers here are read-only — to change anything, open the related module from the sidebar.',
        ],
    ],

    /* ------------------------------------------------ Tours & bookings */

    'package' => [
        'title' => 'How Tour Packages work',
        'intro' => 'A package is a sellable tour product — its title, destination, price, duration and itinerary. Active packages appear on the public website and in the mobile app, where customers can book them.',
        'steps' => [
            'Click Add to create a package with a title, destination, category, price and duration (days/nights).',
            'Upload a cover image and fill in inclusions, exclusions and a day-by-day itinerary — the public page shows only what you fill in.',
            'Set the status to Active to publish it on the website and app; Inactive hides it without deleting anything.',
            'Bookings made against a package appear under Package Booking, linked to the customer who booked.',
        ],
        'tips' => [
            'Categories are managed under Tour Management → Package Category; assigning one keeps public listings filterable.',
            'The price shown is the base selling price — coupons and agent commissions are applied at booking time.',
        ],
    ],

    'tour.category' => [
        'title' => 'How Package Categories work',
        'intro' => 'Categories group your tour packages (Umrah, Adventure, Beach, City…) so customers can filter them on the website and you can report by segment.',
        'steps' => [
            'Add a category with a clear, short name.',
            'Assign it when creating or editing a tour package.',
            'Deactivate a category to hide it from filters without touching the packages inside it.',
        ],
        'tips' => [
            'Keep the list short — a handful of well-named categories filters better than dozens of overlapping ones.',
        ],
    ],

    'tour.guides' => [
        'title' => 'How Tour Guides work',
        'intro' => 'Store the guides your agency works with — name, contact, languages and expertise — and assign them to scheduled departures.',
        'steps' => [
            'Add each guide with their contact details and specialities.',
            'When building a tour schedule, pick a guide for the departure.',
            'Keep availability up to date so double-booking a guide is easy to spot.',
        ],
    ],

    'tour.schedule' => [
        'title' => 'How Tour Schedules work',
        'intro' => 'A schedule is a dated departure of a package — the same package can run many times with different dates, seats and guides.',
        'steps' => [
            'Pick the package this departure belongs to.',
            'Set the start/end dates and the number of seats available.',
            'Assign a tour guide if one is confirmed.',
            'Track seat usage as bookings come in — a full departure should be closed or extended.',
        ],
        'tips' => [
            'Use Manage Schedules for the calendar-style overview of all upcoming departures.',
        ],
    ],

    'tour.itinerary' => [
        'title' => 'How the Itinerary Builder works',
        'intro' => 'Build the day-by-day plan of a package. The itinerary is shown on the public package page exactly as you enter it here.',
        'steps' => [
            'Select the package you want to build an itinerary for.',
            'Add one row per day with a title and description of the day\'s plan.',
            'Reorder or remove days as the plan changes — the public page updates immediately.',
        ],
        'tips' => [
            'If a package has no itinerary rows, the public page simply hides the section — nothing is invented.',
        ],
    ],

    'tour.reports' => [
        'title' => 'How Package Reports work',
        'intro' => 'Reports summarise how your tour products perform — bookings, revenue and occupancy per package and per period.',
        'steps' => [
            'Pick a date range to analyse.',
            'Compare packages by bookings and revenue to see what sells.',
            'Use the results to decide pricing, new departures, or which packages to retire.',
        ],
    ],

    'booking' => [
        'title' => 'How Package Bookings work',
        'intro' => 'A booking links a customer to a tour package with travel dates, traveller count and payment status. It drives invoices, receipts and the customer\'s own app view.',
        'steps' => [
            'Click Add and select the customer — their name, email and phone are snapshotted onto the booking automatically.',
            'Pick the package, dates and number of travellers; the amount is calculated from the package price.',
            'Take payment: recording a payment creates an Invoice and a Receipt and marks the booking Paid.',
            'Cancel a paid booking to refund the amount to the customer\'s wallet.',
        ],
        'tips' => [
            'Bookings made by customers in the mobile app and by B2B agents appear in this same list.',
            'Every money movement lands in Accounting automatically — no double entry needed.',
        ],
    ],

    /* ------------------------------------------------ Customers & CRM */

    'customer' => [
        'title' => 'How Customers work',
        'intro' => 'This is your customer master list. Every booking, payment, visa application and wallet transaction hangs off a customer record, and customers log into the mobile app with these credentials.',
        'steps' => [
            'Add a customer with name, email and phone — email or phone is what they log into the app with.',
            'Open a customer to see their full history: bookings, payments, documents and wallet.',
            'Attach passports and saved travellers to the customer for faster future bookings.',
        ],
        'tips' => [
            'Deleting a customer breaks their history — prefer marking them inactive.',
        ],
    ],

    'customer.passport' => [
        'title' => 'How Passports work',
        'intro' => 'Store customers\' passport details once and reuse them for visa applications and bookings — with expiry tracking so nobody travels on a passport about to lapse.',
        'steps' => [
            'Add a passport against a customer with number, issue and expiry dates.',
            'Attach a scan of the data page for visa processing.',
            'Watch the expiry column — passports close to expiry are flagged.',
        ],
    ],

    'customer.traveler' => [
        'title' => 'How Travelers work',
        'intro' => 'Travellers are the companions a customer books for — family members or colleagues — saved once and reused on every booking.',
        'steps' => [
            'Add travellers under the customer who books for them.',
            'When creating a booking, pick saved travellers instead of retyping their details.',
            'Customers can also manage their own traveller list from the mobile app.',
        ],
    ],

    'crm' => [
        'title' => 'How the CRM works',
        'intro' => 'The CRM tracks people before they become paying customers — leads, follow-ups, conversations and notes — so no enquiry falls through the cracks.',
        'steps' => [
            'Capture every enquiry as a lead (website forms and newsletter signups create leads automatically).',
            'Schedule follow-ups so each lead has a next action and an owner.',
            'Log calls, emails and meetings under Communication so anyone can pick up the thread.',
            'When a lead books, create them as a Customer — their history starts there.',
        ],
    ],

    'crm.leads' => [
        'title' => 'How Leads work',
        'intro' => 'A lead is a potential customer — someone who enquired but hasn\'t booked yet. Website contact forms feed this list automatically.',
        'steps' => [
            'Add leads manually or let website forms create them.',
            'Qualify each lead: set its source, interest and status.',
            'Schedule a follow-up so the lead always has a next step.',
            'Convert to a Customer once they\'re ready to book.',
        ],
    ],

    'crm.followup' => [
        'title' => 'How Follow-ups work',
        'intro' => 'Follow-ups are dated reminders attached to leads — the to-do list that keeps your pipeline moving.',
        'steps' => [
            'Create a follow-up with a date and what needs to happen.',
            'Work through today\'s follow-ups each morning.',
            'Mark each one done and schedule the next touch if the lead is still warm.',
        ],
    ],

    'crm.contact-messages' => [
        'title' => 'How Contact Messages work',
        'intro' => 'Messages submitted through the website contact form land here for triage.',
        'steps' => [
            'Read new messages and decide: enquiry, complaint or spam.',
            'Turn genuine enquiries into leads so they enter the sales pipeline.',
            'Delete spam to keep the queue clean.',
        ],
    ],

    'crm.job-applications' => [
        'title' => 'How Job Applications work',
        'intro' => 'Applications submitted against your public job openings (CMS → Job Openings) are collected here.',
        'steps' => [
            'Review new applications and their attached CVs.',
            'Shortlist or reject; contact shortlisted candidates directly.',
            'Close the job opening in CMS when the position is filled.',
        ],
    ],

    /* ------------------------------------------------ Marketing */

    'coupon' => [
        'title' => 'How Coupons work',
        'intro' => 'Coupons give customers a discount code to use at booking time — a fixed amount or a percentage, with validity dates and usage limits.',
        'steps' => [
            'Create a coupon with a code, discount type (flat or %) and value.',
            'Set the validity window and how many times it can be used.',
            'Share the code in your campaigns; customers apply it during booking.',
            'Deactivate a coupon at any time to stop further use.',
        ],
        'tips' => [
            'Percentage coupons on high-value packages can be expensive — set a sensible maximum.',
        ],
    ],

    'campaign' => [
        'title' => 'How Campaigns work',
        'intro' => 'Campaigns are your marketing pushes — seasonal offers or announcements targeted at your customer and subscriber base.',
        'steps' => [
            'Create a campaign with its message, audience and schedule.',
            'Pair it with a coupon code if the offer includes a discount.',
            'Launch, then watch responses arrive as leads and bookings.',
        ],
    ],

    'newsletter-subscriber' => [
        'title' => 'How Newsletter Subscribers work',
        'intro' => 'People who subscribe on the website are collected here — your opt-in mailing list.',
        'steps' => [
            'Review the list; it grows automatically from the website footer form.',
            'Export or target subscribers when sending a campaign.',
            'Remove anyone who asks to unsubscribe.',
        ],
    ],

    /* ------------------------------------------------ Visa */

    'visa' => [
        'title' => 'How Visa Management works',
        'intro' => 'Track every visa application from submission to decision — documents, appointments, embassy status and expiry — for customers travelling on your bookings.',
        'steps' => [
            'Create an application for a customer with destination country and visa type.',
            'Collect the required documents and attach them to the application.',
            'Record embassy appointments and update the status as it progresses (submitted → processing → approved/rejected).',
            'Customers can follow the same status from the website\'s visa tracking page and the mobile app.',
        ],
        'tips' => [
            'The Expiry Alerts page warns you before issued visas run out — check it weekly.',
        ],
    ],

    /* ------------------------------------------------ Hajj & Umrah */

    'hajj' => [
        'title' => 'How Hajj & Umrah works',
        'intro' => 'A dedicated workflow for pilgrimage packages — pilgrims, groups, payments, flights, hotels and document checklists in one place.',
        'steps' => [
            'Create the Hajj/Umrah package with its season, price and inclusions.',
            'Register pilgrims and collect their documents (passport, photos, vaccination).',
            'Organise pilgrims into groups with a group leader for travel.',
            'Record payments (often in instalments) and track who is fully paid.',
            'Attach flight and hotel details so each group\'s logistics are complete.',
        ],
        'tips' => [
            'Use Reports to see season-wide totals: pilgrims registered, paid vs due, and documents still missing.',
        ],
    ],

    'hajj.pilgrim' => [
        'title' => 'How Pilgrims work',
        'intro' => 'Every person travelling for Hajj or Umrah is registered here with their documents and payment status.',
        'steps' => [
            'Register the pilgrim with personal and passport details.',
            'Tick off required documents as they are received.',
            'Assign the pilgrim to a group before departure.',
        ],
    ],

    /* ------------------------------------------------ Hotels */

    'hotel' => [
        'title' => 'How Hotels work',
        'intro' => 'Manage the hotels you sell — their rooms, nightly rates and availability. Active hotels can be browsed and booked from the mobile app.',
        'steps' => [
            'Add a hotel with its city, star rating and base nightly rate.',
            'Define its rooms and rates under Rooms.',
            'Keep availability updated so app bookings don\'t oversell.',
            'Hotel bookings arrive under Hotel Bookings with the guest and dates; issue a voucher once confirmed.',
        ],
    ],

    'hotel.room' => [
        'title' => 'How Rooms work',
        'intro' => 'Rooms belong to a hotel — each type (single, double, suite…) with its own rate and count.',
        'steps' => [
            'Add each room type under its hotel with capacity and nightly rate.',
            'Keep the room count accurate — it drives availability.',
        ],
    ],

    'hotel.booking' => [
        'title' => 'How Hotel Bookings work',
        'intro' => 'Reservations against your hotels — made here in the office or by customers in the app.',
        'steps' => [
            'Create a booking with guest, hotel, room type and dates.',
            'Take payment; the invoice and receipt are generated automatically.',
            'Issue the voucher the guest presents at check-in.',
        ],
    ],

    /* ------------------------------------------------ Flights */

    'flight' => [
        'title' => 'How Flight Management works',
        'intro' => 'Handle flight requests end to end — booking, ticketing, reissue, refunds and cancellations. App customers submit a request; your team quotes and issues.',
        'steps' => [
            'A flight request arrives (from the app or entered here) with route and dates.',
            'Quote the fare and confirm with the customer.',
            'Issue the ticket and record it under Tickets.',
            'Handle changes through Reissue, and money-back cases through Refund or Cancellation.',
        ],
        'tips' => [
            'Reports shows issued vs refunded volume per period — useful for airline negotiations.',
        ],
    ],

    /* ------------------------------------------------ Transport */

    'transport' => [
        'title' => 'How Transport Services work',
        'intro' => 'Manage ground and water transport — cars, buses, launches, trains and airport transfers — with vehicles, drivers and bookings.',
        'steps' => [
            'Add your vehicles under the matching type (car, bus, launch, train, airport transfer).',
            'Register drivers with their licence details and assign them to vehicles.',
            'Record transport bookings with route, date and fare.',
        ],
        'tips' => [
            'The cheapest fare actually booked per vehicle type is what the public website shows as the "from" price.',
        ],
    ],

    'transport.driver' => [
        'title' => 'How Drivers work',
        'intro' => 'Your driver roster — licence details, contact and which vehicle each driver is assigned to.',
        'steps' => [
            'Add each driver with licence number and phone.',
            'Assign the driver to a vehicle.',
            'Keep licence expiry dates current.',
        ],
    ],

    /* ------------------------------------------------ Other travel products */

    'event-tour' => [
        'title' => 'How Event Tours work',
        'intro' => 'One-off event trips — concerts, sports, festivals — sold like packages but tied to a specific event date.',
        'steps' => [
            'Create the event tour with its date, venue and price.',
            'Publish it while seats remain; close it once the event passes.',
        ],
    ],

    'medical-tour' => [
        'title' => 'How Medical Tourism works',
        'intro' => 'Trips arranged around treatment abroad — hospital, treatment type, travel and stay handled together.',
        'steps' => [
            'Record the patient\'s case with destination hospital and treatment.',
            'Arrange visa, travel and accommodation alongside.',
            'Track the case status through treatment and return.',
        ],
    ],

    'corporate-travel' => [
        'title' => 'How Corporate Travel works',
        'intro' => 'Business-travel arrangements for company clients — trips, approvals and consolidated billing per company.',
        'steps' => [
            'Register the corporate client and their travellers.',
            'Record each trip with its itinerary and cost.',
            'Bill the company on account rather than per traveller.',
        ],
    ],

    'student-service' => [
        'title' => 'How Student Services work',
        'intro' => 'Student-abroad support — admissions, student visas and travel arranged as one case per student.',
        'steps' => [
            'Open a case per student with destination country and institution.',
            'Track admission documents and the student visa application.',
            'Arrange travel once the visa is granted.',
        ],
    ],

    'insurance' => [
        'title' => 'How Travel Insurance works',
        'intro' => 'Sell and track travel-insurance policies attached to your customers and their trips.',
        'steps' => [
            'Record a policy with insurer, coverage and premium.',
            'Link it to the customer (and trip) it covers.',
            'Track expiry and renewals.',
        ],
    ],

    /* ------------------------------------------------ Accounting */

    'acc' => [
        'title' => 'How Accounting works',
        'intro' => 'Every taka that moves through the system lands here — booking payments, refunds, expenses — organised into transactions, ledgers and statements.',
        'steps' => [
            'Most entries are created automatically when bookings are paid, refunded or cancelled.',
            'Record manual income and expenses under their pages.',
            'Use Ledger, Cashbook and Bankbook to see balances per account.',
            'Trial Balance and P&L give you the period-end picture.',
        ],
        'tips' => [
            'If a number looks wrong, trace it back through Transactions — every entry links to its source (booking, invoice or manual entry).',
        ],
    ],

    'acc.invoice' => [
        'title' => 'How Invoices work',
        'intro' => 'An invoice (INV-xxxxx) is created automatically whenever a booking payment is recorded — it is the bill behind every receipt.',
        'steps' => [
            'Find an invoice by number or customer.',
            'Open it to see the booking it belongs to and its payment state.',
        ],
    ],

    'acc.receipt' => [
        'title' => 'How Receipts work',
        'intro' => 'A receipt (RCP-xxxxx) proves a payment against an invoice — one is generated automatically with every recorded payment.',
        'steps' => [
            'Find a receipt by number, customer or date.',
            'Open it to see the invoice and booking it settles.',
        ],
    ],

    /* ------------------------------------------------ Agents (B2B) */

    'agent' => [
        'title' => 'How B2B Agents work',
        'intro' => 'Agents are partner businesses that sell your inventory at net fare and earn commission. They get their own app login, wallet and commission statement.',
        'steps' => [
            'Register an agent (they get the Agent role and can log into the app).',
            'Agents book on behalf of their clients — those bookings carry the agent\'s ID.',
            'Commission accrues per booking and shows under Commissions.',
            'Settle commissions and top-ups through the agent\'s wallet; invoices track what the agent owes you.',
        ],
    ],

    /* ------------------------------------------------ Suppliers */

    'supplier' => [
        'title' => 'How Suppliers work',
        'intro' => 'Suppliers are who you buy from — airlines, hotels, transport and visa processors — with contracts and a payable ledger per supplier.',
        'steps' => [
            'Register each supplier with their category and contact.',
            'Attach contracts and negotiated rates.',
            'Track what you owe them in the supplier ledger as purchases accrue.',
        ],
    ],

    'sup' => [
        'title' => 'How Supplier Management works',
        'intro' => 'Category views of your supplier network — airlines, hotels, transport and visa processors — plus their contracts, ledger and reports.',
        'steps' => [
            'Browse suppliers by category to see who covers what.',
            'Check the Ledger page for outstanding payables per supplier.',
            'Use Reports to compare spend across suppliers.',
        ],
    ],

    /* ------------------------------------------------ HR & internal work */

    'hr' => [
        'title' => 'How HR works',
        'intro' => 'Staff attendance, leave and payslips for your own team.',
        'steps' => [
            'Attendance records who was in each day.',
            'Leave tracks requests and approvals against each staff member\'s balance.',
            'Payslips are generated per month per employee.',
        ],
    ],

    'task' => [
        'title' => 'How Tasks work',
        'intro' => 'Team work management — tasks organised into projects, assigned to staff, with deadlines, a kanban board and a calendar.',
        'steps' => [
            'Create a task with an assignee and a deadline (group related tasks under a project).',
            'Move tasks across the Kanban board as they progress.',
            'Watch Deadlines for anything at risk of slipping.',
        ],
    ],

    'todo' => [
        'title' => 'How the Todo List works',
        'intro' => 'A lightweight personal checklist — quick reminders that don\'t need a full task with assignees and projects.',
        'steps' => [
            'Add an item with an optional note or attachment.',
            'Tick it off when done; delete what\'s no longer relevant.',
        ],
    ],

    /* ------------------------------------------------ Support */

    'support' => [
        'title' => 'How the Support Desk works',
        'intro' => 'Customer support tickets from the app and website land here; the knowledge base and announcements are your self-service side.',
        'steps' => [
            'New tickets arrive with the customer\'s issue — reply from the ticket thread.',
            'Set status (open → in progress → resolved) so the queue stays honest.',
            'Write knowledge-base articles for questions that keep repeating.',
            'Use announcements to broadcast service notices to all users.',
        ],
    ],

    'support.kb' => [
        'title' => 'How the Knowledge Base works',
        'intro' => 'Self-service articles that answer common questions before they become tickets.',
        'steps' => [
            'Write an article per recurring question, in plain language.',
            'Group articles by topic so they are easy to find.',
            'Update or retire articles when the process changes.',
        ],
    ],

    'support.announcement' => [
        'title' => 'How Announcements work',
        'intro' => 'Broadcast notices — schedule changes, offers, downtime — shown to your users.',
        'steps' => [
            'Create the announcement with its message and validity window.',
            'Publish it; it is visible while active.',
            'Expire or delete it when no longer relevant.',
        ],
    ],

    /* ------------------------------------------------ Reports */

    'report' => [
        'title' => 'How Reports work',
        'intro' => 'Cross-module reporting — sales, financial, customer, package, visa, hotel, flight and agent performance over any period.',
        'steps' => [
            'Pick the report that matches your question (sales for revenue, financial for money in/out, agent for partner performance…).',
            'Set the date range and any filters.',
            'Export or act on what you find — every figure traces back to records in its module.',
        ],
    ],

    /* ------------------------------------------------ CMS / website */

    'cms' => [
        'title' => 'How the CMS works',
        'intro' => 'Everything shown on your public website is edited here — pages, sliders, blogs, galleries, testimonials, menus and service content. No code changes needed.',
        'steps' => [
            'Pick the content type from the CMS menu (slider for the homepage hero, blog for articles, and so on).',
            'Create or edit entries; active items appear on the website immediately.',
            'Use Menus to control the header and footer navigation of the site.',
        ],
    ],

    'cms.blog' => [
        'title' => 'How Blogs work',
        'intro' => 'Articles for the website\'s blog section — travel guides, news and SEO content.',
        'steps' => [
            'Write the post with a title, excerpt, body and cover image.',
            'Set a category and reading time for nicer listings.',
            'Publish when ready — drafts stay hidden from the site.',
        ],
    ],

    'cms.slider' => [
        'title' => 'How Sliders work',
        'intro' => 'The homepage hero banners. The active slider with the lowest sort order is the first thing visitors see.',
        'steps' => [
            'Add a slide with an image, headline, badge and call-to-action link.',
            'Order slides with sort order — lowest shows first.',
            'Deactivate a slide to remove it without deleting it.',
        ],
    ],

    'cms.gallery' => [
        'title' => 'How the Gallery works',
        'intro' => 'Photos shown on the website\'s gallery page — your trips and destinations.',
        'steps' => [
            'Upload images with a caption.',
            'Set sort order to control the display sequence.',
        ],
    ],

    'cms.testimonial' => [
        'title' => 'How Testimonials work',
        'intro' => 'Customer quotes displayed on the website for social proof.',
        'steps' => [
            'Add the customer\'s name, city, photo and quote.',
            'Activate the ones you want shown; rotate them seasonally.',
        ],
    ],

    'cms.faq' => [
        'title' => 'How FAQs work',
        'intro' => 'Questions and answers shown on the website\'s FAQ page.',
        'steps' => [
            'Add each question with a clear, short answer.',
            'Order them so the most common questions come first.',
        ],
    ],

    'cms.menu' => [
        'title' => 'How Menus work',
        'intro' => 'The website\'s header and footer navigation is built from these rows — structure decides rendering: an item with grandchildren becomes a mega menu, with children a dropdown, alone a plain link.',
        'steps' => [
            'Add a menu item with its label, URL and position (header, footer or footer-legal).',
            'Nest items under a parent to create dropdowns; nest two levels for a mega menu.',
            'Reorder items to change their sequence in the navigation.',
        ],
        'tips' => [
            'Footer root items render as column headings with their children below.',
        ],
    ],

    'cms.seo' => [
        'title' => 'How SEO settings work',
        'intro' => 'Meta titles and descriptions for the website\'s pages — what search engines display.',
        'steps' => [
            'Set a unique title and description per page.',
            'Keep titles under ~60 characters and descriptions under ~160.',
        ],
    ],

    'cms.content-block' => [
        'title' => 'How Content Blocks work',
        'intro' => 'Small reusable content sections used across the website — home services, why-us points, about values, visa steps and similar lists.',
        'steps' => [
            'Each block belongs to a section key (where it appears on the site).',
            'Edit the title, text and icon; the site section updates immediately.',
            'Add or remove blocks to lengthen or shorten a section.',
        ],
    ],

    'cms.visa-service' => [
        'title' => 'How Visa Services (website) work',
        'intro' => 'The visa-service cards shown on the public website — countries, requirements and fees you advertise.',
        'steps' => [
            'Add a card per country/visa type with fee and processing time.',
            'Keep fees current — this is marketing content, separate from actual visa applications.',
        ],
    ],

    'cms.flight-route' => [
        'title' => 'How Flight Routes (website) work',
        'intro' => 'Popular routes and partner airlines advertised on the website\'s flight page.',
        'steps' => [
            'Add routes with origin, destination and an indicative fare.',
            'The airlines you enter also feed the partner-airline strip on the site.',
        ],
    ],

    'cms.transport-service' => [
        'title' => 'How Transport Services (website) work',
        'intro' => 'The transport offerings advertised on the public website.',
        'steps' => [
            'Add a card per vehicle type with its description and image.',
            'The public "from" price comes from the cheapest actual booking of that type.',
        ],
    ],

    'cms.job-opening' => [
        'title' => 'How Job Openings work',
        'intro' => 'Vacancies published on the website\'s careers page — applications land in CRM → Job Applications.',
        'steps' => [
            'Post the opening with title, description and deadline.',
            'Review applications under CRM as they arrive.',
            'Close the opening once filled.',
        ],
    ],

    /* ------------------------------------------------ Administration */

    'role' => [
        'title' => 'How Roles & Permissions work',
        'intro' => 'Roles bundle permissions; users get a role and inherit everything in it. What someone can see and do anywhere in this system is decided here.',
        'steps' => [
            'Create a role (e.g. Manager, Accountant, Reservation).',
            'Tick the permissions the role should have, module by module.',
            'Assign the role when creating users — change the role and every holder updates at once.',
        ],
        'tips' => [
            'Give the minimum permissions a job needs — it is easier to add later than to audit excess.',
        ],
    ],

    'user' => [
        'title' => 'How Users work',
        'intro' => 'Staff accounts for this admin panel. Each user has a role that controls exactly which modules they can open.',
        'steps' => [
            'Create the user with email and password.',
            'Assign a role — permissions come entirely from it.',
            'Deactivate a user to block login without deleting their history.',
        ],
    ],

    'language' => [
        'title' => 'How Languages work',
        'intro' => 'Manage interface languages and translate every phrase used in the panel.',
        'steps' => [
            'Add a language with its name and direction (LTR/RTL).',
            'Open its phrase editor and translate module by module.',
            'Users switch language from the top bar.',
        ],
    ],

    'branch' => [
        'title' => 'How Branches work',
        'intro' => 'Physical offices of your agency — used to organise staff and business by location.',
        'steps' => [
            'Add each branch with address and contact.',
            'Assign staff and business records to their branch.',
        ],
    ],

    'activity.logs' => [
        'title' => 'How Activity Logs work',
        'intro' => 'A read-only audit trail — who did what, where and when across the panel.',
        'steps' => [
            'Filter by user, module or date to investigate a change.',
            'Use it to answer "who edited this?" — entries cannot be modified.',
        ],
    ],

    'login.activity' => [
        'title' => 'How Login Activity works',
        'intro' => 'Every sign-in to the panel — user, time, IP and device — for spotting anything unusual.',
        'steps' => [
            'Scan for logins at odd hours or from unknown locations.',
            'If something looks wrong, reset that user\'s password and review their role.',
        ],
    ],

    'profile' => [
        'title' => 'How your Profile works',
        'intro' => 'Your own account — name, photo and password.',
        'steps' => [
            'Update your details and photo here.',
            'Change your password regularly; it takes effect immediately.',
        ],
    ],

    /* ------------------------------------------------ Settings */

    'settings.general' => [
        'title' => 'How General Settings work',
        'intro' => 'Site-wide identity and contact details — name, logo, address, phones, social links and the stats shown on the website.',
        'steps' => [
            'Fill in your agency identity: name, tagline, logo and favicon.',
            'Set contact details — these appear on the public website\'s header, footer and contact page.',
            'Save; changes reflect on the site immediately.',
        ],
    ],

    'settings.mail' => [
        'title' => 'How Mail Settings work',
        'intro' => 'The SMTP account this system sends email through — password resets, notifications and campaign mail.',
        'steps' => [
            'Enter your SMTP host, port, username and password.',
            'Set the from-name and from-address recipients will see.',
            'Use the test-send button to confirm delivery before relying on it.',
        ],
    ],

    'settings.recaptcha' => [
        'title' => 'How reCAPTCHA Settings work',
        'intro' => 'Google reCAPTCHA keys that protect public forms (login, contact) from bots.',
        'steps' => [
            'Create a site at Google reCAPTCHA and copy the site key and secret key here.',
            'Enable it; public forms start verifying automatically.',
        ],
    ],

    'settings.social' => [
        'title' => 'How Social Login Settings work',
        'intro' => 'OAuth credentials that let customers sign in with Google or Facebook instead of a password.',
        'steps' => [
            'Create an app on the provider\'s developer console.',
            'Paste the client ID and secret here and enable the provider.',
            'The login page shows the social button once enabled.',
        ],
    ],

    /* ------------------------------------------------ Portals */

    'staff' => [
        'title' => 'How the Staff Portal works',
        'intro' => 'Your personal workspace — your attendance, leave, payslips and assigned tasks in one place.',
        'steps' => [
            'Check your dashboard for today\'s summary.',
            'Apply for leave and follow its approval status.',
            'Work through the tasks assigned to you and update their progress.',
        ],
    ],

    'cust' => [
        'title' => 'How the Customer Portal works',
        'intro' => 'A customer\'s self-service view — their bookings, payments, documents, visa status and wallet.',
        'steps' => [
            'Browse tours and make bookings.',
            'Pay from the wallet or record other payment methods.',
            'Track visa applications and download documents.',
        ],
    ],

];
