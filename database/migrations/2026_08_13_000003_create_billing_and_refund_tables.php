<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Brings the rest of the money into the books.
     *
     * Three holes are closed here:
     *
     *  - a booking marked paid in the back office produced no invoice and no
     *    receipt, so a sale the agency had actually made never reached the
     *    ledger at all (the mobile app was the only path that billed)
     *  - a cancelled booking credited the customer's wallet while its invoice
     *    stayed "paid" and the sale stayed on the books: refunds had nowhere
     *    to be recorded
     *  - the customer wallet was money the agency was holding for people, and
     *    it appeared nowhere in the accounts
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // How the customer paid, used when the booking is marked paid to
            // record the receipt against the right cash/bank account.
            $table->string('payment_method', 20)->nullable()->after('status');
        });

        Schema::table('invoices', function (Blueprint $table) {
            // Maintained from the invoice's refunds, the way paid_amount is
            // maintained from its receipts.
            $table->decimal('refunded_amount', 12, 2)->default(0)->after('paid_amount');
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            // What produced this line: a receipt paid from the wallet, a
            // refund credited to it, or nothing at all for a plain top-up —
            // which is the only kind that posts to the journal on its own.
            $table->string('source_type', 100)->nullable()->after('description');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->string('method', 30)->nullable()->after('source_id');
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->nullable()->index();
            $table->unsignedBigInteger('booking_id')->nullable()->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('reference')->unique();
            $table->decimal('amount', 12, 2);
            // Where the money went back to: Cash, Bank or the customer's wallet.
            $table->string('method', 30)->default('Cash');
            $table->date('refunded_on');
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropIndex(['source_type', 'source_id']);
            $table->dropColumn(['source_type', 'source_id', 'method']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('refunded_amount');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
};
