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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 50);
            $table->string('email')->nullable();
            $table->string('whatsapp_number', 50)->nullable();
            $table->string('company')->nullable();
            $table->string('position')->nullable();
            $table->enum('source', ['whatsapp', 'manual', 'website', 'referral', 'other'])->default('whatsapp');
            $table->enum('status', ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost'])->default('new');
            $table->integer('lead_score')->default(0);
            $table->decimal('expected_revenue', 15, 2)->default(0);
            $table->decimal('actual_revenue', 15, 2)->default(0);
            $table->decimal('lifetime_value', 15, 2)->default(0);
            $table->decimal('acquisition_cost', 15, 2)->default(0);
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('last_contact_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->text('lost_reason')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('phone');
            $table->index('whatsapp_number');
            $table->index('status');
            $table->index('source');
            $table->index('assigned_to');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
