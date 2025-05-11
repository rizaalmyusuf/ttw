<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $fillable = [
        'title',
        'description',
        'file',
        'classroom_id',
    ];

    protected $hidden = [
        'classroom_id',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
