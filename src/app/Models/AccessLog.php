<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
    protected $fillable = [
        'video_file_id',
        'video_share_id',
        'access_email',
        'ip_address',
        'user_agent',
        'action'
    ];

    public function videoFile()
    {
        return $this->belongsTo(VideoFile::class);
    }

    public function videoShare()
    {
        return $this->belongsTo(VideoShare::class);
    }
}
