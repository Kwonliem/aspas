<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'title',
        'description',
        'xp_reward',
        'credit_reward',
        'end_date',
    ];

    protected $casts = [
        'end_date' => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'challenge_user')
            ->withPivot('submission_link', 'status', 'created_at')
            ->withTimestamps();
    }
}