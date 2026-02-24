<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered; // Hapus atau biarkan tidak terpakai
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'nis' => 'required|string|max:20',
            'class' => 'required|string|max:20',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nis' => $request->nis,
            'class' => $request->class,
            'role' => 'student',
            'credits' => 20,
            'xp' => 0,
            'password' => Hash::make($request->password),
        ]);

        // --- BAGIAN INI BIANG KEROKNYA ---
        // HAPUS atau KOMENTARI baris ini agar email TIDAK otomatis terkirim
        // event(new Registered($user)); 
        // ----------------------------------

        Auth::login($user);

        // Redirect ke halaman Verify Email (User akan melihat tombol "Send Verification Link" di sana)
        return redirect()->route('verification.notice');
    }
}