<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'filename',
        'original_filename',
        'mime_type',
        'size',
        's3_path'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
