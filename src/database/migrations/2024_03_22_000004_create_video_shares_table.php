<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('video_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_file_id')->constrained('video_files')->onDelete('cascade');
            $table->string('email')->nullable();
            $table->string('access_token')->unique();
            $table->string('share_type');
            $table->string('shared_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('video_shares');
    }
};
