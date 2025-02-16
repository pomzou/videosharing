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
            $table->foreignId('video_file_id')->constrained()->onDelete('cascade');
            $table->string('email')->nullable();
            $table->string('access_token')->unique();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['email', 'access_token']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('video_shares');
    }
};
