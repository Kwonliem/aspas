<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseEnrollmentController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $user = $request->user();

        if ($user->enrolledCourses()->where('course_id', $course->id)->exists()) {
            return redirect()->route('classroom.my-courses');
        }

        if ($course->credits > 0 && $user->credits < $course->credits) {
            return back()->with('error', 'Not enough credits to enroll.');
        }

        DB::transaction(function () use ($user, $course) {
            if ($course->credits > 0) {
                $user->decrement('credits', $course->credits);
            }

            $user->enrolledCourses()->attach($course->id, ['progress' => 0]);
        });

        return redirect()->route('classroom.my-courses');
    }
}