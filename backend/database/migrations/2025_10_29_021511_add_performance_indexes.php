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
        // Add indexes to documents table for better query performance
        Schema::table('documents', function (Blueprint $table) {
            // Only add if doesn't exist (handled by Laravel in newer versions)
            try {
                $table->index('status');
            } catch (\Exception $e) {}
            
            try {
                $table->index('created_by');
            } catch (\Exception $e) {}
            
            try {
                $table->index('created_at');
            } catch (\Exception $e) {}
            
            try {
                $table->index(['status', 'created_by']);
            } catch (\Exception $e) {}
            
            try {
                $table->index(['status', 'created_at']);
            } catch (\Exception $e) {}
        });

        // Add indexes to document_approvals table
        Schema::table('document_approvals', function (Blueprint $table) {
            try {
                $table->index('document_id');
            } catch (\Exception $e) {}
            
            try {
                $table->index('approver_id');
            } catch (\Exception $e) {}
            
            try {
                $table->index('status');
            } catch (\Exception $e) {}
            
            try {
                $table->index(['document_id', 'level']);
            } catch (\Exception $e) {}
            
            try {
                $table->index(['approver_id', 'status']);
            } catch (\Exception $e) {}
        });

        // Add indexes to users table
        Schema::table('users', function (Blueprint $table) {
            try {
                $table->index('email');
            } catch (\Exception $e) {}
            
            try {
                $table->index('role');
            } catch (\Exception $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'created_by']);
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('document_approvals', function (Blueprint $table) {
            $table->dropIndex(['document_id']);
            $table->dropIndex(['approver_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['document_id', 'level']);
            $table->dropIndex(['approver_id', 'status']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['role']);
        });
    }
};
