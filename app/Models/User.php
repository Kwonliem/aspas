<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, Prunable;

    protected $fillable = [
        'name',
        'email',
        'nis',
        'class',
        'bio',
        'credits',
        'xp',
        'password',
        'role',
        'avatar',
        'subject',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function ($user) {
            if ($user->avatar) {
                $path = str_replace('/storage/', '', $user->avatar);
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            if ($user->role === 'teacher') {
                foreach ($user->taughtCourses()->get() as $course) {
                    if ($course->cover_image) {
                        $coursePath = str_replace('/storage/', '', $course->cover_image);
                        if (Storage::disk('public')->exists($coursePath)) {
                            Storage::disk('public')->delete($coursePath);
                        }
                    }
                }
            }
        });
    }

    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function deletionRequest()
    {
        return $this->hasOne(DeletionRequest::class);
    }

    public function taughtCourses()
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'course_user')
                    ->withPivot('progress')
                    ->withTimestamps();
    }

    public function portfolios()
    {
        return $this->hasMany(Portfolio::class);
    }

    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new class extends VerifyEmail {
            protected function verificationUrl($notifiable)
            {
                return URL::temporarySignedRoute(
                    'verification.verify',
                    Carbon::now()->addMinute(),
                    [
                        'id' => $notifiable->getKey(),
                        'hash' => sha1($notifiable->getEmailForVerification()),
                    ]
                );
            }
        });
    }

    public function challenges()
    {
        return $this->belongsToMany(Challenge::class, 'challenge_user')
            ->withPivot('submission_link', 'status', 'created_at')
            ->withTimestamps();
    }

    public function prunable(): Builder
    {
        return static::where('email_verified_at', null)
                     ->where('created_at', '<=', now()->subHour());
    }
}