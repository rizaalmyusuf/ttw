<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent;

class Classroom extends Eloquent\Model
{
    use Eloquent\Factories\HasFactory, Notifiable;
    
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

    public function teacher(): Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function teachers(): Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(User::class, 'classroomable');
    }

    public function students(): Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(User::class, 'classroomable')->groupBy('classroomable_id');
    }
    
    public function topics(): Eloquent\Relations\HasMany
    {
        return $this->hasMany(Topic::class);
    }
}
