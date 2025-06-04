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
        Schema::create('votes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('contest_id')->nullable();
            $table->ulid('submission_id')->constrained('submissions')->onDelete('cascade'); // Reference to the submission being voted on
            $table->ulid('voter_id')->nullable();
            $table->string('voter_type')->default('user'); // 'user' or 'guest'
            $table->string('guest_token')->nullable(); // For guest votes
            $table->integer('quantity')->default(1);
            $table->unsignedInteger('amount')->default(0);
            $table->ipAddress('ip_address')->nullable(); // IP address of the voter
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->foreign('contest_id')->references('id')->on('contests');
            $table->foreign('submission_id')->references('id')->on('submissions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
