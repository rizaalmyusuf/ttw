<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent;
use Illuminate\Notifications\Notifiable;

class Group extends Eloquent\Model
{
    use Notifiable;

    protected $fillable = [
        'code',
        'name',
        'classroom_id',
    ];

    public function classroom(): Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function students(): Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public static function getOnlyInside(Classroom $classroom){
        return self::where('classroom_id', $classroom->id)->groupBy('name')->get();
    }

    public static function makeGroup($data, Classroom $record){
        return self::create([
            'name' => $data['name'],
            'classroom_id' => $record->id,
            'code' => $data['code'] ?? $record->token.'_'.Str::slug($data['name']),
        ]);
    }
}
