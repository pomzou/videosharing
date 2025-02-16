<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
    Schema::create('video_files', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('title');
        $table->text('description')->nullable();
        $table->string('file_name');
        $table->string('original_name');
        $table->string('mime_type');
        $table->bigInteger('file_size');
        $table->string('s3_path');
        $table->string('privacy')->default('private'); // privacyカラムを追加
        $table->datetime('url_expires_at')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
    }

    public function down()
    {
        Schema::dropIfExists('video_files');
    }
};
