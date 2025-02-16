<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('video_shares')) {
            Schema::create('video_shares', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('video_file_id');
                $table->string('email')->nullable();
                $table->string('access_token');
                $table->timestamp('expires_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }


    public function down()
    {
        Schema::dropIfExists('video_shares');
    }
};
