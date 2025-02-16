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
            $table->string('file_name');              // S3上のファイル名
            $table->string('original_name');          // アップロード時の元のファイル名
            $table->string('mime_type');              // ファイルのMIMEタイプ
            $table->bigInteger('file_size');          // ファイルサイズ（バイト）
            $table->string('s3_path');                // S3上のパス
            $table->datetime('url_expires_at')->nullable(); // 署名付きURLの有効期限
            $table->timestamps();
            $table->softDeletes();  // 論理削除用
        });
    }

    public function down()
    {
        Schema::dropIfExists('video_files');
    }
};
