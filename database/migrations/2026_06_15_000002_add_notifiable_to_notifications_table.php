<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Make notifications targetable to any account (Customer or User) without
 * losing the legacy `user_id`. The mobile API uses notifiable_type/_id;
 * a NULL owner = a general/broadcast notice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('notifiable_type')->nullable()->after('user_id');
            $table->unsignedBigInteger('notifiable_id')->nullable()->after('notifiable_type');
            $table->index(['notifiable_type', 'notifiable_id']);
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['notifiable_type', 'notifiable_id']);
            $table->dropColumn(['notifiable_type', 'notifiable_id']);
        });
    }
};
