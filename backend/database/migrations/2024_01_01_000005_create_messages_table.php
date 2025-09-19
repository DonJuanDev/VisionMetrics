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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('whatsapp_message_id')->nullable();
            $table->enum('sender', ['client', 'agent', 'system']);
            $table->enum('type', ['text', 'image', 'document', 'audio', 'video', 'location', 'contact'])
                  ->default('text');
            $table->text('body')->nullable();
            $table->json('attachments')->nullable();
            $table->enum('status', ['sent', 'delivered', 'read', 'failed'])
                  ->default('sent');
            $table->boolean('is_parsed')->default(false);
            $table->decimal('parsed_value', 12, 2)->nullable();
            $table->string('parsed_currency', 10)->nullable();
            $table->json('nlp_data')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
            $table->index(['company_id']);
            $table->index(['sender']);
            $table->index(['is_parsed']);
            $table->index(['whatsapp_message_id']);
            $table->fullText(['body']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
