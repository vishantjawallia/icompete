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
        Schema::table('contests', function (Blueprint $table) {

            $table->double('winner_amount', 10, 2)->default(0)->after('prize');
            $table->double('organizer_amount', 10, 2)->default(0)->after('winner_amount');
            $table->double('admin_amount', 10, 2)->default(0)->after('organizer_amount');
        });
        // for submissions table
        Schema::table('submissions', function (Blueprint $table) {

            $table->tinyInteger('is_winner')->default(0)->after('vote_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contests', function (Blueprint $table) {
            $table->dropColumn('winner_amount');
            $table->dropColumn('organizer_amount');
            $table->dropColumn('admin_amount');
        });
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn('is_winner');
        });
    }
};
