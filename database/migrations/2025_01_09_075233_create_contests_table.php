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
        Schema::create('contests', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('organizer_id')->index();
            $table->ulid('category_id')->index();
            $table->string('category')->default('others');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->enum('type', ['free', 'paid', 'exclusive'])->default('free');
            $table->double('amount', 8, 2)->default(0); // price based on the contest type
            $table->enum('entry_type', ['free', 'paid', 'exclusive'])->default('free');
            $table->decimal('entry_fee', 10, 2)->default(0); // based on the entry type
            $table->enum('entry_status', ['closed', 'open'])->default('closed');
            $table->integer('prize')->default(20); // winner percentage
            $table->enum('status', ['draft', 'active', 'completed', 'canceled', 'pending'])->default('draft');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->dateTime('voting_start_date')->nullable(); // Voting period start
            $table->dateTime('voting_end_date')->nullable(); // Voting period end
            $table->integer('max_entries')->nullable();
            $table->integer('featured')->default(0);
            $table->bigInteger('entry_coins')->default(0);
            $table->bigInteger('voting_coins')->default(0);
            $table->text('rules')->nullable();
            $table->json('requirements')->nullable();
            $table->json('meta')->nullable();
            $table->json('custom')->nullable();
            $table->timestamps();
            $table->foreign('organizer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('contest_categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contests');
    }
};
