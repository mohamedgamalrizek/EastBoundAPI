<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dmc_websites', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('domain')->nullable()->unique();
            $table->string('language')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        foreach (['Eastbound', 'English Flow', 'Spanish Website', 'Italian Website', 'French Website', 'Website 5'] as $name) {
            DB::table('dmc_websites')->insert(['name' => $name, 'slug' => Str::slug($name), 'created_at' => now(), 'updated_at' => now()]);
        }
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('dmc_website_id')->nullable()->constrained('dmc_websites')->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
            $table->string('lost_reason', 1000)->nullable();
        });
        Schema::create('dmc_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('position')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('dmc_opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('assigned_to')->constrained('users');
            $table->string('title');
            $table->string('stage')->default('Open')->index();
            $table->decimal('expected_value', 14, 2)->default(0);
            $table->date('expected_close')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('dmc_approval_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('minimum_margin', 5, 2)->nullable();
            $table->decimal('maximum_markup', 8, 2)->nullable();
            $table->decimal('team_leader_limit', 14, 2)->nullable();
            $table->timestamps();
        });
        DB::table('dmc_approval_policies')->insert(['name' => 'Standard', 'created_at' => now(), 'updated_at' => now()]);
        Schema::create('dmc_quotations', function (Blueprint $table) {
            $table->id();
            $table->uuid('series');
            $table->unsignedInteger('revision')->default(1);
            $table->unique(['series', 'revision']);
            $table->foreignId('opportunity_id')->constrained('dmc_opportunities');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->string('title');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->unsignedInteger('travelers')->default(1);
            $table->string('status')->default('Draft')->index();
            $table->decimal('total_cost', 14, 2)->default(0);
            $table->decimal('total_price', 14, 2)->default(0);
            $table->decimal('margin', 8, 2)->default(0);
            $table->decimal('markup', 10, 2)->nullable();
            $table->string('approval_level')->nullable();
            $table->text('approval_note')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
        Schema::create('dmc_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('dmc_quotations')->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers');
            $table->string('service_type');
            $table->string('description', 1000);
            $table->date('service_date');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_cost', 14, 2);
            $table->decimal('unit_price', 14, 2);
            $table->timestamps();
        });
        Schema::create('dmc_sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->unique()->constrained('dmc_quotations');
            $table->foreignId('booking_id')->unique()->constrained('bookings');
            $table->foreignId('confirmed_by')->constrained('users');
            $table->string('customer_confirmation');
            $table->timestamps();
        });
        Schema::create('dmc_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->unique()->constrained('dmc_sales_orders');
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('manager_id')->constrained('users');
            $table->string('title');
            $table->date('starts_on')->index();
            $table->date('ends_on');
            $table->string('status')->default('Planning')->index();
            $table->timestamps();
        });
        Schema::create('dmc_activities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers');
            $table->unsignedInteger('capacity')->default(1);
            $table->decimal('cost', 14, 2)->default(0);
            $table->decimal('price', 14, 2)->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('dmc_trip_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('dmc_trips');
            $table->foreignId('quotation_item_id')->unique()->constrained('dmc_quotation_items');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers');
            $table->foreignId('hotel_id')->nullable()->constrained('hotels');
            $table->foreignId('driver_id')->nullable()->constrained('drivers');
            $table->foreignId('guide_id')->nullable()->constrained('tour_guides');
            $table->foreignId('activity_id')->nullable()->constrained('dmc_activities');
            $table->date('service_date')->index();
            $table->string('confirmation')->nullable();
            $table->string('status')->default('Planned');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('dmc_purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('dmc_trips');
            $table->foreignId('trip_item_id')->unique()->constrained('dmc_trip_items');
            $table->string('description', 1000);
            $table->decimal('estimated_cost', 14, 2);
            $table->date('required_on');
            $table->string('status')->default('Requested');
            $table->timestamps();
        });
        Schema::create('dmc_rfqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('dmc_purchase_requests');
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->decimal('quoted_amount', 14, 2)->nullable();
            $table->date('valid_until');
            $table->string('status')->default('Draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('dmc_vendor_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->unique()->constrained('dmc_purchase_requests');
            $table->foreignId('rfq_id')->unique()->constrained('dmc_rfqs');
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('trip_id')->constrained('dmc_trips');
            $table->decimal('amount', 14, 2);
            $table->date('required_on');
            $table->string('status')->default('Issued');
            $table->foreignId('variance_approved_by')->nullable()->constrained('users');
            $table->text('variance_reason')->nullable();
            $table->timestamps();
        });
        Schema::create('dmc_vendor_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_order_id')->constrained('dmc_vendor_orders');
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('supplier_transaction_id')->nullable()->unique()->constrained('supplier_transactions');
            $table->string('reference');
            $table->unique(['supplier_id', 'reference']);
            $table->decimal('amount', 14, 2);
            $table->date('issued_on');
            $table->date('due_on');
            $table->timestamps();
        });
        Schema::create('dmc_vendor_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_bill_id')->constrained('dmc_vendor_bills');
            $table->foreignId('supplier_transaction_id')->nullable()->unique()->constrained('supplier_transactions');
            $table->uuid('idempotency_key')->unique();
            $table->decimal('amount', 14, 2);
            $table->date('paid_on');
            $table->string('method');
            $table->string('reference');
            $table->unique(['vendor_bill_id', 'reference']);
            $table->timestamps();
        });
        Schema::create('dmc_trip_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('dmc_trips');
            $table->string('kind');
            $table->unique(['trip_id', 'kind']);
            $table->foreignId('task_id')->nullable()->constrained('tasks');
            $table->timestamps();
        });
        Schema::create('dmc_campaign_spends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns');
            $table->foreignId('website_id')->constrained('dmc_websites');
            $table->date('spent_on');
            $table->decimal('amount', 14, 2);
            $table->string('reference');
            $table->unique(['campaign_id', 'website_id', 'reference']);
            $table->timestamps();
        });
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('payment_terms')->nullable();
            $table->string('currency', 3)->nullable();
            $table->text('bank_details')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
        });
        DB::table('permissions')->updateOrInsert(['attribute' => 'dmc_sales_approval'], [
            'keywords' => json_encode(['team_leader' => 'dmc_sales_approve_tl', 'general_manager' => 'dmc_sales_approve_gm']),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $root = DB::table('roles')->where('slug', 'super-admin')->first();
        if ($root) {
            $previous = json_decode($root->permissions, true) ?? [];
            $permissions = array_values(array_unique(array_merge($previous, ['dmc_sales_approve_tl', 'dmc_sales_approve_gm'])));
            DB::table('roles')->where('id', $root->id)->update(['permissions' => json_encode($permissions)]);
            foreach (DB::table('users')->where('role_id', $root->id)->get(['id', 'permissions']) as $user) {
                if ((json_decode($user->permissions, true) ?? []) === $previous) {
                    DB::table('users')->where('id', $user->id)->update(['permissions' => json_encode($permissions)]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('suppliers', fn (Blueprint $table) => $table->dropColumn(['payment_terms', 'currency', 'bank_details', 'rating']));
        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dmc_website_id');
            $table->dropConstrainedForeignId('campaign_id');
            $table->dropColumn('lost_reason');
        });
        foreach (['campaign_spends', 'trip_alerts', 'vendor_payments', 'vendor_bills', 'vendor_orders', 'rfqs', 'purchase_requests', 'trip_items', 'activities', 'trips', 'sales_orders', 'quotation_items', 'quotations', 'approval_policies', 'opportunities', 'contacts', 'websites'] as $table) {
            Schema::dropIfExists('dmc_'.$table);
        }
    }
};
