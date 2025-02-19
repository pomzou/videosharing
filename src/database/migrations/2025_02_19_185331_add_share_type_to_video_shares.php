<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('video_shares', function (Blueprint $table) {
            $table->enum('share_type', ['email', 'simple'])->default('simple')->after('is_active');
        });

        // Update existing shares to 'email' type since they were all email shares before
        DB::table('video_shares')->update(['share_type' => 'email']);
    }

    public function down()
    {
        Schema::table('video_shares', function (Blueprint $table) {
            $table->dropColumn('share_type');
        });
    }
};
