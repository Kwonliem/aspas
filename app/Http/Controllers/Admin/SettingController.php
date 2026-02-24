<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; // Jangan lupa import ini
use Illuminate\Validation\Rule; // Import Rule untuk validasi unique
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Settings', [
            'status' => session('status'),
        ]);
    }

    // --- TAMBAHKAN METHOD UPDATE INI ---
    public function update(Request $request)
    {
        $user = $request->user();

        // 1. Validasi Input
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // Validasi email unik, kecuali untuk user ini sendiri
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        // 2. Logic Update & Bypass Verifikasi
        if ($validated['email'] !== $user->email) {
            $user->email = $validated['email'];
            
            // BYPASS: Set waktu verifikasi ke SEKARANG agar langsung verified
            $user->email_verified_at = now(); 
        }

        $user->name = $validated['name'];
        $user->save();

        // 3. Kembali ke halaman setting dengan pesan sukses
        return back()->with('status', 'profile-updated'); 
    }
}