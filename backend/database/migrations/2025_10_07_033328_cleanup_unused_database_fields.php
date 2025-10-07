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
        // Remove unused fields from document_templates table
        Schema::table('document_templates', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['default_approval_flow_id']);
            // Then drop the columns
            $table->dropColumn(['file_path', 'is_active', 'default_approval_flow_id']);
        });

        // Drop the entire approval_flows table as it's not used
        Schema::dropIfExists('approval_flows');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate approval_flows table
        Schema::create('approval_flows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_template_default')->default(false);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Restore removed fields to document_templates table
        Schema::table('document_templates', function (Blueprint $table) {
            $table->string('file_path', 500)->nullable()->after('description');
            $table->boolean('is_active')->default(true)->after('file_path');
            $table->foreignId('default_approval_flow_id')->nullable()->constrained('approval_flows')->onDelete('set null')->after('is_active');
        });
    }
};
