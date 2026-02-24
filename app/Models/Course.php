<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'title',
        'slug',
        'description',
        'credits',
        'xp',
        'duration_days',
        'cover_image',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
    
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class)->orderBy('order');
    }

    
    public function project()
    {
        return $this->hasOne(Project::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'course_user')
                    ->withPivot('progress')
                    ->withTimestamps();
    }
}