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
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('phone', 50)->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('first_contact_at')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->enum('origin', ['meta', 'google', 'outras', 'nao_rastreada'])
                  ->default('nao_rastreada');
            $table->string('utm_source')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('tracking_token', 64)->nullable();
            $table->string('referrer_url')->nullable();
            $table->json('attribution_data')->nullable();
            $table->enum('status', ['new', 'contacted', 'qualified', 'converted', 'lost'])
                  ->default('new');
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'origin']);
            $table->index(['company_id', 'status']);
            $table->index(['phone']);
            $table->index(['tracking_token']);
            $table->index(['first_contact_at']);
            $table->index(['last_message_at']);
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
