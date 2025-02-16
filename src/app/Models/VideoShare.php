<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoShare extends Model
{
    protected $fillable = [
        'video_file_id',
        'email',
        'access_token',
        'expires_at',
        'is_active'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function videoFile()
    {
        return $this->belongsTo(VideoFile::class);
    }

    public function accessLogs()
    {
        return $this->hasMany(AccessLog::class);
    }
}
