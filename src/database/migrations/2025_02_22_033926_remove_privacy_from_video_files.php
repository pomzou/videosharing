<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePrivacyFromVideoFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('video_files', function (Blueprint $table) {
            $table->dropColumn('privacy');
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
            $table->string('privacy'); // 元々のカラム型を指定（ここは元の型に合わせて変更）
        });
    }
}
