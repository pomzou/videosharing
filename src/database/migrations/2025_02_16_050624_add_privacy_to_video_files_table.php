<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('video_files', function (Blueprint $table) {
            $table->string('privacy')->default('private')->after('s3_path');
        });
    }

    public function down()
    {
        Schema::table('video_files', function (Blueprint $table) {
            $table->dropColumn('privacy');
        });
    }
};
