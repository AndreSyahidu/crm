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
        Schema::create('daily_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->integer('new_leads')->default(0);
            $table->integer('qualified_leads')->default(0);
            $table->integer('won_deals')->default(0);
            $table->integer('lost_deals')->default(0);
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->integer('whatsapp_messages_sent')->default(0);
            $table->integer('whatsapp_messages_received')->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0);
            $table->decimal('average_deal_value', 15, 2)->default(0);
            $table->json('metrics')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_stats');
    }
};
