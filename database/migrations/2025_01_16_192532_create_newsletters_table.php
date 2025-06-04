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
        Schema::create('newsletters', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->tinyInteger('organizers')->default(0);
            $table->tinyInteger('contestants')->default(0);
            $table->tinyInteger('voters')->default(0);
            $table->tinyInteger('admin')->default(0);
            $table->text('other_emails')->nullable();
            $table->string('subject')->nullable();
            $table->longText('message')->nullable();
            $table->tinyInteger('status')->default(2);
            $table->timestamp('date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletters');
    }
};
