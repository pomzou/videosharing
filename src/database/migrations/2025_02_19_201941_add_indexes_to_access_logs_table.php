<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('access_logs', function (Blueprint $table) {
            // 単一カラムのインデックス
            $table->index('created_at');
            $table->index('access_email');
            $table->index('action');

            // 複合インデックス
            $table->index(['video_file_id', 'created_at']);
            $table->index(['video_share_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::table('access_logs', function (Blueprint $table) {
            // 単一カラムのインデックス
            $table->dropIndex(['access_logs_created_at_index']);
            $table->dropIndex(['access_logs_access_email_index']);
            $table->dropIndex(['access_logs_action_index']);

            // 複合インデックス
            $table->dropIndex(['access_logs_video_file_id_created_at_index']);
            $table->dropIndex(['access_logs_video_share_id_created_at_index']);
        });
    }
};
