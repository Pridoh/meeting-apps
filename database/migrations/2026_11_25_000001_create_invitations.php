<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('meeting_id')->constrained();
            $t->string('email');
            $t->foreignId('user_id')->nullable()->constrained();
            $t->char('token_hash', 64)->unique();
            $t->timestamp('expires_at');
            $t->string('status')->default('pending');
            $t->timestamp('responded_at')->nullable();
            $t->unsignedInteger('resend_count')->default(0);
            $t->timestamp('last_sent_at')->nullable();
            $t->string('active_email')->virtualAs("CASE WHEN status IN ('pending','accepted') THEN email ELSE NULL END");
            $t->unique(['meeting_id', 'active_email']);
            $t->timestamps();
        });
        Schema::create('invitation_deliveries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('invitation_id')->constrained();
            $t->string('status')->default('queued');
            $t->string('error_message')->nullable();
            $t->timestamp('attempted_at')->nullable();
            $t->timestamps();
        });
        Schema::table('meeting_participants', fn (Blueprint $t) => $t->foreignId('invitation_id')->nullable()->constrained());
    }

    public function down(): void
    {
        Schema::table('meeting_participants', fn (Blueprint $t) => $t->dropConstrainedForeignId('invitation_id'));
        Schema::dropIfExists('invitation_deliveries');
        Schema::dropIfExists('invitations');
    }
};
