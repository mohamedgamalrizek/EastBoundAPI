<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');                 // Airline | Hotel | Transport | Visa
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // Agreements with a supplier: rates, period and terms. A supplier can
        // hold several (e.g. a net-rate deal and a separate commission deal).
        Schema::create('supplier_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('contract_no')->unique();
            $table->string('title');
            $table->enum('rate_type', ['Fixed', 'Commission', 'Credit', 'Net rate'])->default('Fixed');
            $table->decimal('value', 15, 2)->default(0)->comment('contract value or agreed rate');
            $table->decimal('commission_rate', 5, 2)->nullable()->comment('% when rate_type = Commission');
            $table->unsignedSmallInteger('credit_days')->nullable()->comment('payment terms in days');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('terms')->nullable();
            $table->string('document')->nullable()->comment('scanned agreement, public disk');
            $table->enum('status', ['Draft', 'Active', 'Expired', 'Terminated'])->default('Active');
            $table->timestamps();

            $table->index(['supplier_id', 'status']);
        });

        // The supplier ledger: one row per bill, payment or adjustment.
        // Credit raises what the agency owes, debit settles it, and
        // `balance_after` is the running balance at that point, so a statement
        // prints without recomputing. `suppliers.balance` is kept in step with
        // these rows rather than typed by hand.
        Schema::create('supplier_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_contract_id')->nullable()->constrained()->nullOnDelete();
            $table->date('txn_date');
            $table->enum('type', ['Bill', 'Payment', 'Credit Note', 'Adjustment']);
            $table->string('reference')->nullable()->comment('invoice or voucher no');
            $table->string('description')->nullable();
            $table->decimal('debit', 15, 2)->default(0)->comment('reduces what we owe');
            $table->decimal('credit', 15, 2)->default(0)->comment('increases what we owe');
            $table->decimal('balance_after', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['supplier_id', 'txn_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_transactions');
        Schema::dropIfExists('supplier_contracts');
        Schema::dropIfExists('suppliers');
    }
};
