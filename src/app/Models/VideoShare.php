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
        'is_active',
        'share_type',
        'shared_url' // Added shared_url to fillable
    ];

    public function getActiveSharedUrlAttribute() // Added accessor for active_shared_url
    {
        return $this->shared_url; // Assuming shared_url is the source for active_shared_url
    }

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'share_type' => 'string'
    ];

    public function isEmailShare(): bool
    {
        return $this->share_type === 'email';
    }

    public function isSimpleShare(): bool
    {
        return $this->share_type === 'simple';
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function videoFile()
    {
        return $this->belongsTo(VideoFile::class);
    }

    public function accessLogs()
    {
        return $this->hasMany(AccessLog::class);
    }
}
