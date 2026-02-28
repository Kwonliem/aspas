<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\DeletionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        
        $pendingDeletion = DeletionRequest::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        return Inertia::render('Teacher/Settings', [
            'deletion_request' => $pendingDeletion 
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'specialization' => 'nullable|string|max:100',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        
        
        $user->subject = $validated['specialization'] ?? $user->subject; 

       
        if ($request->hasFile('avatar')) {
            
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                
                $oldFile = str_replace('/storage/', '', $user->avatar);
                if (Storage::disk('public')->exists($oldFile)) {
                    Storage::disk('public')->delete($oldFile);
                }
            }

          
            $file = $request->file('avatar');
            
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('avatars', $filename, 'public');

            
            $user->avatar = '/storage/' . $path;
        }

        $user->save();

        
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

    
    public function requestDeletion(Request $request)
    {
       
        $request->validate([
            'password' => ['required', 'current_password'], 
            'reason' => ['required', 'string', 'min:10'],   
        ]);

       
        $exists = DeletionRequest::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return back()->withErrors(['error' => 'You already have a pending deletion request.']);
        }

       
        DeletionRequest::create([
            'user_id' => auth()->id(),
            'reason' => $request->reason,
            'status' => 'pending'
        ]);

        
        return back()->with('message', 'Account deletion request submitted to Admin.');
    }
}