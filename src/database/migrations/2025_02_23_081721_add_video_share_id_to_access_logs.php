<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('access_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('video_share_id')->nullable()->after('id');
        });
    }

    public function down()
    {
        Schema::table('access_logs', function (Blueprint $table) {
            $table->dropColumn('video_share_id');
        });
    }
};
