<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        
        $activeCoursesCount = $user->enrolledCourses()->wherePivot('progress', '<', 100)->count();
        
        
        $completedCoursesCount = $user->enrolledCourses()->wherePivot('progress', 100)->count();

        
        $activeCourses = $user->enrolledCourses()
                              ->with('teacher') 
                              ->wherePivot('progress', '<', 100) 
                              ->latest()
                              ->take(3) 
                              ->get();

        
        $courseCertificates = $user->enrolledCourses()
                              ->wherePivot('progress', 100)
                              ->latest('course_user.updated_at') 
                              ->get()
                              ->map(function ($course) {
                                  return [
                                      'id' => 'course_' . $course->id, 
                                      'title' => $course->title,
                                      'completed_at' => $course->pivot->updated_at->format('M d, Y'), 
                                      'download_url' => route('classroom.certificate', $course->id), 
                                      'date_for_sort' => $course->pivot->updated_at 
                                  ];
                              });

       
        $challengeCertificates = $user->challenges()
                              ->wherePivot('status', 'passed')
                              ->latest('challenge_user.updated_at')
                              ->get()
                              ->map(function ($challenge) {
                                  return [
                                      'id' => 'challenge_' . $challenge->id, 
                                      'title' => 'Challenge: ' . $challenge->title, 
                                      'completed_at' => $challenge->pivot->updated_at->format('M d, Y'),
                                      'download_url' => route('student.challenges.certificate', $challenge->id),
                                      'date_for_sort' => $challenge->pivot->updated_at 
                                  ];
                              });

        
        $allCertificates = $courseCertificates->concat($challengeCertificates)
                                              ->sortByDesc('date_for_sort')
                                              ->values() 
                                              ->map(function ($cert) {
                                                  
                                                  unset($cert['date_for_sort']);
                                                  return $cert;
                                              })->toArray(); 

        
        return Inertia::render('Student/Dashboard', [
            'activeCoursesCount' => $activeCoursesCount,
            'completedCoursesCount' => $completedCoursesCount,
            'activeCourses' => $activeCourses,
            'certificates' => $allCertificates, 
        ]);
    }
}