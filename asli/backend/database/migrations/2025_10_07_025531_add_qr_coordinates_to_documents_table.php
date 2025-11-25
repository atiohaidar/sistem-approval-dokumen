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
            $table->float('qr_x')->nullable()->after('qr_position'); // 0.0 - 1.0 relative to page width
            $table->float('qr_y')->nullable()->after('qr_x');       // 0.0 - 1.0 relative to page height
            $table->integer('qr_page')->default(1)->after('qr_y');  // Page number (1-based)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['qr_x', 'qr_y', 'qr_page']);
        });
    }
};
