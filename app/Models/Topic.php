<?php

namespace App\Models;

use Illuminate\Database\Eloquent;
use Illuminate\Notifications\Notifiable;

class Topic extends Eloquent\Model
{
    use Notifiable;

    protected $fillable = [
        'title',
        'description',
        'file',
        'classroom_id',
    ];

    protected $hidden = [
        'classroom_id',
    ];

    public function classroom(): Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function answers(): Eloquent\Relations\HasMany
    {
        return $this->hasMany(Answer::class);
    }
}
