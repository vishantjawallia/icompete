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
        Schema::create('admin_notifies', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->enum('type', ['admin', 'user', 'others'])->default('admin');
            $table->string('title')->nullable();
            $table->string('message')->nullable();
            $table->json('data')->nullable();
            $table->string('url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_notifies');
    }
};
