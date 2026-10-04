<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Insurance, student, medical and corporate records named their client in a
     * free-text column and stopped there: a sale could not be seen on the
     * customer's profile, and nothing recorded what the agency earned on it.
     *
     * Event tours are deliberately excluded — that table is a product listing
     * (title, seats, event date), not a client record, so it needs bookings
     * rather than a customer column.
     */
    private const TABLES = [
        'insurances',
        'student_services',
        'medical_tours',
        'corporate_travels',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->unsignedBigInteger('customer_id')->nullable()->after('id')->index();
                // What the agency keeps, as opposed to the premium/cost/budget
                // figures already stored, which are the client's total outlay.
                $blueprint->decimal('service_fee', 12, 2)->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn(['customer_id', 'service_fee']);
            });
        }
    }
};
