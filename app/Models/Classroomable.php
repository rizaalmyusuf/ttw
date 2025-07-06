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

    public static function inviteStudent($users, Classroom $classroom): bool
    {
        foreach($users as $user){
            self::create([
                'classroom_id' => $classroom->id,
                'classroomable_id' => $user->id,
                'classroomable_type' => User::class,
            ]);
        }

        return true;
    }

    public static function kickStudent(User $user, Classroom $classroom): bool
    {
        GroupUser::where('user_id', $user->id)->whereIn('group_id', $classroom->groups->pluck('id'))->delete();
        return self::where('classroomable_id', $user->id)->delete();
    }
}
