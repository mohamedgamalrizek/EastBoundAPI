# Lead language and website reporting

Open Reports → Lead Languages & Websites, or use the report button on CRM → Leads. The existing `report_read` permission controls access and CSV export.

The report uses the existing `leads.website` and `leads.language` fields. The four languages come from `Lead::LANGUAGES`. Website options include actual lead values and the approved websites configured in `config/lead-reporting.php`. Populate that list with the company's five approved names or domains to show zero-activity sites too; names must match the stored lead values. No website-to-language mapping is inferred. Empty attribution appears as `Unspecified`; legacy values remain visible.

Run `php artisan migrate` before opening the report. The additive migration adds nullable `lead_id` references to package, flight and hotel bookings. Lead conversion sets this reference automatically for new flight/hotel bookings. Staff with CRM access can also select the originating lead when creating or editing a booking, including old bookings. Historical records are not linked by email, name or free-text notes.

Date filters count leads created in the period and revenue from bookings created in the period independently. A booking created this month from an older lead still contributes this month's revenue. Website and language filters apply to both measures. Grouping reflects the lead's current attribution.

Booked revenue includes confirmed/paid package bookings, confirmed/paid hotel bookings, and confirmed/reissued standalone flights. A flight attached to a package booking is excluded to avoid counting that package twice. Pending, cancelled and refunded bookings are excluded. This measure is booking value, not cash receipts, profit or recognized accounting revenue. Opportunity value and Won lead value appear separately and are never presented as booked revenue.

Bookings without a lead reference are shown as unattributed revenue for the date range, independently of website/language filters. Existing financial documents and ledger postings are unchanged.
