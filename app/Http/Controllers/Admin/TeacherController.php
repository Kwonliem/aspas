<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'teacher');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('subject', 'like', '%' . $request->search . '%');
            });
        }

        $teachers = $query->latest()
            ->paginate(10)
            ->through(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'email' => $teacher->email,
                    'avatar' => $teacher->avatar,
                    'joined_at' => Carbon::parse($teacher->created_at)->format('M d, Y'),
                    'specialization' => $teacher->subject ?? 'General',
                ];
            })
            ->withQueryString();

        return Inertia::render('Admin/Teachers', [
            'teachers' => $teachers,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'subject' => 'nullable|string|max:100', // Sesuaikan jika wajib/tidak
        ]);

        // 2. Buat User Baru
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'teacher',       // Set role teacher
            'subject' => $validated['subject'] ?? null,

            // KUNCI UTAMA: Set waktu verifikasi ke 'sekarang'
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.teachers')->with('message', 'Teacher account created and verified successfully!');
    }

    public function update(Request $request, User $teacher)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',

            'email' => 'required|email|unique:users,email,' . $teacher->id,
            'subject' => 'required|string|max:100',

            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $teacher->name = $validated['name'];
        $teacher->email = $validated['email'];
        $teacher->subject = $validated['subject'];


        if ($request->filled('password')) {
            $teacher->password = Hash::make($validated['password']);
        }

        $teacher->save();

        return redirect()->back();
    }

    public function destroy(User $teacher)
    {
        $teacher->delete();
        return redirect()->back();
    }
}
