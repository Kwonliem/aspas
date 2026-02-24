<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicProfileController extends Controller
{
    public function show($id)
    {
        $student = User::where('role', 'student')->findOrFail($id);
        
        $portfolios = $student->portfolios()->latest()->get();
        
        $courseCertificates = $student->enrolledCourses()
            ->wherePivot('progress', 100)
            ->with('teacher')
            ->latest('course_user.updated_at')
            ->get()
            ->map(function ($course) {
                return [
                    'id' => 'course_' . $course->id,
                    'title' => $course->title,
                    'subtitle' => 'By ' . ($course->teacher->name ?? 'Unknown'),
                    'completed_at' => $course->pivot->updated_at->format('M d, Y'),
                    'download_url' => route('classroom.certificate', $course->id),
                    'date_for_sort' => $course->pivot->updated_at
                ];
            });

        $challengeCertificates = $student->challenges()
            ->wherePivot('status', 'passed')
            ->latest('challenge_user.updated_at')
            ->get()
            ->map(function ($challenge) {
                return [
                    'id' => 'challenge_' . $challenge->id,
                    'title' => $challenge->title,
                    'subtitle' => 'Weekly Challenge',
                    'completed_at' => $challenge->pivot->updated_at->format('M d, Y'),
                    'download_url' => route('student.challenges.certificate', $challenge->id),
                    'date_for_sort' => $challenge->pivot->updated_at
                ];
            });

        $certificates = $courseCertificates->concat($challengeCertificates)
            ->sortByDesc('date_for_sort')
            ->values()
            ->map(function ($cert) {
                unset($cert['date_for_sort']);
                return $cert;
            })->toArray();

        $enrolledCourses = $student->enrolledCourses()
            ->with('teacher')
            ->latest()
            ->get();
            
        $rank = User::where('role', 'student')
            ->where('xp', '>', $student->xp)
            ->count() + 1;

        return Inertia::render('PublicProfile', [
            'student' => $student,
            'portfolios' => $portfolios,
            'certificates' => $certificates,
            'enrolledCourses' => $enrolledCourses, 
            'rank' => $rank
        ]);
    }
}