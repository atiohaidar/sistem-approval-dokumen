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
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->bigInteger('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->foreignId('template_id')->nullable()->constrained('document_templates')->nullOnDelete();
            $table->enum('status', ['draft', 'pending_approval', 'completed', 'rejected', 'cancelled'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->json('approvers')->nullable();
            $table->integer('current_level')->default(1);
            $table->json('level_progress')->nullable();
            $table->float('qr_x')->nullable();
            $table->float('qr_y')->nullable();
            $table->integer('qr_page')->nullable()->default(1);
            $table->float('qr_size')->nullable();
            $table->string('qr_code_path')->nullable();
            $table->boolean('public_access')->default(false);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_templates');
    }
};
