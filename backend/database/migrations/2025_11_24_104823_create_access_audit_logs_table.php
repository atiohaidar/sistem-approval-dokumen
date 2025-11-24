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
        Schema::create('access_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('access_token_id')->nullable()->constrained('document_access_tokens')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // NULL for anonymous access
            $table->string('action', 50)->index(); // 'view', 'download', 'preview'
            $table->string('ip_address', 45)->nullable(); // IPv6 compatible
            $table->text('user_agent')->nullable();
            $table->string('referer')->nullable();
            $table->boolean('success')->default(true)->index();
            $table->string('failure_reason')->nullable();
            $table->text('metadata')->nullable(); // JSON: additional context
            $table->timestamp('created_at')->index();
            
            // Composite indexes for common queries
            $table->index(['document_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['access_token_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_audit_logs');
    }
};
