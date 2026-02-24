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

        // 1. Hitung total course yang di-enroll (progress belum 100)
        $activeCoursesCount = $user->enrolledCourses()->wherePivot('progress', '<', 100)->count();
        
        // 2. Hitung total course yang sudah selesai (progress = 100)
        $completedCoursesCount = $user->enrolledCourses()->wherePivot('progress', 100)->count();

        // 3. Ambil daftar course yang di-enroll beserta data Gurunya
        $activeCourses = $user->enrolledCourses()
                              ->with('teacher') 
                              ->wherePivot('progress', '<', 100) 
                              ->latest()
                              ->take(3) 
                              ->get();

        // 4. AMBIL DATA SERTIFIKAT DARI COURSE (Progress = 100%)
        $courseCertificates = $user->enrolledCourses()
                              ->wherePivot('progress', 100)
                              ->latest('course_user.updated_at') 
                              ->get()
                              ->map(function ($course) {
                                  return [
                                      'id' => 'course_' . $course->id, // Beri awalan unik
                                      'title' => $course->title,
                                      'completed_at' => $course->pivot->updated_at->format('M d, Y'), 
                                      'download_url' => route('classroom.certificate', $course->id), 
                                      'date_for_sort' => $course->pivot->updated_at // Data tersembunyi untuk proses sorting
                                  ];
                              });

        // 5. AMBIL DATA SERTIFIKAT DARI CHALLENGE (Status = 'passed')
        $challengeCertificates = $user->challenges()
                              ->wherePivot('status', 'passed')
                              ->latest('challenge_user.updated_at')
                              ->get()
                              ->map(function ($challenge) {
                                  return [
                                      'id' => 'challenge_' . $challenge->id, // Beri awalan unik
                                      'title' => 'Challenge: ' . $challenge->title, // Tambahkan kata "Challenge" biar jelas
                                      'completed_at' => $challenge->pivot->updated_at->format('M d, Y'),
                                      'download_url' => route('student.challenges.certificate', $challenge->id),
                                      'date_for_sort' => $challenge->pivot->updated_at // Data tersembunyi untuk proses sorting
                                  ];
                              });

        // 6. GABUNGKAN KEDUA SERTIFIKAT & URUTKAN DARI YANG TERBARU
        $allCertificates = $courseCertificates->concat($challengeCertificates)
                                              ->sortByDesc('date_for_sort')
                                              ->values() // Reset index array
                                              ->map(function ($cert) {
                                                  // Buang kolom 'date_for_sort' karena tidak diperlukan oleh Vue
                                                  unset($cert['date_for_sort']);
                                                  return $cert;
                                              })->toArray(); // Pastikan jadi array murni

        // 7. Kirim data ke Vue
        return Inertia::render('Student/Dashboard', [
            'activeCoursesCount' => $activeCoursesCount,
            'completedCoursesCount' => $completedCoursesCount,
            'activeCourses' => $activeCourses,
            'certificates' => $allCertificates, // Kirim data gabungan ke Vue
        ]);
    }
}