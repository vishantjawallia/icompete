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
        Schema::create('coin_payments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('user_id')->constrained();
            $table->string('gateway'); // e.g., 'paypal', 'flutterwave'
            $table->unsignedInteger('coins'); // e.g., 100, 500, etc.
            $table->decimal('amount', 10, 2); // Price in USD or other currency
            $table->string('reference')->unique(); // Unique transaction reference
            $table->string('status')->default('pending'); // 'pending', 'completed', 'failed'
            $table->json('data')->nullable();
            $table->json('response')->nullable();
            $table->timestamp('expires_at');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coin_payments');
    }
};
