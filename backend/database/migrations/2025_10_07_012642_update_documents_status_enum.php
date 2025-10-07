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
        // For SQLite, we need to recreate the column with new enum values
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->enum('status', ['draft', 'pending_approval', 'in_progress', 'completed', 'rejected', 'cancelled'])->default('draft');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->enum('status', ['draft', 'pending', 'in_progress', 'completed', 'rejected', 'cancelled'])->default('draft');
        });
    }
};
