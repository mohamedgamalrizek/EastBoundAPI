<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trip_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('destination');
            $table->date('start_date');
            $table->unsignedSmallInteger('days');
            $table->string('budget')->nullable();
            $table->text('interests')->nullable();
            $table->json('plan');
            $table->timestamps();
        });

        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->integer('points');
            $table->string('type', 30);
            $table->string('description');
            $table->nullableMorphs('source');
            $table->string('reference')->unique();
            $table->timestamps();
            $table->index(['customer_id', 'created_at']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('referral_code', 20)->nullable()->unique()->after('tier');
            $table->foreignId('referred_by')->nullable()->after('referral_code')->constrained('customers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by');
            $table->dropUnique(['referral_code']);
            $table->dropColumn('referral_code');
        });
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('trip_plans');
    }
};
