<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('timezone')->default('Asia/Jakarta');
            $table->enum('mode', ['online', 'offline']);
            $table->string('location')->nullable();
            $table->foreignId('room_id')->nullable()->constrained();
            $table->enum('room_status', ['none', 'pending', 'confirmed'])->default('none');
            $table->enum('status', ['scheduled', 'ongoing', 'completed'])->default('scheduled');
            $table->unsignedInteger('schedule_version')->default(1);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'status', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
