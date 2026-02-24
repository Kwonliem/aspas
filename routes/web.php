<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\DeletionRequestController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\Student\CourseEnrollmentController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\LeaderboardController;
use App\Http\Controllers\Student\PortfolioController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\CourseController;
use App\Http\Controllers\Teacher\PendingSubmissionController;
use App\Http\Controllers\Teacher\SettingController as TeacherSettingController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
Route::get('/p/{id}', [PublicProfileController::class, 'show'])->name('public.profile');

Route::middleware('auth')->group(function () {

    Route::get('/verify-email', function () {
        if (request()->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }
        return Inertia::render('Auth/VerifyEmail', ['status' => session('status')]);
    })->name('verification.notice');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');

    Route::get('/verify-email/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('dashboard');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');


    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/students', [StudentController::class, 'index'])->name('students');
        Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
        Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');

        Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers');
        Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
        Route::put('/teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
        Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy'])->name('teachers.destroy');

        Route::get('/deletions', [DeletionRequestController::class, 'index'])->name('deletions');
        Route::delete('/deletions/{deletionRequest}/approve', [DeletionRequestController::class, 'approve'])->name('deletions.approve');
        Route::delete('/deletions/{deletionRequest}/reject', [DeletionRequestController::class, 'reject'])->name('deletions.reject');

        Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings');
        Route::patch('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
    });

    Route::middleware(['role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {

        Route::get('/pending-submissions', [PendingSubmissionController::class, 'index'])->name('submissions.index');

        Route::post('/pending-submissions/{course}/{student}/review', [PendingSubmissionController::class, 'review'])->name('submissions.review');

        Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');

        Route::get('/challenges', [\App\Http\Controllers\Teacher\ChallengeController::class, 'index'])->name('challenges');
        Route::post('/challenges', [\App\Http\Controllers\Teacher\ChallengeController::class, 'store'])->name('challenges.store');
        Route::put('/challenges/{challenge}', [\App\Http\Controllers\Teacher\ChallengeController::class, 'update'])->name('challenges.update');
        Route::delete('/challenges/{challenge}', [\App\Http\Controllers\Teacher\ChallengeController::class, 'destroy'])->name('challenges.destroy');
        Route::get('/challenges/{challenge}/submissions', [\App\Http\Controllers\Teacher\ChallengeController::class, 'submissions'])->name('challenges.submissions');
        Route::post('/challenges/{challenge}/submissions/{student}/review', [\App\Http\Controllers\Teacher\ChallengeController::class, 'review'])->name('challenges.review');

        Route::prefix('settings')->name('settings')->group(function () {
            Route::get('/', [TeacherSettingController::class, 'index']);
            Route::post('/', [TeacherSettingController::class, 'update'])->name('.update');
            Route::delete('/avatar', [TeacherSettingController::class, 'removeAvatar'])->name('.avatar.delete');
            Route::post('/request-deletion', [TeacherSettingController::class, 'requestDeletion'])->name('.deletion.request');
        });

        Route::get('/courses', [CourseController::class, 'index'])->name('courses');
        Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');

        Route::prefix('courses')->name('courses.')->group(function () {
            Route::delete('/{course}', [CourseController::class, 'destroy'])->name('destroy');
            Route::get('/{course}/manage', [CourseController::class, 'manage'])->name('manage');
            Route::get('/{course}/preview', [CourseController::class, 'preview'])->name('preview');
            Route::post('/{course}/update', [CourseController::class, 'update'])->name('update');
            Route::post('/{course}/chapters', [CourseController::class, 'updateCurriculum'])->name('update_chapters');
            Route::delete('/{course}/lessons/{lesson}', [CourseController::class, 'destroyLesson'])->name('lessons.destroy');
            Route::delete('/{course}/quizzes/{quiz}', [CourseController::class, 'destroyQuiz'])->name('quizzes.destroy');
            Route::delete('/chapters/{chapter}', [CourseController::class, 'destroyChapter'])->name('chapters.destroy');
            Route::post('/upload-media', [CourseController::class, 'uploadMedia'])->name('media.upload');
            Route::post('/delete-media', [CourseController::class, 'deleteMedia'])->name('media.delete');
        });
    });

    Route::middleware(['auth', 'role:student', 'verified'])->group(function () {

        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');

        Route::prefix('classroom')->name('classroom.')->group(function () {
            Route::get('/my-courses', function (\Illuminate\Http\Request $request) {
                $courses = $request->user()->enrolledCourses()->with('teacher')->get();

                return Inertia::render('Student/MyLearning', [
                    'courses' => $courses
                ]);
            })->name('my-courses');

            Route::get('/{course}/learn', [\App\Http\Controllers\Student\ClassroomController::class, 'show'])->name('show');
            Route::post('/{course}/complete', [\App\Http\Controllers\Student\ClassroomController::class, 'complete'])->name('complete');
            Route::get('/{course}/certificate', [\App\Http\Controllers\Student\ClassroomController::class, 'certificate'])->name('certificate');
        });

        Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio');
        Route::post('/portfolio', [PortfolioController::class, 'store'])->name('student.portfolio.store');
        Route::put('/portfolio/{portfolio}', [PortfolioController::class, 'update'])->name('student.portfolio.update');
        Route::delete('/portfolio/{portfolio}', [PortfolioController::class, 'destroy'])->name('student.portfolio.destroy');

        Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');
        Route::get('/challenges', [\App\Http\Controllers\Student\ChallengeController::class, 'index'])->name('student.challenges');
        Route::post('/challenges/{challenge}/submit', [\App\Http\Controllers\Student\ChallengeController::class, 'submit'])->name('student.challenges.submit');
        Route::get('/challenges/{challenge}/certificate', [\App\Http\Controllers\Student\ChallengeController::class, 'certificate'])->name('student.challenges.certificate');


        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Student\SettingController::class, 'index'])->name('edit');
            Route::post('/', [\App\Http\Controllers\Student\SettingController::class, 'updateProfile'])->name('update');
            Route::delete('/avatar', [\App\Http\Controllers\Student\SettingController::class, 'destroyAvatar'])->name('avatar.delete');
            Route::post('/deletion-request', [\App\Http\Controllers\Student\SettingController::class, 'requestDeletion'])->name('deletion.request');
        });

        Route::post('/courses/{course}/enroll', [CourseEnrollmentController::class, 'store'])->name('student.courses.enroll');
    });
});

require __DIR__ . '/auth.php';
