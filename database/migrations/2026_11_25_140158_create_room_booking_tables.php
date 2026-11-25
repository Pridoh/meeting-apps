<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_reviewers', function (Blueprint $table): void {
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['room_id', 'user_id']);
        });
        Schema::create('room_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('meeting_id')->constrained();
            $table->foreignId('requester_id')->constrained('users');
            $table->foreignId('room_id')->constrained();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->text('purpose');
            $table->unsignedInteger('people_count');
            $table->unsignedInteger('version')->default(1);
            $table->string('status')->default('draft');
            $table->dateTime('submitted_at')->nullable();
            $table->timestamps();
            $table->index(['room_id', 'status', 'start_time', 'end_time']);
        });
        Schema::create('room_request_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_request_id')->constrained();
            $table->unsignedInteger('request_version');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedInteger('size');
            $table->index(['room_request_id', 'request_version']);
        });
        Schema::create('room_request_decisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_request_id')->constrained();
            $table->foreignId('reviewer_id')->constrained('users');
            $table->unsignedInteger('request_version');
            $table->string('result');
            $table->text('reason');
            $table->dateTime('decided_at');
            $table->unique(['room_request_id', 'request_version']);
        });
        Schema::create('room_reservations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_id')->constrained();
            $table->foreignId('room_request_id')->constrained()->unique();
            $table->foreignId('meeting_id')->constrained();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('status')->default('active');
            $table->dateTime('released_at')->nullable();
            $table->index(['room_id', 'status', 'start_time', 'end_time']);
        });
    }

    public function down(): void
    {
        foreach (['room_reservations', 'room_request_decisions', 'room_request_attachments', 'room_requests', 'room_reviewers'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
