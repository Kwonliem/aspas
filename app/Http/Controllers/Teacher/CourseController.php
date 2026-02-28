<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::where('teacher_id', auth()->id())
            
            ->with(['chapters.lessons', 'chapters.quizzes', 'project'])
            ->latest()
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'credits' => $course->credits,
                    'xp' => $course->xp,
                    'duration_days' => $course->duration_days, 
                    'image' => $course->cover_image ? asset($course->cover_image) : null,
                    'status' => ucfirst($course->status),
                    'statusColor' => $course->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800',
                    
                    'lessons_count' => $course->chapters->flatMap->lessons->count(),
                    'quizzes_count' => $course->chapters->flatMap->quizzes->count(),
                    
                    
                    'project_count' => $course->project ? 1 : 0, 
                    
                    'students_count' => 0, 
                ];
            });

        return Inertia::render('Teacher/Courses', [
            'courses' => $courses
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'credits' => 'required|integer|min:0',
            'xp' => 'required|integer|min:0',
            'duration_days' => 'required|integer|min:0', 
            'status' => 'required|in:draft,published',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'teacher_id' => auth()->id(),
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . Str::random(5),
            'description' => $request->description,
            'credits' => $request->credits,
            'xp' => $request->xp,
            'duration_days' => $request->duration_days, 
            'status' => $request->status,
        ];

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('courses', 'public');
            $data['cover_image'] = '/storage/' . $path;
        }

        Course::create($data);

        return redirect()->back()->with('message', 'Course created successfully!');
    }

    public function destroy(Course $course)
    {
        if ($course->teacher_id !== auth()->id()) {
            abort(403);
        }

        if ($course->cover_image) {
            $imagePath = str_replace('/storage/', '', $course->cover_image);
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
        }

        $course->delete();

        return redirect()->back()->with('message', 'Course deleted successfully!');
    }

    public function manage(Course $course)
    {
        if ($course->teacher_id !== auth()->id()) abort(403);

        return Inertia::render('Teacher/ManageCourse', [
            'course' => $course,
            'chapters' => $course->chapters()
                ->with(['lessons' => function ($q) {
                    $q->orderBy('order');
                }])
                ->with(['quizzes' => function ($q) {
                    $q->orderBy('order');
                }])
                ->orderBy('order')
                ->get(),
           
            'project' => $course->project 
        ]);
    }

    public function update(Request $request, Course $course)
    {
        if ($course->teacher_id !== auth()->id()) abort(403);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'credits' => 'required|integer|min:0',
            'xp' => 'required|integer|min:0',
            'duration_days' => 'required|integer|min:0', 
            'status' => 'required|in:draft,published',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . Str::random(5),
            'description' => $request->description,
            'credits' => $request->credits,
            'xp' => $request->xp,
            'duration_days' => $request->duration_days, 
            'status' => $request->status,
        ];

        if ($request->hasFile('cover_image')) {
            if ($course->cover_image) {
                $oldPath = str_replace('/storage/', '', $course->cover_image);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $path = $request->file('cover_image')->store('courses', 'public');
            $data['cover_image'] = '/storage/' . $path;
        }

        $course->update($data);

        return redirect()->back()->with('message', 'Course updated successfully!');
    }

    public function updateCurriculum(Request $request, Course $course)
    {
        if ($course->teacher_id !== auth()->id()) abort(403);

        $request->validate([
            'chapters' => 'array',
            'chapters.*.title' => 'required|string',
            'project' => 'nullable', 
        ]);

        $submittedChapterIds = collect($request->chapters)->pluck('id')->filter()->toArray();
        $course->chapters()->whereNotIn('id', $submittedChapterIds)->delete();

        foreach ($request->chapters as $cIndex => $chData) {
            $chapter = $course->chapters()->updateOrCreate(
                ['id' => $chData['id'] ?? null],
                ['title' => $chData['title'], 'order' => $cIndex + 1]
            );

            $lessonsData = $chData['lessons'] ?? [];
            $submittedLessonIds = collect($lessonsData)->pluck('id')->filter()->toArray();
            $chapter->lessons()->whereNotIn('id', $submittedLessonIds)->delete();

            foreach ($lessonsData as $lIndex => $lesData) {
                $chapter->lessons()->updateOrCreate(
                    ['id' => $lesData['id'] ?? null],
                    [
                        'title' => $lesData['title'],
                        'slug' => Str::slug($lesData['title']) . '-' . Str::random(5),
                        'content' => $lesData['content'] ?? [],
                        'order' => $lIndex + 1,
                        'is_published' => $lesData['is_published'] ?? false,
                    ]
                );
            }

            $quizzesData = $chData['quizzes'] ?? [];
            $submittedQuizIds = collect($quizzesData)->pluck('id')->filter()->toArray();
            $chapter->quizzes()->whereNotIn('id', $submittedQuizIds)->delete();

            foreach ($quizzesData as $qIndex => $qData) {
                $chapter->quizzes()->updateOrCreate(
                    ['id' => $qData['id'] ?? null],
                    [
                        'title' => $qData['title'],
                        'slug' => Str::slug($qData['title']) . '-' . Str::random(5),
                        'questions' => $qData['questions'] ?? [],
                        'min_score' => $qData['min_score'] ?? 80,
                        'order' => $qIndex + 1,
                    ]
                );
            }
        }

        
        if ($request->filled('project')) {
            $course->project()->updateOrCreate(
                ['course_id' => $course->id], 
                [
                    'title' => $request->project['title'] ?? 'Final Project',
                    'description' => $request->project['description'] ?? '',
                ]
            );
        } else {
            if ($course->project) {
                $course->project()->delete();
            }
        }

        return redirect()->back()->with('message', 'Curriculum saved successfully!');
    }

    public function destroyQuiz(Course $course, Quiz $quiz)
    {
        if ($course->teacher_id !== auth()->id()) abort(403);
        $quiz->delete();
        return redirect()->back();
    }

    public function destroyLesson(Course $course, Lesson $lesson)
    {
        if ($course->teacher_id !== auth()->id()) abort(403);
        $lesson->delete();
        return redirect()->back()->with('message', 'Lesson deleted.');
    }

    public function uploadMedia(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('course-content', $filename, 'public');

            return response()->json([
                'url' => '/storage/' . $path
            ]);
        }

        return response()->json(['error' => 'Upload failed'], 400);
    }

    public function deleteMedia(Request $request)
    {
        $request->validate(['url' => 'required|string']);

        $relativePath = str_replace('/storage/', '', $request->url);

        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
            return response()->json(['message' => 'File deleted']);
        }

        return response()->json(['message' => 'File not found'], 404);
    }

    public function destroyChapter(Chapter $chapter)
    {
        $course = $chapter->course;

        if ($course->teacher_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $chapter->delete();

        return redirect()->back()->with('message', 'Chapter deleted successfully');
    }

    public function preview(Course $course)
    {
        if ($course->teacher_id !== auth()->id()) abort(403);

        
        $course->load(['chapters' => function ($q) {
            $q->orderBy('order');
        }, 'chapters.lessons' => function ($q) {
            $q->orderBy('order');
        }, 'chapters.quizzes', 'project']);

        return Inertia::render('Teacher/PreviewCourse', [
            'course' => $course,
            'curriculum' => $course->chapters
        ]);
    }
}