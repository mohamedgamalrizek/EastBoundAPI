<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CmsPage;

/**
 * CMS pages. The four legal slugs below are rendered by the public site
 * (privacy-policy, terms-conditions, refund-policy, cancellation-policy) —
 * their bodies are authored in Markdown and were migrated here out of the
 * old hardcoded blade templates.
 */
class CmsPageSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->pages() as $page) {
            CmsPage::updateOrCreate(['slug' => $page['slug']], $page);
        }
    }

    private function pages(): array
    {
        return [
            [
                'slug'             => 'privacy-policy',
                'title'            => 'Privacy Policy',
                'status'           => 'published',
                'meta_description' => 'How we collect, use and protect your personal information.',
                'body'             => <<<'MD'
Your privacy matters. This policy explains what information we collect, how we use it, and the choices you have.

## Information we collect

- Contact details you provide (name, phone, email).
- Booking and travel preferences.
- Payment information processed securely via our gateways.
- Usage data such as pages visited and device information.

## How we use your information

We use your information to process bookings, provide customer support, send relevant updates, and improve our services. We never sell your personal data to third parties.

## Data security

We apply industry-standard security measures, including encryption, to protect your data. Access is restricted to authorised personnel only.

## Your rights

You may request access to, correction of, or deletion of your personal data at any time by contacting us.

## Contact us

For privacy questions, email us or visit our [contact page](/contact-us).
MD,
            ],
            [
                'slug'             => 'terms-conditions',
                'title'            => 'Terms & Conditions',
                'status'           => 'published',
                'meta_description' => 'The terms and conditions governing the use of our services and bookings.',
                'body'             => <<<'MD'
By using our website and services, you agree to the following terms and conditions.

## 1. Bookings

All bookings are subject to availability and confirmation. A booking is only confirmed once the required deposit or full payment has been received.

## 2. Pricing

Prices are quoted in Bangladeshi Taka (৳) and may change due to currency fluctuations, supplier rates or taxes until a booking is confirmed.

## 3. Travel documents

It is the traveler's responsibility to ensure passports, visas and other documents are valid. We assist with visas but do not guarantee issuance.

## 4. Liability

We act as an intermediary between travelers and suppliers (airlines, hotels, etc.) and are not liable for losses caused by third-party suppliers or events beyond our control.

## 5. Changes & cancellations

Changes and cancellations are governed by our [Cancellation Policy](/cancellation-policy) and [Refund Policy](/refund-policy).

## 6. Governing law

These terms are governed by the laws of Bangladesh.
MD,
            ],
            [
                'slug'             => 'refund-policy',
                'title'            => 'Refund Policy',
                'status'           => 'published',
                'meta_description' => 'How and when refunds are processed for cancelled bookings.',
                'body'             => <<<'MD'
We want you to book with confidence. This policy explains how refunds are handled.

## Refund eligibility

Refunds depend on the type of booking, how far in advance you cancel, and the terms of suppliers such as airlines and hotels.

## Refund timeline

- Cancellations 30+ days before travel: up to 90% refund.
- 15–29 days before travel: up to 60% refund.
- 7–14 days before travel: up to 30% refund.
- Less than 7 days: non-refundable (supplier-dependent).

## Non-refundable items

Visa fees, issued air tickets and certain promotional packages may be non-refundable. These are clearly marked at the time of booking.

## How refunds are processed

Approved refunds are returned to your original payment method within 7–14 business days. See our [Cancellation Policy](/cancellation-policy) for related details.

## Request a refund

Contact our [support center](/support-center) and we will start the process.
MD,
            ],
            [
                'slug'             => 'cancellation-policy',
                'title'            => 'Cancellation Policy',
                'status'           => 'published',
                'meta_description' => 'How to cancel a booking and the charges that may apply.',
                'body'             => <<<'MD'
Plans change — here's how cancellations work.

## How to cancel

You can request a cancellation by contacting our support team or replying to your booking confirmation. Cancellations are effective from the time we receive your written request.

## Cancellation charges

- 30+ days before travel: 10% of the package value.
- 15–29 days before travel: 40% of the package value.
- 7–14 days before travel: 70% of the package value.
- Less than 7 days: up to 100% (supplier-dependent).

## Supplier terms

Airlines, hotels and other suppliers may apply their own cancellation rules, which can override the above. We always share applicable terms before you confirm.

## Refunds

Any refundable amount after cancellation charges is processed per our [Refund Policy](/refund-policy).

## Need help?

Contact our [support center](/support-center) and we'll guide you through the process.
MD,
            ],

            // Informational rows for the CMS page list.
            [
                'slug' => '/',
                'title' => 'Home',
                'status' => 'published',
                'meta_description' => null,
                'body' => null,
                'promo_badge' => 'Hot Deal',
                'promo_title' => 'Umrah Packages',
                'promo_text' => 'Premium packages from BDT 145,000 - limited seats.',
                'promo_link' => '/umrah',
                'stat_travelers' => '12K+',
                'stat_destinations' => '220+',
                'stat_visa_success' => '98%',
                'stat_experience' => '11 yrs',
            ],
            ['slug' => '/contact',  'title' => 'Contact Us', 'status' => 'published', 'meta_description' => null, 'body' => null],
            ['slug' => '/services', 'title' => 'Services',   'status' => 'published', 'meta_description' => null, 'body' => null],
            ['slug' => '/faq',      'title' => 'FAQ',        'status' => 'published', 'meta_description' => null, 'body' => null],
            [
                'slug' => 'about-us',
                'title' => 'About Us',
                'status' => 'published',
                'meta_description' => 'Tours, visas, flights and pilgrimages for thousands of travelers across Bangladesh and beyond.',
                'body' => null,
                'breadcrumb_label' => 'About Us',
                'hero_subtitle' => 'We make travel effortless, transparent and trustworthy.',
                'story_image' => '',
                'story_eyebrow' => 'Our story',
                'story_heading' => 'Built by travel people, for travelers',
                'story_lead' => 'From a single agency in Dhaka to a full travel platform - tours, visas, flights, hotels and Hajj & Umrah, all under one roof.',
                'story_body' => 'We combine deep local expertise with modern technology so every journey is simple to book and a joy to experience. Our mission is to make world-class travel accessible to everyone.',
                'story_badges' => 'IATA accredited, Govt. approved Hajj agency, 24/7 support',
                'stat_travelers' => '12K+',
                'stat_destinations' => '220+',
                'stat_visa_success' => '98%',
                'stat_experience' => '11 yrs',
            ],
            [
                'slug' => 'blog',
                'title' => 'Travel Blog',
                'status' => 'published',
                'meta_description' => 'Travel tips, destination guides and visa advice from our team to help you plan smarter trips.',
                'body' => null,
                'breadcrumb_label' => 'Blog',
                'hero_subtitle' => 'Tips, guides and inspiration for your next journey.',
                'search_placeholder' => 'Search articles...',
                'all_label' => 'All',
                'read_more_label' => 'Read more',
                'empty_text' => 'No articles published yet - check back soon.',
                'filtered_empty_text' => 'No articles matched your search.',
            ],
            [
                'slug' => 'contact-us',
                'title' => 'Contact Us',
                'status' => 'published',
                'meta_description' => 'Get in touch - call, email or send a message and our travel experts will get back to you shortly.',
                'body' => null,
                'breadcrumb_label' => 'Contact Us',
                'hero_subtitle' => "We'd love to help you plan your next journey.",
                'contact_address_label' => 'Visit us',
                'contact_phone_label' => 'Call us',
                'contact_email_label' => 'Email us',
                'form_title' => 'Send us a message',
                'form_intro' => "Fill in the form and we'll respond within one business day.",
                'form_name_label' => 'Full name',
                'form_name_placeholder' => 'Your name',
                'form_email_label' => 'Email',
                'form_email_placeholder' => 'you@email.com',
                'form_phone_label' => 'Phone',
                'form_phone_placeholder' => '+880 17...',
                'form_notes_label' => 'Subject',
                'form_notes_placeholder' => 'Type your subject',
                'form_message_label' => 'Message',
                'form_message_placeholder' => 'How can we help?',
                'form_submit_button' => 'Send message',
                'success_message' => 'Thanks! Your message has been sent - we will get back to you soon.',
                'subject_options' => "General enquiry\nTour packages\nPackage booking\nBooking support\nVisa services\nVisa application status\nFlight booking\nHotel booking\nTransport booking\nHajj & Umrah\nUmrah packages\nTravel insurance\nStudent consultancy\nMedical tourism\nCorporate travel\nEvents & conference\nBecome an agent\nAgent support\nPayment & invoice\nRefund & cancellation\nCustomer portal support\nTechnical support\nComplaint\nFeedback\nPartnership\nSupplier enquiry\nCareer / job application",
            ],
            [
                'slug' => 'become-an-agent',
                'title' => 'Become a Travel Agent',
                'status' => 'published',
                'meta_description' => 'Partner with FLOW. Earn attractive commissions, access wholesale rates and grow your travel business with our agent program.',
                'body' => null,
                'breadcrumb_label' => 'Become an Agent',
                'hero_subtitle' => 'Partner with FLOW and grow your travel business.',
                'section_icon' => 'fa-handshake',
                'section_eyebrow' => 'Agent program',
                'section_heading' => 'Why partner with us',
                'form_title' => 'Apply to become an agent',
                'form_intro' => 'Fill in your details and our partnerships team will get in touch.',
                'form_name_label' => 'Full name / Company',
                'form_name_placeholder' => 'Your name or agency',
                'form_phone_label' => 'Phone',
                'form_phone_placeholder' => '+880 17...',
                'form_email_label' => 'Email',
                'form_email_placeholder' => 'you@email.com',
                'form_notes_label' => 'Tell us about your business',
                'form_notes_placeholder' => 'Location, experience, monthly volume...',
                'form_submit_button' => 'Submit application',
                'success_message' => 'Application received! Our partnerships team will reach out to you shortly.',
            ],
        ];
    }
}
