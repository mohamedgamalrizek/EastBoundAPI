<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Three gaps closed in one pass, because they all hang off a booking:
 *
 *  - a coupon that can actually be spent (the module was CRUD-only; no
 *    booking path ever looked at it),
 *  - loyalty points that can be spent as well as earned,
 *  - a review a customer may only write for a trip they actually paid for.
 *
 * `bookings.amount` keeps its meaning — what the customer owes — so the
 * billing observers, invoices, receipts, the ledger and agent commission all
 * carry on unchanged and simply see the discounted figure. `gross_amount`
 * records what it would have been before any discount, which is what the
 * invoice needs in order to show the saving.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('gross_amount', 12, 2)->default(0)->after('amount');
            $table->foreignId('coupon_id')->nullable()->after('gross_amount')->constrained('coupons')->nullOnDelete();
            $table->string('coupon_code', 60)->nullable()->after('coupon_id');
            $table->decimal('coupon_discount', 12, 2)->default(0)->after('coupon_code');
            $table->unsignedInteger('points_redeemed')->default(0)->after('coupon_discount');
            $table->decimal('points_discount', 12, 2)->default(0)->after('points_redeemed');
        });

        // Existing bookings were never discounted, so their list price is the
        // amount they already carry. Without this every historical booking
        // would report a gross of 0 and a 100% saving.
        DB::table('bookings')->update(['gross_amount' => DB::raw('amount')]);

        Schema::table('coupons', function (Blueprint $table) {
            // usage_limit was enforced nowhere because nothing counted uses.
            $table->unsignedInteger('used_count')->default(0)->after('usage_limit');
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            // Nullable so the office can still publish a testimonial it
            // collected offline; a review WITH a booking is the verified one
            // and is the only kind a customer can write themselves.
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->unsignedTinyInteger('rating');            // 1..5
            $table->string('title')->nullable();
            $table->text('comment')->nullable();
            $table->string('status', 20)->default('pending'); // pending | approved | rejected
            $table->text('reply')->nullable();                // the agency's public answer
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();

            // One review per trip.
            $table->unique(['booking_id', 'customer_id'], 'review_booking_customer_unique');
            $table->index(['package_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('used_count');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('coupon_id');
            $table->dropColumn(['gross_amount', 'coupon_code', 'coupon_discount', 'points_redeemed', 'points_discount']);
        });
    }
};
