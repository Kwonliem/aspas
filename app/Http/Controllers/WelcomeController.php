<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Challenge; // <-- TAMBAHKAN INI
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class WelcomeController extends Controller
{
    public function index()
    {
        // 1. Ambil Data Course (Kode Anda yang sudah ada)
        $courses = Course::where('status', 'published')
            ->with(['user', 'chapters.lessons', 'chapters.quizzes', 'project']) 
            ->latest()
            ->take(8)
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'cover_image' => $course->cover_image ? asset($course->cover_image) : null,
                    'credits' => $course->credits,
                    'xp' => $course->xp,
                    'status' => $course->status,
                    
                    'teacher_id' => $course->teacher_id, 
                    
                    'teacher' => [
                        'name' => $course->user->name,
                        'avatar' => $course->user->avatar, 
                    ],

                    'lessons_count' => $course->chapters->flatMap->lessons->count(),
                    'quizzes_count' => $course->chapters->flatMap->quizzes->count(),
                    'project_count' => $course->project ? 1 : 0,
                ];
            });

        // 2. AMBIL 1 CHALLENGE TERBARU YANG BELUM EXPIRED
        $latestChallenge = Challenge::where('end_date', '>', now())->latest()->first();

        // 3. Kirim semua data ke Vue
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'courses' => $courses,
            'latestChallenge' => $latestChallenge, // <-- KIRIM DATA CHALLENGE KE VUE
        ]);
    }
}