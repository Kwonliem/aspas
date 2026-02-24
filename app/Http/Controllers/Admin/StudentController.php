<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'student');

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm)
                  ->orWhere('nis', 'like', $searchTerm);
            });
        }

        if ($request->filled('class_filter')) {
            $query->where('class', $request->class_filter);
        }

        $students = $query->orderBy('xp', 'desc')->paginate(10)->withQueryString();

        $availableClasses = User::where('role', 'student')
            ->whereNotNull('class')
            ->distinct()
            ->pluck('class');

        return Inertia::render('Admin/Students', [
            'students' => $students,
            'filters' => $request->only(['search', 'class_filter']),
            'availableClasses' => $availableClasses,
        ]);
    }

    public function update(Request $request, User $student)
    {
        if ($student->role !== 'student') {
            abort(403);
        }

        $request->validate([
            'nis' => 'nullable|string|max:255|unique:users,nis,' . $student->id,
        ]);

        $student->update([
            'nis' => $request->nis,
        ]);

        return redirect()->back();
    }

    public function destroy(User $student)
    {
        if ($student->role !== 'student') {
            abort(403);
        }

        $student->delete();

        return redirect()->back();
    }
}