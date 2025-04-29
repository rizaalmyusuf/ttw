<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = [
        'content',
        'topic_id',
        'student_id',
    ];

    protected $hidden = [
        'topic_id',
        'student_id',
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
