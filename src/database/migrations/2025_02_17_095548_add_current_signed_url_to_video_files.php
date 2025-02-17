<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('video_files', function (Blueprint $table) {
            $table->text('current_signed_url')->nullable()->after('url_expires_at');
        });
    }

    public function down()
    {
        Schema::table('video_files', function (Blueprint $table) {
            $table->dropColumn('current_signed_url');
        });
    }
};
