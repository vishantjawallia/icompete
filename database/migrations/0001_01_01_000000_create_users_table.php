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
        Schema::create('users', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->enum('role', ['organizer', 'contestant', 'voter', 'guest'])->default('guest'); // User role
            $table->string('phone')->nullable();
            $table->string('gender')->nullable();
            $table->string('image')->nullable();
            $table->text('bio')->nullable(); // Optional bio
            $table->string('social_links')->nullable(); // Social media links (JSON or comma-separated)
            $table->string('phone_number')->nullable(); // Optional phone number
            $table->datetime('code_sent')->nullable();
            $table->string('verify_code', 20)->nullable();
            $table->boolean('email_verify')->default(0);
            $table->enum('status', ['active', 'inactive', 'banned'])->default('active'); // User status
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->ulid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
