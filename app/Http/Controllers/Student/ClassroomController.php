<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class ClassroomController extends Controller
{
    public function show(Request $request, Course $course)
    {
        $user = $request->user();
        
        $enrollment = $user->enrolledCourses()
            ->withPivot('progress', 'completed_data', 'created_at') // DITAMBAHKAN: created_at untuk hitung expired
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) abort(403);

        $progress = $enrollment->pivot->progress;
        $expiredAt = null;

        // LOGIKA TIMER: Cek jika course memiliki batas waktu
        if ($course->duration_days > 0) {
            $expirationDate = $enrollment->pivot->created_at->addDays($course->duration_days);
            $expiredAt = $expirationDate->format('M d, Y H:i');
            
            // Jika sudah lewat waktu DAN belum 100%, tendang muridnya
            if (now()->greaterThan($expirationDate) && $progress < 100) {
                $user->enrolledCourses()->detach($course->id);
                // Redirect ke My Learning dengan pesan gagal (Bisa disesuaikan route-nya)
                return redirect()->route('classroom.my-courses')->with('error', 'Waktu course telah habis. Silakan enroll ulang.');
            }
        }

        $course->load(['chapters.lessons', 'chapters.quizzes', 'project', 'teacher']);

        $defaultData = ['lessons' => [], 'quizzes' => [], 'project_status' => null, 'project_link' => null];
        
        $pivotData = $enrollment->pivot->completed_data;
        $completedData = $pivotData ? json_decode($pivotData, true) : $defaultData;

        return Inertia::render('Student/Classroom', [
            'course' => $course,
            'progress' => $progress,
            'completedData' => $completedData,
            'expiredAt' => $progress < 100 ? $expiredAt : null // Kirim info expired ke Vue jika belum lulus
        ]);
    }

    public function complete(Request $request, Course $course)
    {
        $user = $request->user();
        
        $enrollment = $user->enrolledCourses()
            ->withPivot('progress', 'completed_data', 'created_at') // DITAMBAHKAN: created_at
            ->where('course_id', $course->id)
            ->first();

        // LOGIKA TIMER: Cek ulang sebelum memproses progress baru
        if ($course->duration_days > 0) {
            $expirationDate = $enrollment->pivot->created_at->addDays($course->duration_days);
            if (now()->greaterThan($expirationDate) && $enrollment->pivot->progress < 100) {
                $user->enrolledCourses()->detach($course->id);
                return Inertia::location(route('classroom.my-courses'));
            }
        }
        
        $defaultData = ['lessons' => [], 'quizzes' => [], 'project_status' => null, 'project_link' => null];
        
        $pivotData = $enrollment->pivot->completed_data;
        $completedData = $pivotData ? json_decode($pivotData, true) : $defaultData;

        if ($request->type === 'lesson') {
            if (!in_array($request->item_id, $completedData['lessons'])) {
                $completedData['lessons'][] = $request->item_id;
            }
        } elseif ($request->type === 'quiz') {
            if (!in_array($request->item_id, $completedData['quizzes'])) {
                $completedData['quizzes'][] = $request->item_id;
            }
        } elseif ($request->type === 'project') {
            $completedData['project_link'] = $request->link;
            $completedData['project_status'] = 'pending'; 
        }

        $course->load(['chapters.lessons', 'chapters.quizzes', 'project']);
        $totalItems = 0;
        
        foreach($course->chapters as $c) {
            $totalItems += $c->lessons->count();
            $totalItems += $c->quizzes->count();
        }
        if ($course->project) $totalItems += 1;

        $completedItemsCount = count($completedData['lessons']) + count($completedData['quizzes']);
        
        if (isset($completedData['project_status']) && $completedData['project_status'] === 'passed') {
            $completedItemsCount += 1;
        }

        $oldProgress = $enrollment->pivot->progress;

        $progress = $totalItems > 0 ? round(($completedItemsCount / $totalItems) * 100) : 0;

        if ($oldProgress < 100 && $progress >= 100) {
            $user->increment('xp', $course->xp);
        }

        $user->enrolledCourses()->updateExistingPivot($course->id, [
            'completed_data' => json_encode($completedData),
            'progress' => $progress
        ]);

        return redirect()->back();
    }

    public function certificate(Request $request, Course $course)
    {
        $user = $request->user();
        
        $enrollment = $user->enrolledCourses()
            ->withPivot('progress', 'completed_data')
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) abort(403);

        $completedData = json_decode($enrollment->pivot->completed_data, true);

        if (!isset($completedData['project_status']) || $completedData['project_status'] !== 'passed') {
            abort(403);
        }

        $pdf = Pdf::loadView('certificates.template', [
            'student' => $user->name,
            'course' => $course->title,
            'date' => now()->format('d F Y')
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Certificate-' . Str::slug($course->title) . '.pdf');
    }
}