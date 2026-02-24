<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeletionRequest;
use App\Models\StudentProfile;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'students' => User::where('role', 'student')->count(),
                'teachers' => User::where('role', 'teacher')->count(),
                'deletions' => DeletionRequest::where('status', 'pending')->count(),
            ],
            'topStudents' => StudentProfile::with('user')->orderBy('xp', 'desc')->take(5)->get(),
            'recentTeachers' => User::where('role', 'teacher')->latest()->take(5)->get(),
        ]);
    }
}