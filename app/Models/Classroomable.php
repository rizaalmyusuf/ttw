<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroomable extends Model
{
    protected $table = 'classroomables';

    protected $fillable = [
        'classroom_id',
        'classroomable_id',
        'classroomable_type',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class);
    }
}
