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
        // Remove redundant qr_position JSON column since we have separate qr_x, qr_y, qr_page columns
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('qr_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore qr_position JSON column
        Schema::table('documents', function (Blueprint $table) {
            $table->json('qr_position')->nullable()->after('approvers');
        });
    }
};
