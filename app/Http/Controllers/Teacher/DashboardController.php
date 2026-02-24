<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $teacherId = auth()->id();

        $totalCourses = Course::where('teacher_id', $teacherId)->count();
        
        $totalStudents = DB::table('course_user')
            ->join('courses', 'course_user.course_id', '=', 'courses.id')
            ->where('courses.teacher_id', $teacherId)
            ->distinct('course_user.user_id')
            ->count('course_user.user_id');

        $courses = Course::where('teacher_id', $teacherId)
            ->withCount(['chapters', 'students'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'slug' => $course->slug,
                    'cover_image' => $course->cover_image ? asset($course->cover_image) : null,
                    'status' => $course->status,
                    'chapters_count' => $course->chapters_count,
                    'students_count' => $course->students_count ?? 0, 
                    'progress' => 0, 
                    'avg_grade' => '-',
                ];
            });

        return Inertia::render('Teacher/Dashboard', [
            'stats' => [
                'total_courses' => $totalCourses,
                'total_students' => $totalStudents,
            ],
            'recent_courses' => $courses
        ]);
    }
}