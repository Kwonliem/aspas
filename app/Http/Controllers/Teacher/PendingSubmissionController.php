<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PendingSubmissionController extends Controller
{
    public function index()
    {
        $submissions = DB::table('course_user')
            ->join('courses', 'course_user.course_id', '=', 'courses.id')
            ->join('users', 'course_user.user_id', '=', 'users.id')
            ->leftJoin('projects', 'courses.id', '=', 'projects.course_id') 
            ->where('courses.teacher_id', auth()->id())
            ->whereJsonContains('course_user.completed_data->project_status', 'pending')
            ->select(
                'course_user.course_id',
                'course_user.user_id',
                'course_user.completed_data',
                'course_user.updated_at as created_at',
                'users.name as student_name',
                'users.email as student_email',
                'users.avatar as student_avatar',
                'courses.title as course_title',
                'projects.title as project_title'
            )
            ->orderBy('course_user.updated_at', 'desc')
            ->paginate(10);

        $submissions->getCollection()->transform(function ($item) {
            $completedData = json_decode($item->completed_data, true);
            
            return [
                'id' => $item->course_id . '_' . $item->user_id,
                'student_id' => $item->user_id, 
                'student' => [
                    'name' => $item->student_name,
                    'email' => $item->student_email,
                    'avatar' => $item->student_avatar,
                ],
                'course' => [
                    'id' => $item->course_id,
                    'title' => $item->course_title,
                ],
                'title' => $item->project_title ?? 'Final Project',
                'link' => $completedData['project_link'] ?? '#',
                'created_at' => $item->created_at,
            ];
        });

        return Inertia::render('Teacher/PendingSubmissions', [
            'submissions' => $submissions
        ]);
    }

  
    public function review(Request $request, $course_id, $student_id)
    {
        $request->validate([
            'status' => 'required|in:passed,revision'
        ]);

       
        $enrollment = DB::table('course_user')
            ->where('course_id', $course_id)
            ->where('user_id', $student_id)
            ->first();

        if (!$enrollment) return redirect()->back();

        $completedData = json_decode($enrollment->completed_data, true) ?? [];
        $progress = $enrollment->progress;

        if ($request->status === 'passed') {
            $completedData['project_status'] = 'passed';
            
           
            $course = Course::with(['chapters.lessons', 'chapters.quizzes', 'project'])->find($course_id);
            $totalItems = 0;
            foreach($course->chapters as $c) {
                $totalItems += $c->lessons->count();
                $totalItems += $c->quizzes->count();
            }
            if ($course->project) $totalItems += 1;

            $completedItemsCount = count($completedData['lessons'] ?? []) + count($completedData['quizzes'] ?? []) + 1;
            $oldProgress = $progress;
            $progress = $totalItems > 0 ? round(($completedItemsCount / $totalItems) * 100) : 0;

            if ($oldProgress < 100 && $progress >= 100) {
                \App\Models\User::where('id', $student_id)->increment('xp', $course->xp);
            }

        } else {
            
            $completedData['project_status'] = null; 
            $completedData['project_link'] = null; 
           
        }

        
        DB::table('course_user')
            ->where('course_id', $course_id)
            ->where('user_id', $student_id)
            ->update([
                'completed_data' => json_encode($completedData),
                'progress' => $progress,
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('message', 'Submission reviewed successfully.');
    }
}