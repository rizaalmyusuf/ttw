<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $fillable = [
        'name',
        'token',
        'subject',
    ];

    protected $hidden = [
        'token',
        'teacher_id',
        'student_id',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'student_id');
    }

    public function topic()
    {
        return $this->hasMany(Topic::class);
    }
}
