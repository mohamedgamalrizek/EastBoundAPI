<?php

namespace Tests\Feature;

use App\Http\Requests\Dmc\SaveWorkflowRequest;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LeadQuotationTest extends TestCase
{
    public function test_quotation_context_rejects_other_leads_and_closed_opportunities(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->integer('assigned_to');
            $table->softDeletes();
        });
        Schema::create('dmc_opportunities', function (Blueprint $table) {
            $table->id();
            $table->integer('lead_id');
            $table->string('stage');
        });
        DB::table('leads')->insert([['id' => 1, 'assigned_to' => 10], ['id' => 2, 'assigned_to' => 20]]);
        DB::table('dmc_opportunities')->insert([
            ['id' => 1, 'lead_id' => 1, 'stage' => 'New'],
            ['id' => 2, 'lead_id' => 2, 'stage' => 'New'],
            ['id' => 3, 'lead_id' => 1, 'stage' => 'Won'],
        ]);
        $user = new User;
        $user->id = 10;
        $user->permissions = ['lead_read', 'dmc_sales_create'];
        $this->actingAs($user);
        foreach ([[1, 1, true], [1, 2, false], [1, 3, false], [2, 2, false]] as [$lead, $opportunity, $valid]) {
            $request = SaveWorkflowRequest::create('/dmc/quotations', 'POST', ['quotation_lead_id' => $lead, 'opportunity_id' => $opportunity]);
            $request->setUserResolver(fn () => $user);
            $route = new Route('POST', 'dmc/quotations', fn () => null);
            $route->defaults('module', 'quotations');
            $route->bind($request);
            $request->setRouteResolver(fn () => $route);
            $rules = array_intersect_key($request->rules(), array_flip(['quotation_lead_id', 'opportunity_id']));
            $this->assertSame($valid, Validator::make($request->all(), $rules)->passes());
        }
    }
}
