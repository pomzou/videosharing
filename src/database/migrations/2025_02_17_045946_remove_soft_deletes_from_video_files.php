<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('video_files', function (Blueprint $table) {
            $table->dropSoftDeletes(); // deleted_at カラムを削除
        });
    }

    public function down()
    {
        Schema::table('video_files', function (Blueprint $table) {
            $table->softDeletes(); // ロールバック用
        });
    }
};
