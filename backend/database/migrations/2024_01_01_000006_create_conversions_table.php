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
        Schema::create('conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->decimal('value', 12, 2);
            $table->string('currency', 10)->default('BRL');
            $table->enum('payment_method', [
                'pix', 'boleto', 'cartao_credito', 'cartao_debito', 
                'transferencia', 'dinheiro', 'outro'
            ])->nullable();
            $table->enum('detected_by', ['manual', 'nlp', 'webhook'])
                  ->default('manual');
            $table->json('detection_data')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])
                  ->default('confirmed');
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('detected_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['lead_id']);
            $table->index(['conversation_id']);
            $table->index(['detected_at']);
            $table->index(['confirmed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversions');
    }
};
