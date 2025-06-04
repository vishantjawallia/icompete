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
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('user_id');
            $table->string('code')->nullable();
            $table->double('coins', 10, 2)->default(0);
            $table->double('amount', 12, 2)->default(0);
            $table->decimal('fee', 10, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'processing', 'completed', 'canceled'])->default('pending');
            $table->string('method')->default('paypal');
            $table->json('meta')->nullable();
            $table->text('payment_details')->nullable();
            $table->double('oldbal', 10, 2)->nullable();
            $table->double('newbal', 10, 2)->nullable();
            $table->text('admin_notes')->nullable();
            $table->json('response')->nullable();
            $table->timestamp('approval_date')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
