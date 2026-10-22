<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'lesson_order',
        'content_type',
        'file_path',
        'original_filename',
        'duration_seconds',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}

