<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = ['chapter_id', 'title', 'slug', 'content', 'order', 'is_published'];

    protected $casts = [
        'content' => 'array',
        'is_published' => 'boolean',
    ];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    protected static function booted()
    {
        static::deleting(function ($lesson) {
            
            if (!empty($lesson->content)) {
                foreach ($lesson->content as $block) {
                   
                    if (isset($block['type']) && $block['type'] === 'image' && !empty($block['value'])) {

                        
                        $relativePath = str_replace('/storage/', '', $block['value']);

                        
                        if (Storage::disk('public')->exists($relativePath)) {
                            Storage::disk('public')->delete($relativePath);
                        }
                    }
                }
            }
        });
    }
}
