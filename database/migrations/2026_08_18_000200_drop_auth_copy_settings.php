<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Auth screen copy moved from the settings table to lang/<code>/auth.json,
 * so it can be translated per language instead of being one fixed string.
 * Drops the now-orphaned rows; the demo login toggle stays, it is a feature
 * flag and not translatable text.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        DB::table('settings')->whereIn('key', [
            'auth_brand_name',
            'auth_brand_features',
            'auth_brand_feature_icon',
            'auth_quote_stars',
            'auth_quote_text',
            'auth_quote_name',
            'auth_quote_role',
            'auth_login_brand_heading',
            'auth_login_brand_subtitle',
            'auth_login_top_text',
            'auth_login_top_link',
            'auth_login_form_title',
            'auth_login_form_intro',
            'auth_login_submit_button',
            'auth_divider_text',
            'auth_login_signup_text',
            'auth_login_signup_link',
            'auth_login_admin_link',
            'auth_register_brand_heading',
            'auth_register_brand_subtitle',
            'auth_register_top_text',
            'auth_register_top_link',
            'auth_register_form_title',
            'auth_register_form_intro',
            'auth_register_submit_button',
            'auth_register_signin_text',
            'auth_register_signin_link',
            'auth_verify_brand_heading',
            'auth_verify_brand_subtitle',
            'auth_verify_form_title',
            'auth_verify_form_intro',
            'auth_verify_code_label',
            'auth_verify_code_placeholder',
            'auth_verify_submit_button',
            'auth_verify_divider_text',
            'auth_verify_resend_text',
            'auth_verify_resend_link',
            'auth_admin_brand_heading',
            'auth_admin_brand_subtitle',
            'auth_admin_top_text',
            'auth_admin_top_link',
            'auth_admin_form_title',
            'auth_admin_form_intro',
            'auth_admin_submit_button',
            'auth_demo_label',
            'auth_demo_hint',
        ])->delete();
    }

    public function down(): void
    {
        // The values live in lang/*/auth.json now; nothing to restore.
    }
};
