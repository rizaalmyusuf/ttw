<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

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

    public function teachers(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'classroomable');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function students(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'classroomable');
    }
    
    public function topics()
    {
        return $this->hasMany(Topic::class);
    }
}
