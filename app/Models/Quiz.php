<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = ['chapter_id', 'title', 'slug', 'questions', 'min_score', 'order'];
    
    protected $casts = [
        'questions' => 'array',
    ];

    public function chapter() { return $this->belongsTo(Chapter::class); }
}