<?php

use App\Enums\Gender;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('address')->comment('Birth date');
            // Nullable: app sign-up does not force a gender.
            $table->enum('gender', array_column(Gender::cases(), 'value'))->nullable()->after('date_of_birth');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['date_of_birth', 'gender']);
        });
    }
};
