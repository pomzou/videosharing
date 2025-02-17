<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoFile extends Model
{

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'file_name',
        'original_name',
        'mime_type',
        'file_size',
        's3_path',
        'privacy',
        'current_signed_url',
        'url_expires_at'
    ];

    // 日付として扱うカラムを指定
    protected $casts = [
        'url_expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shares()
    {
        return $this->hasMany(VideoShare::class);
    }

    public function accessLogs()
    {
        return $this->hasMany(AccessLog::class);
    }
}
