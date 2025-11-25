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
        Schema::table('documents', function (Blueprint $table) {
            // Drop the enum constraint and change to json
            $table->dropColumn('qr_position');
            $table->json('qr_position')->nullable()->after('approvers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Revert back to enum
            $table->dropColumn('qr_position');
            $table->enum('qr_position', ['top-left', 'top-right', 'bottom-left', 'bottom-right'])->nullable()->after('approvers');
        });
    }
};
