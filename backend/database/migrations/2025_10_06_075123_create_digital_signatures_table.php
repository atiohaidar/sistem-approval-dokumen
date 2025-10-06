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
        Schema::create('digital_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('approval_action_id')->constrained('approval_actions');
            $table->string('signature_hash', 255);
            $table->string('qr_code_path', 500)->nullable();
            $table->text('qr_code_data');
            $table->string('verification_url', 500)->nullable();
            $table->boolean('is_valid')->default(true);
            $table->timestamp('signed_at')->useCurrent();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_signatures');
    }
};
