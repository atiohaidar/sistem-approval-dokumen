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
            $table->enum('qr_position', ['top-left', 'top-right', 'bottom-left', 'bottom-right', 'center'])->default('bottom-right')->after('total_steps');
            $table->string('qr_code_path', 500)->nullable()->after('qr_position');
            $table->text('qr_code_data')->nullable()->after('qr_code_path');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['qr_position', 'qr_code_path', 'qr_code_data']);
        });
    }
};
