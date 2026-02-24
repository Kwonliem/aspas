<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $fillable = ['course_id', 'title', 'order'];

    protected static function booted()
    {
        static::deleting(function ($chapter) {

            $chapter->lessons->each->delete();
        });
    }

    public function projects()
    {
        
        return $this->hasMany(Project::class)->orderBy('order');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class)->orderBy('order');
    }
}
