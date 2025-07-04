<?php

namespace App\Models;

use Illuminate\Database\Eloquent;
use Illuminate\Notifications\Notifiable;

class Answer extends Eloquent\Model
{
    use Notifiable;

    protected $fillable = [
        'content',
        'topic_id',
        'student_id',
    ];

    protected $hidden = [
        'topic_id',
        'student_id',
    ];

    public function topic(): Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Topic::class, 'topic_id');
    }

    public function student(): Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function replyFrom(): Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'reply_from');
    }
}
