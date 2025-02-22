<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSharedUrlColumnInVideoShares extends Migration
{
    public function up()
    {
        Schema::table('video_shares', function (Blueprint $table) {
            // VARCHAR(255)からTEXT型に変更
            $table->text('shared_url')->change();
        });
    }

    public function down()
    {
        Schema::table('video_shares', function (Blueprint $table) {
            $table->string('shared_url', 255)->change();
        });
    }
}
