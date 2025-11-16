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
        Schema::create('follow_up_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sequence_id')->constrained('follow_up_sequences')->onDelete('cascade');
            $table->integer('step_number');
            $table->integer('delay_days');
            $table->enum('action_type', ['whatsapp', 'email', 'task', 'note']);
            $table->text('message_template')->nullable();
            $table->string('subject')->nullable();
            $table->timestamps();

            $table->index('sequence_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follow_up_steps');
    }
};
