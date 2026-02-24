<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nis',
        'class_name',
        'credits',
        'xp',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}