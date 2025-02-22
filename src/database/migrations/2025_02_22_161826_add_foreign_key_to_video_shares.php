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
        Schema::table('video_shares', function (Blueprint $table) {
            $table->foreign('video_file_id')->references('id')->on('video_files')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('video_shares', function (Blueprint $table) {
            // 外部キー制約を削除
            $table->dropForeign(['video_file_id']);
        });
    }
};
