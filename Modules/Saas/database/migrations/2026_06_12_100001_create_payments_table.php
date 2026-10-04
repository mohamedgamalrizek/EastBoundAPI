<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! config('saas.enabled')) {
            return; // SaaS mode off → SaaS tables are not created
        }

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('gateway');                       // bkash | stripe | razorpay ...
            $table->string('reference')->unique();           // our id sent to the gateway
            $table->string('gateway_ref')->nullable();       // the gateway's transaction id
            $table->string('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('plan_id')->nullable()->index();
            $table->unsignedBigInteger('subscription_id')->nullable()->index();
            $table->string('payer_name')->nullable();
            $table->string('payer_email')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 8)->default('BDT');
            $table->string('status')->default('pending');    // pending | success | failed | cancelled
            $table->json('payload')->nullable();             // raw gateway responses
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
