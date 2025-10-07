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
            $table->json('approvers')->nullable()->after('total_steps');
            $table->enum('qr_position', ['top-left', 'top-right', 'bottom-left', 'bottom-right'])->nullable()->after('approvers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['approvers', 'qr_position']);
        });
    }
};
