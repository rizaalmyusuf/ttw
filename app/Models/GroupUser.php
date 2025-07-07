<?php

namespace App\Models;

use Illuminate\Database\Eloquent;
use Illuminate\Notifications\Notifiable;

class GroupUser extends Eloquent\Model
{
    use Eloquent\Factories\HasFactory, Notifiable;

    protected $table = 'group_user';

    protected $fillable = [
        'group_id',
        'user_id',
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
                'user_id' => $student->id,
            ]);
        }
    }

    public static function assignStudents($students)
    {
        foreach ($students as $studentId) {
            self::create([
                'group_id' => Group::latest('created_at')->first()->id,
                'user_id' => $studentId,
            ]);
        }
    }

    public static function updateGroup($data, Group $group){
        if($data['code'] === $group->code || $data['name'] === $group->name){
            if($data['studentsInGroup'] === $group->students->pluck('id')->toArray()){
                return $group->update(['name' => $data['name']]);
            }else{
                $oldStudents = $group->students->pluck('id')->toArray();
                $newStudents = $data['studentsInGroup'];
                $studentsToRemove = array_diff($oldStudents, $newStudents);
                $studentsToAdd = array_diff($newStudents, $oldStudents);

                foreach($studentsToRemove as $studentId){
                    self::where(['group_id' => $group->id, 'user_id' => $studentId])->delete();
                }

                foreach($studentsToAdd as $studentId){
                    $groupsClassroom = Group::where('classroom_id', $group->classroom_id)->get()->pluck('id')->toArray();
                    $idGroupUser = self::where('user_id', $studentId)->whereIn('group_id', $groupsClassroom)->pluck('id');
                    if($idGroupUser->isNotEmpty()){
                        self::whereIn('id', $idGroupUser)->update(['group_id' => $group->id]);
                    }
                    if(self::where(['group_id' => $group->id, 'user_id' => $studentId])->doesntExist()){
                        self::create([
                            'group_id' => $group->id,
                            'user_id' => $studentId,
                        ]);
                    }
                }
                return true;
            }
        }elseif($group->where('code', $data['code'])->exists()){
            return false;
        }else{
            return $group->update([
                'code' => $data['code'],
                'name' => $data['name'],
            ]);
        }
    }

    public static function findGroupName($record): string|null
    {
        $studentId = $record->student_id;
        $groups = self::where('user_id', $studentId)->get();
        $groupNames = $groups->pluck('group.name')->unique();
        return $groupNames->first();
    }
}
