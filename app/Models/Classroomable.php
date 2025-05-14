<?php

namespace App\Models;

use Illuminate\Database\Eloquent;
use Illuminate\Notifications\Notifiable;

class Classroomable extends Eloquent\Model
{
    use Eloquent\Factories\HasFactory, Notifiable;

    protected $table = 'classroomables';

    protected $fillable = [
        'classroom_id',
        'classroomable_id',
        'classroomable_type',
    ];

    public function classroom(): Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function teacher(): Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function student(): Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
