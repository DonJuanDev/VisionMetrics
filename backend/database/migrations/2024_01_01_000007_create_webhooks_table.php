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
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('url', 1000);
            $table->json('events'); // Array of events to subscribe to
            $table->string('secret')->nullable(); // For webhook signature verification
            $table->boolean('active')->default(true);
            $table->integer('retry_attempts')->default(3);
            $table->integer('timeout')->default(30);
            $table->timestamp('last_triggered_at')->nullable();
            $table->enum('last_status', ['success', 'failed', 'timeout'])->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};
