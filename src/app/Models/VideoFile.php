<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


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

    public function isOwner($userId = null)
    {
        return $this->user_id === ($userId ?? Auth::id());
    }

    public function getFileType()
    {
        $mimeType = strtolower($this->mime_type);

        if (strpos($mimeType, 'video/') === 0) {
            return 'video';
        } elseif (strpos($mimeType, 'image/') === 0) {
            return 'image';
        } elseif (strpos($mimeType, 'audio/') === 0) {
            return 'audio';
        } elseif (strpos($mimeType, 'text/') === 0) {
            return 'text';
        } elseif (strpos($mimeType, 'application/pdf') === 0) {
            return 'pdf';
        } elseif (
            strpos($mimeType, 'application/msword') === 0 ||
            strpos($mimeType, 'application/vnd.openxmlformats-officedocument.wordprocessingml') === 0
        ) {
            return 'document';
        } elseif (
            strpos($mimeType, 'application/vnd.ms-excel') === 0 ||
            strpos($mimeType, 'application/vnd.openxmlformats-officedocument.spreadsheetml') === 0
        ) {
            return 'spreadsheet';
        } elseif (
            strpos($mimeType, 'application/zip') === 0 ||
            strpos($mimeType, 'application/x-rar') === 0 ||
            strpos($mimeType, 'application/x-7z-compressed') === 0
        ) {
            return 'archive';
        }

        return 'other';
    }
}
