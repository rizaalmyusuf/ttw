<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'file',
    ];

    protected $hidden = [
        'slug',
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
