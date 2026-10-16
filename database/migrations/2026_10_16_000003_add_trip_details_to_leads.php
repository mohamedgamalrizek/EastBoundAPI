<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('subject')->nullable();
            $table->string('destinations')->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->unsignedInteger('adults')->nullable();
            $table->unsignedInteger('children')->nullable();
            $table->string('budget', 100)->nullable();
            $table->text('comments')->nullable();
        });

        DB::table('leads')->where('notes', 'like', "Affiliate lead\n%")
            ->orderBy('id')->chunkById(100, function ($leads) {
                foreach ($leads as $lead) {
                    $details = [];
                    $sections = explode("\n\n", $lead->notes, 2);

                    foreach ([
                        'subject' => 'Subject',
                        'destinations' => 'Destinations',
                        'from_date' => 'From Date',
                        'to_date' => 'To Date',
                        'duration' => 'Duration',
                        'adults' => 'Adults',
                        'children' => 'Children',
                        'budget' => 'Budget',
                    ] as $field => $label) {
                        if (preg_match('/^'.preg_quote($label, '/').': ([^\r\n]*)$/m', $sections[0], $matches)) {
                            $details[$field] = $matches[1] === 'Not specified' ? null : $matches[1];
                            if ($details[$field] !== null && in_array($field, ['from_date', 'to_date'], true)) {
                                $details[$field] = Carbon::parse($details[$field])->toDateString();
                            }
                        }
                    }

                    $details['comments'] = $sections[1] ?? null;
                    DB::table('leads')->where('id', $lead->id)->update($details);
                }
            });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['subject', 'destinations', 'from_date', 'to_date', 'duration', 'adults', 'children', 'budget', 'comments']);
        });
    }
};
