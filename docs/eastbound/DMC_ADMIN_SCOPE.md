# Eastbound DMC admin scope

The internal admin panel focuses on CRM, sales and lead campaigns, website/language attribution, hotels, transport, guides, tour programmes, suppliers and accounting. The approved source network contains six websites, managed through Websites & Lead Tracking Integration → Company Websites Management. The registry is also included in lead attribution reports; `config/lead-reporting.php` remains compatible with historical source labels.

Services (insurance, student consultancy, medical tourism, corporate travel and events/bookings), pilgrimage, visa processing, retail flight ticketing, coupons, reviews and newsletter subscriber management are excluded from the admin panel.

`App\Support\DmcModules` disables module permissions even for previously assigned admin permissions. The existing permission middleware returns 404 for excluded module routes and shared-permission entry points, including newsletter subscribers, flight reports/catalogues/settings and CRM flight conversion. Role and user permission selectors exclude those modules. Lead campaigns remain within the CRM/sales navigation.

The customer profile no longer loads or displays the removed services. The booking form hides coupon entry and preserves historical booking adjustments when the omitted field is saved. Existing records, financial documents and ledger entries are retained. Historical flight revenue remains part of lead attribution reporting; removing the retail flight module does not rewrite recorded sales.

Hotels, transport and airport transfers, guides, schedules and tour programmes remain accessible through their existing permissions. The retired Flight Management module is a retail ticketing workflow. Domestic flights are supported as supplier-confirmed service lines within DMC quotations, trips and purchasing; this does not restore retail ticketing or add GDS ticket issuance. See [DMC workflow runbook](DMC_WORKFLOW_RUNBOOK.md).

These exclusions apply to the internal admin panel. Public website and mobile/portal workflows retain their existing routes.
