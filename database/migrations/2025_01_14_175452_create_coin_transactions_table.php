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
        Schema::create('coin_transactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('user_id')->constrained();
            $table->double('amount', 10, 2)->default(0);
            $table->string('code')->nullable();
            $table->double('coins', 10, 2)->default(0);
            $table->string('type')->default('credit');
            $table->string('service'); // purchase, spend, vote, reward, referral
            $table->string('description')->nullable();
            $table->double('oldbal', 10, 2)->nullable();
            $table->double('newbal', 10, 2)->nullable();
            $table->json('metadata')->nullable();
            $table->json('response')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coin_transactions');
    }
};
