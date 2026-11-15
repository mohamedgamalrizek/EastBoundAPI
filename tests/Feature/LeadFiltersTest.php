<?php

namespace Tests\Feature;

use App\Models\Lead;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LeadFiltersTest extends TestCase
{
    public function test_filters_combine_and_search_does_not_include_other_stages_or_deleted_leads(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            foreach (['name', 'phone', 'email', 'subject', 'interest', 'destinations', 'stage', 'language'] as $column) {
                $table->string($column)->nullable();
            }
            $table->integer('assigned_to')->nullable();
            $table->decimal('value')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
        foreach ([['New', null, 100], ['Lost', null, 100], ['New', '2026-11-13', 100], ['New', null, 200]] as [$stage, $deleted, $value]) {
            DB::table('leads')->insert(['name' => 'Cairo trip', 'stage' => $stage, 'language' => 'English', 'value' => $value,
                'created_at' => '2026-11-13 23:59:59', 'deleted_at' => $deleted]);
        }
        $filters = ['search' => 'Cairo', 'stage' => 'New', 'language' => 'English', 'assigned_to' => 'unassigned',
            'created_from' => '2026-11-13', 'created_to' => '2026-11-13', 'sort' => 'value_high'];
        $this->assertSame([4, 1], Lead::filtered($filters)->pluck('id')->all());
        $this->assertSame([1], Lead::filtered($filters + ['value_min' => 0, 'value_max' => 100])->pluck('id')->all());
        $this->assertSame(0, Lead::filtered(['search' => 'missing'])->count());
        $this->assertSame([1, 2, 4], Lead::filtered(['sort' => 'value_low'])->pluck('id')->sort()->values()->all());
    }
}
