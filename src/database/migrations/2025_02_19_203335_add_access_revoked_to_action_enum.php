<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First, modify the column to be a string temporarily to avoid enum constraints
        Schema::table('access_logs', function (Blueprint $table) {
            $table->string('action')->change();
        });

        // Then alter it back to enum with the new value
        DB::statement("ALTER TABLE access_logs MODIFY action ENUM('view', 'download', 'share_created', 'access_revoked') DEFAULT 'view'");
    }

    public function down()
    {
        // First, modify the column to be a string temporarily to avoid enum constraints
        Schema::table('access_logs', function (Blueprint $table) {
            $table->string('action')->change();
        });

        // Then alter it back to the previous enum
        DB::statement("ALTER TABLE access_logs MODIFY action ENUM('view', 'download', 'share_created') DEFAULT 'view'");
    }
};
