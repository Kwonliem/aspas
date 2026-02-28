<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use Illuminate\Validation\Rule; 
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Settings', [
            'status' => session('status'),
        ]);
    }

   
    public function update(Request $request)
    {
        $user = $request->user();

        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        
        if ($validated['email'] !== $user->email) {
            $user->email = $validated['email'];
            
            
            $user->email_verified_at = now(); 
        }

        $user->name = $validated['name'];
        $user->save();

        
        return back()->with('status', 'profile-updated'); 
    }
}