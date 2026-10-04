<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Closes the agent settlement loop.
     *
     * Until now a commission was a row somebody typed in, its status was a
     * dropdown, the wallet was written by nothing at all, and no money ever
     * left the agency: "paid" meant only that a word had changed. This adds
     * the three pieces that were missing:
     *
     *   - users.commission_rate — what the agent earns, so a commission can be
     *     worked out from the booking instead of being keyed in
     *   - agent_commissions.booking_id is already there; approved_on/paid_on
     *     record when it was credited to the wallet
     *   - agent_withdrawals — the agent asks for their money, the office
     *     approves and pays it, and only then does cash move
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Percent of the booking value an agent earns. NULL falls back to
            // the agency default in Settings (agent_commission_rate).
            $table->decimal('commission_rate', 5, 2)->nullable()->after('role_id');
        });

        Schema::table('agent_commissions', function (Blueprint $table) {
            // When the commission was credited to the agent's wallet.
            $table->date('approved_on')->nullable()->after('earned_on');
            // Auto-generated commissions are tied to the booking that earned
            // them, so re-saving a booking updates rather than duplicates.
            $table->boolean('is_auto')->default(false)->after('approved_on');
        });

        Schema::table('agent_wallet_transactions', function (Blueprint $table) {
            // Which commission or withdrawal put this line in the wallet, so
            // re-approving updates the line instead of adding a second one.
            $table->string('source_type', 100)->nullable()->after('description');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('agent_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id')->index();
            $table->string('reference')->unique();
            $table->decimal('amount', 12, 2);
            // How the agent wants the money: Cash, Bank, bKash, Nagad.
            $table->string('method', 30)->default('Bank');
            $table->string('account_details')->nullable();
            // requested -> approved -> paid, or rejected. Cash only moves, and
            // the wallet is only debited, on 'paid'.
            $table->string('status', 20)->default('requested')->index();
            $table->date('requested_on');
            $table->date('processed_on')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_withdrawals');

        Schema::table('agent_wallet_transactions', function (Blueprint $table) {
            $table->dropIndex(['source_type', 'source_id']);
            $table->dropColumn(['source_type', 'source_id']);
        });

        Schema::table('agent_commissions', function (Blueprint $table) {
            $table->dropColumn(['approved_on', 'is_auto']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('commission_rate');
        });
    }
};
