<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Online payments (bKash, SSLCOMMERZ).
 *
 * Every card/mobile-wallet payment starts here as a pending intent, before the
 * payer ever leaves for the provider's page. It exists so that:
 *
 *   - the provider's callback can be matched back to what was being paid — the
 *     redirect comes back on a fresh request with no session to trust;
 *   - a repeated or replayed callback settles the booking exactly once
 *     (`status` is the guard, and `reference` is unique);
 *   - a payment that was started but never finished is visible afterwards
 *     instead of vanishing.
 *
 * Settlement itself is unchanged: a confirmed intent flips its payable to paid
 * and the existing observers raise the invoice and receipt.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_intents', function (Blueprint $table) {
            $table->id();

            // Our own id for the transaction; what the provider echoes back.
            $table->string('reference', 40)->unique();

            $table->string('gateway', 30);            // bkash | sslcommerz
            $table->string('method', 30);             // receipt method: bKash | Card
            $table->nullableMorphs('payable');        // Booking | HotelBooking | TransportBooking
            $table->unsignedBigInteger('customer_id')->nullable()->index();

            $table->decimal('amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('BDT');

            $table->string('status', 20)->default('pending')->index(); // pending|paid|failed|cancelled
            $table->string('gateway_ref')->nullable();                 // provider's trx id
            $table->text('failure_reason')->nullable();

            // Provider-specific handles kept between initiate() and verify()
            // (bKash paymentID, SSLCOMMERZ sessionkey).
            $table->json('payload')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_intents');
    }
};
