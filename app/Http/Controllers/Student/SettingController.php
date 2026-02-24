<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use App\Models\DeletionRequest; 

class SettingController extends Controller
{
    // 1. Tampilkan Halaman Setting
    public function index(Request $request)
    {
        return Inertia::render('Student/Settings', [
            'deletion_request' => $request->user()->deletionRequest()->first(),
        ]);
    }

    // 2. Update Profile (Nama, Email, Class, Bio, Avatar)
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'class' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|max:2048',
        ]);

        // Jika email berubah, cabut status verifikasi & matikan kirim email otomatis
        if ($validated['email'] !== $user->email) {
            $user->email_verified_at = null;
            $user->email = $validated['email'];
            
            // 👇 DIMATIKAN AGAR TIDAK ERROR SMTP DAN TIDAK NGIRIM OTOMATIS 👇
            // $user->sendEmailVerificationNotification();
        }

        // Handle Avatar Upload
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                $oldPath = str_replace('/storage/', '', $user->avatar);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = '/storage/' . $path;
        }

        $user->fill([
            'name' => $validated['name'],
            'class' => $validated['class'] ?? $user->class,
            'bio' => $validated['bio'] ?? $user->bio,
            'avatar' => $validated['avatar'] ?? $user->avatar,
        ]);
        
        $user->save();

        return redirect()->back();
    }

    // 3. Hapus Avatar
    public function destroyAvatar(Request $request)
    {
        $user = $request->user();

        if ($user->avatar) {
            $path = str_replace('/storage/', '', $user->avatar);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            
            $user->update(['avatar' => null]);
        }

        return redirect()->back();
    }

    // 4. Request Deletion (Hapus Akun)
    public function requestDeletion(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();

        // Cek apakah sudah pernah request sebelumnya
        if ($user->deletionRequest()->exists()) {
            return back()->withErrors(['reason' => 'You already have a pending deletion request.']);
        }

        $user->deletionRequest()->create([
            'reason' => $request->reason,
            'status' => 'pending'
        ]);

        return redirect()->back();
    }
}