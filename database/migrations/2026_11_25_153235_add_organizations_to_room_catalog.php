<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_id')->constrained('users');
            $table->string('name');
            $table->string('category');
            $table->string('join_code', 24)->unique();
            $table->timestamps();
        });
        Schema::create('organization_memberships', function (Blueprint $table): void {
            $table->foreignId('organization_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->timestamp('created_at');
            $table->primary(['organization_id', 'user_id']);
        });
        Schema::create('buildings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained();
            $table->string('name');
            $table->string('location')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['organization_id', 'name']);
        });
        Schema::table('rooms', function (Blueprint $table): void {
            $table->foreignId('building_id')->nullable()->constrained();
            $table->unique(['building_id', 'name']);
            $table->dropUnique(['location', 'name']);
        });
        Schema::create('room_manager_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_id')->constrained();
            $table->foreignId('applicant_id')->constrained('users');
            $table->string('status')->default('pending');
            $table->foreignId('decided_by')->nullable()->constrained('users');
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            $table->index(['room_id', 'applicant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_manager_applications');
        Schema::table('rooms', function (Blueprint $table): void {
            $table->dropUnique(['building_id', 'name']);
            $table->dropConstrainedForeignId('building_id');
        });
        Schema::dropIfExists('buildings');
        Schema::dropIfExists('organization_memberships');
        Schema::dropIfExists('organizations');
    }
};
