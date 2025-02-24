<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('access_logs', function (Blueprint $table) {
            // 既存のカラムが存在しない場合のみ追加
            if (!Schema::hasColumn('access_logs', 'access_email')) {
                $table->string('access_email')->nullable()->after('video_share_id');
            }
            if (!Schema::hasColumn('access_logs', 'ip_address')) {
                $table->string('ip_address')->nullable()->after('access_email');
            }
            if (!Schema::hasColumn('access_logs', 'user_agent')) {
                $table->string('user_agent')->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn('access_logs', 'action')) {
                $table->string('action')->nullable()->after('user_agent');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('access_logs', function (Blueprint $table) {
            $table->dropColumn([
                'access_email',
                'ip_address',
                'user_agent',
                'action'
            ]);
        });
    }
};
