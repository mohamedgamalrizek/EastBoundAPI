<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dmc_trip_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('dmc_trips');
            $table->foreignId('expense_account_id')->constrained('accounts');
            $table->string('title');
            $table->decimal('amount', 14, 2);
            $table->date('spent_on');
            $table->string('method');
            $table->string('reference');
            $table->uuid('idempotency_key')->unique();
            $table->timestamps();
        });
        $permissions = [];
        foreach (['dmc_sales', 'dmc_operations', 'dmc_procurement', 'dmc_marketing'] as $module) {
            $keywords = [];
            foreach (['read', 'create', 'update'] as $action) {
                $keywords[$action] = $module.'_'.$action;
                $permissions[] = $module.'_'.$action;
            }
            DB::table('permissions')->updateOrInsert(['attribute' => $module], ['keywords' => json_encode($keywords), 'created_at' => now(), 'updated_at' => now()]);
        }
        $root = DB::table('roles')->where('slug', 'super-admin')->first();
        if ($root) {
            $previous = json_decode($root->permissions, true) ?? [];
            $updated = array_values(array_unique(array_merge($previous, $permissions)));
            DB::table('roles')->where('id', $root->id)->update(['permissions' => json_encode($updated)]);
            foreach (DB::table('users')->where('role_id', $root->id)->get(['id', 'permissions']) as $user) {
                if ((json_decode($user->permissions, true) ?? []) === $previous) {
                    DB::table('users')->where('id', $user->id)->update(['permissions' => json_encode($updated)]);
                }
            }
        }
        foreach (['EG' => 'Egypt Branch', 'JO' => 'Jordan Branch'] as $code => $name) {
            if (! DB::table('branches')->where('code', $code)->exists()) {
                DB::table('branches')->insert(['name' => $name, 'code' => $code, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dmc_trip_expenses');
    }
};
