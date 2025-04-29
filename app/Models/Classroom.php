<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Classroom extends Model
{
    use HasFactory, Notifiable;
    
    protected $fillable = [
        'name',
        'token',
        'subject',
        'teacher_id',
        // 'student_id',
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
