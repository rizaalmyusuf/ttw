<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Panel;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Eloquent\Factories\HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'role',
    ];

    // protected $guarded;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'status',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if($panel->getId() === 'app' && $this->role === 0) {
            return false;
        }elseif($panel->getId() === 'app' && $this->role != 0) {
            return true;
        }

        if($panel->getId() === 'admin' && $this->role != 0) {
            return false;
        }elseif($panel->getId() === 'admin' && $this->role === 0) {
            return true;
        }

        return false;
    }

    public function classrooms(): Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(Classroom::class, 'classroomable');
    }

    public function groups(): Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(Group::class, 'groupable');
    }

    public static function notInClassroomId($classroomId){
        return self::where('role', 2)
            ->whereNot(function ($query) use ($classroomId) {
                $query->whereIn('id', Classroomable::where('classroom_id', $classroomId)->pluck('classroomable_id'));
        });
    }

    public static function findUngroupedStudents(Classroom $classroom)
    {
        return self::where('role', 2)
            ->whereIn('id', Classroomable::where('classroom_id', $classroom->id)->pluck('classroomable_id'))
            ->whereNotIn('id', Groupable::whereIn('group_id', $classroom->groups()->pluck('id'))->pluck('groupable_id'));
    }
}
