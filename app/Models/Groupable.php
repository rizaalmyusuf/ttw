<?php

namespace App\Models;

use Illuminate\Database\Eloquent;
use Illuminate\Notifications\Notifiable;

class Groupable extends Eloquent\Model
{
    use Eloquent\Factories\HasFactory, Notifiable;

    protected $table = 'groupables';

    protected $fillable = [
        'group_id',
        'groupable_id',
        'groupable_type',
    ];

    public function group(): Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function student(): Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function assignGroup($students, Group $group, Classroom $classroom)
    {
        foreach ($students as $student) {
            self::create([
                'group_id' => $group->id,
                'groupable_id' => $student->id,
                'groupable_type' => User::class,
            ]);
        }
    }

    public static function assignStudents($students)
    {
        foreach ($students as $studentId) {
            self::create([
                'group_id' => Group::latest('created_at')->first()->id,
                'groupable_id' => $studentId,
                'groupable_type' => User::class,
            ]);
        }
    }
}
