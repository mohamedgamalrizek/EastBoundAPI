<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('social_accounts')) {
            $indexes = collect(Schema::getIndexes('social_accounts'))->pluck('name')->all();

            if (in_array('social_accounts_user_id_unique', $indexes, true)
                && !in_array('social_accounts_user_id_index', $indexes, true)) {
                // MySQL requires an index for the foreign key. Add a normal
                // one before removing the old unique index.
                Schema::table('social_accounts', function (Blueprint $table) {
                    $table->index('user_id', 'social_accounts_user_id_index');
                });
            }

            if (in_array('social_accounts_user_id_unique', $indexes, true)) {
                Schema::table('social_accounts', function (Blueprint $table) {
                    $table->dropUnique('social_accounts_user_id_unique');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('social_accounts')) {
            $indexes = collect(Schema::getIndexes('social_accounts'))->pluck('name')->all();
            if (in_array('social_accounts_user_id_index', $indexes, true)) {
                Schema::table('social_accounts', function (Blueprint $table) {
                    $table->dropIndex('social_accounts_user_id_index');
                });
            }
            if (!in_array('social_accounts_user_id_unique', $indexes, true)) {
                Schema::table('social_accounts', function (Blueprint $table) {
                    $table->unique('user_id');
                });
            }
        }
    }
};
