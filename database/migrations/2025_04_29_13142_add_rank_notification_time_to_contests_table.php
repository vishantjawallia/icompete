<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRankNotificationTimeToContestsTable extends Migration
{
    public function up()
    {
        Schema::table('contests', function (Blueprint $table) {
            $table->timestamp('rank_notification_time')->nullable()->after('end_date');
        });
    }

    public function down()
    {
        Schema::table('contests', function (Blueprint $table) {
            $table->dropColumn('rank_notification_time');
        });
    }
}
