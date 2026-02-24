<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\DeletionRequest; // <--- Import Model ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        // Cek apakah user sudah pernah request deletion yang statusnya pending
        // Ini dikirim ke frontend agar tombol bisa berubah jadi "Pending"
        $pendingDeletion = DeletionRequest::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        return Inertia::render('Teacher/Settings', [
            'deletion_request' => $pendingDeletion // <--- Kirim data ke Vue
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        // Validasi
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'specialization' => 'nullable|string|max:100',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Maksimal 2MB
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        // Pastikan kolom di database benar 'subject' atau 'specialization'
        // Di kode awal Anda tertulis $user->subject = ... tapi validasi 'specialization'
        // Saya biarkan sesuai kode asli Anda:
        $user->subject = $validated['specialization'] ?? $user->subject; 

        // Cek apakah ada file avatar yang dikirim
        if ($request->hasFile('avatar')) {
            // Hapus foto lama jika ada (dan bukan foto default dari UI Avatars)
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                // Ambil path relatif tanpa '/storage/' untuk dihapus via Storage Facade
                $oldFile = str_replace('/storage/', '', $user->avatar);
                if (Storage::disk('public')->exists($oldFile)) {
                    Storage::disk('public')->delete($oldFile);
                }
            }

            // Simpan foto baru
            $file = $request->file('avatar');
            // Generate nama unik agar browser tidak cache foto lama
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('avatars', $filename, 'public');

            // Simpan path lengkap ke database
            $user->avatar = '/storage/' . $path;
        }

        $user->save();

        // Redirect kembali dengan pesan sukses
        return redirect()->back()->with('message', 'Profile updated successfully!');
    }

    public function removeAvatar(Request $request)
    {
        $user = $request->user();

        if ($user->avatar && str_contains($user->avatar, '/storage/')) {
            $oldPath = str_replace('/storage/', '', $user->avatar);

            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $user->avatar = null;
        $user->save();

        return redirect()->back();
    }

    // --- LOGIKA BARU UNTUK REQUEST DELETION ---
    public function requestDeletion(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'password' => ['required', 'current_password'], // Keamanan: Konfirmasi password user saat ini
            'reason' => ['required', 'string', 'min:10'],   // Alasan wajib diisi
        ]);

        // 2. Cek apakah sudah ada request pending (Mencegah spam)
        $exists = DeletionRequest::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return back()->withErrors(['error' => 'You already have a pending deletion request.']);
        }

        // 3. Simpan ke Database
        DeletionRequest::create([
            'user_id' => auth()->id(),
            'reason' => $request->reason,
            'status' => 'pending'
        ]);

        // 4. Redirect kembali
        return back()->with('message', 'Account deletion request submitted to Admin.');
    }
}