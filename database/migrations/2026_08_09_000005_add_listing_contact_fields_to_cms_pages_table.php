<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->string('search_placeholder')->nullable()->after('stat_experience');
            $table->string('all_label')->nullable()->after('search_placeholder');
            $table->string('read_more_label')->nullable()->after('all_label');
            $table->string('empty_text', 500)->nullable()->after('read_more_label');
            $table->string('filtered_empty_text', 500)->nullable()->after('empty_text');
            $table->string('contact_address_label')->nullable()->after('filtered_empty_text');
            $table->string('contact_phone_label')->nullable()->after('contact_address_label');
            $table->string('contact_email_label')->nullable()->after('contact_phone_label');
            $table->string('form_message_label')->nullable()->after('form_notes_placeholder');
            $table->string('form_message_placeholder', 500)->nullable()->after('form_message_label');
            $table->longText('subject_options')->nullable()->after('form_message_placeholder');
        });

        DB::table('cms_pages')->updateOrInsert(
            ['slug' => 'blog'],
            [
                'title' => 'Travel Blog',
                'meta_description' => 'Travel tips, destination guides and visa advice from our team to help you plan smarter trips.',
                'body' => null,
                'breadcrumb_label' => 'Blog',
                'hero_subtitle' => 'Tips, guides and inspiration for your next journey.',
                'search_placeholder' => 'Search articles...',
                'all_label' => 'All',
                'read_more_label' => 'Read more',
                'empty_text' => 'No articles published yet - check back soon.',
                'filtered_empty_text' => 'No articles matched your search.',
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('cms_pages')->updateOrInsert(
            ['slug' => 'contact-us'],
            [
                'title' => 'Contact Us',
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
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->dropColumn([
                'search_placeholder',
                'all_label',
                'read_more_label',
                'empty_text',
                'filtered_empty_text',
                'contact_address_label',
                'contact_phone_label',
                'contact_email_label',
                'form_message_label',
                'form_message_placeholder',
                'subject_options',
            ]);
        });
    }
};
