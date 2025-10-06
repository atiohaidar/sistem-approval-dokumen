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
        Schema::create('approval_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flow_id')->constrained('approval_flows')->onDelete('cascade');
            $table->unsignedInteger('step_order');
            $table->string('step_name');
            $table->enum('step_type', ['sequential', 'parallel'])->default('sequential');
            $table->boolean('is_required')->default(true);
            $table->boolean('can_skip')->default(false);
            $table->unsignedInteger('minimum_approvers')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_steps');
    }
};
