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
        Schema::create('post_comments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('user_id')->index();
            $table->ulid('post_id')->index();
            $table->ulid('parent_comment_id')->nullable()->index();
            $table->string('title')->nullable();
            $table->text('content');
            $table->enum('status', ['active', 'disabled', 'pending'])->default('active');
            $table->timestamps();
            $table->softDeletes();
            
            // Add unique constraint to id column
            $table->unique('id');
            
            // Add foreign keys
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_comment_id')->references('id')->on('post_comments')->onDelete('cascade'); // For nested comments
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_comments');
    }
};
