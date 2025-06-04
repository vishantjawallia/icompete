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
        Schema::create('submissions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('user_id')->index();
            $table->ulid('contest_id')->index();
            $table->enum('type', ['entry', 'submission'])->default('submission'); // Type to know if it's submission or contestants
            $table->string('title');
            $table->text('description')->nullable();
            $table->bigInteger('vote_count')->default(0);
            $table->json('media')->nullable();
            $table->enum('status', ['submitted', 'approved', 'rejected', 'pending', 'enabled', 'disabled'])->default('submitted'); // Submission status
            $table->enum('vote_status', ['enabled', 'disabled'])->default('enabled'); // Voting phase status
            $table->json('response')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('contest_id')->references('id')->on('contests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
