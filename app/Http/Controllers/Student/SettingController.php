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
    
    public function index(Request $request)
    {
        return Inertia::render('Student/Settings', [
            'deletion_request' => $request->user()->deletionRequest()->first(),
        ]);
    }

    
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

        
        if ($validated['email'] !== $user->email) {
            $user->email_verified_at = null;
            $user->email = $validated['email'];
            
            
        }

        
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

   
    public function requestDeletion(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();

        
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