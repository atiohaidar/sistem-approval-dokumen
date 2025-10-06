<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('approval_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_approval_id')->constrained('document_approvals')->onDelete('cascade');
            $table->foreignId('step_id')->constrained('approval_steps');
            $table->foreignId('step_approver_id')->constrained('step_approvers');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('action', ['approve', 'reject', 'skip', 'delegate']);
            $table->json('action_data')->nullable();
            $table->text('notes')->nullable();
            $table->text('skip_reason')->nullable();
            $table->foreignId('delegated_to')->nullable()->constrained('users');
            $table->string('signature_hash', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_actions');
    }
};
