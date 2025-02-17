<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrivacyToVideoFilesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('video_files', function (Blueprint $table) {
            // privacyカラムの追加などの処理
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('video_files', function (Blueprint $table) {
            if (Schema::hasColumn('video_files', 'privacy')) {
                $table->dropColumn('privacy');
            }
        });
    }
}
