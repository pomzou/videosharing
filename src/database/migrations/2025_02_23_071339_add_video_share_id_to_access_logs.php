<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVideoShareIdToAccessLogs extends Migration
{
    public function up()
    {
        Schema::table('access_logs', function (Blueprint $table) {
            $table->foreignId('video_share_id')
                ->nullable()
                ->constrained('video_shares')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('access_logs', function (Blueprint $table) {
            $table->dropForeign(['video_share_id']);
            $table->dropColumn('video_share_id');
        });
    }
}
