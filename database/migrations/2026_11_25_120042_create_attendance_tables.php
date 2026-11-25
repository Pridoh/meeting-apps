<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', fn (Blueprint $table) => $table->dateTime('schedule_version_at', 6)->nullable());
        DB::table('meetings')->update(['schedule_version_at' => DB::raw('CASE WHEN schedule_version = 1 THEN created_at ELSE updated_at END')]);

        Schema::create('attendance_choices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('meeting_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->unsignedInteger('schedule_version');
            $table->string('choice');
            $table->string('source');
            $table->boolean('is_late');
            $table->dateTime('created_at', 6);
            $table->index(['user_id', 'meeting_id', 'schedule_version', 'id'], 'attendance_choice_history');
        });

        Schema::create('attendance_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('meeting_id')->constrained();
            $table->unsignedInteger('schedule_version');
            $table->dateTime('deadline_at');
            $table->unsignedInteger('confirmed_count');
            $table->unsignedInteger('uncertain_count');
            $table->unsignedInteger('not_attending_count');
            $table->unsignedInteger('unchosen_count');
            $table->dateTime('taken_at');
            $table->unique(['meeting_id', 'schedule_version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_snapshots');
        Schema::dropIfExists('attendance_choices');
        Schema::table('meetings', fn (Blueprint $table) => $table->dropColumn('schedule_version_at'));
    }
};
