<?php

namespace Tests\Feature;

use App\Models\Lead;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LeadSoftDeleteTest extends TestCase
{
    public function test_deleted_leads_are_hidden_restorable_and_permanently_deletable(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'activitylog.enabled' => false]);
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        $lead = Lead::create(['name' => 'Soft delete test']);
        $lead->delete();
        $this->assertNull(Lead::find($lead->id));
        $this->assertSame(1, Lead::onlyTrashed()->count());
        $this->assertSame('Soft delete test', Lead::onlyTrashed()->findOrFail($lead->id)->name);

        Lead::onlyTrashed()->findOrFail($lead->id)->restore();
        $this->assertNotNull(Lead::find($lead->id));
        $this->assertSame(0, Lead::onlyTrashed()->count());

        $lead->fresh()->delete();
        Lead::onlyTrashed()->findOrFail($lead->id)->forceDelete();
        $this->assertSame(0, Lead::withTrashed()->count());
    }
}
