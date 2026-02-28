<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChallengeController extends Controller
{
    public function index()
    {
        $challenges = Challenge::where('teacher_id', auth()->id())
            ->withCount(['participants as pending_submissions' => function ($query) {
                $query->where('challenge_user.status', 'pending');
            }])
            ->latest()
            ->get();

        return Inertia::render('Teacher/Challenges', [
            'challenges' => $challenges
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'xp_reward' => 'required|integer|min:0',
            'credit_reward' => 'required|integer|min:0',
            'end_date' => 'required|date|after:today',
        ]);

        $validated['teacher_id'] = auth()->id();

        Challenge::create($validated);

        return redirect()->back();
    }

    public function update(Request $request, Challenge $challenge)
    {
        if ($challenge->teacher_id !== auth()->id()) abort(403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'xp_reward' => 'required|integer|min:0',
            'credit_reward' => 'required|integer|min:0',
            'end_date' => 'required|date',
        ]);

        $challenge->update($validated);

        return redirect()->back();
    }

    public function destroy(Challenge $challenge)
    {
        if ($challenge->teacher_id !== auth()->id()) abort(403);

        $challenge->delete();

        return redirect()->back();
    }


    public function submissions(Challenge $challenge)
    {
        if ($challenge->teacher_id !== auth()->id()) abort(403);

        $submissions = $challenge->participants()
            ->select('users.id', 'users.name', 'users.email')
            ->withPivot('submission_link', 'status', 'created_at', 'updated_at')
            ->orderByRaw("FIELD(status, 'pending', 'passed', 'failed')")
            ->latest('challenge_user.created_at')
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'link' => $student->pivot->submission_link,
                    'status' => $student->pivot->status,
                    'submitted_at' => $student->pivot->created_at->diffForHumans(),
                ];
            });

        return Inertia::render('Teacher/ChallengeSubmissions', [
            'challenge' => $challenge,
            'submissions' => $submissions
        ]);
    }

    public function review(Request $request, Challenge $challenge, \App\Models\User $student)
    {
        if ($challenge->teacher_id !== auth()->id()) abort(403);

        $request->validate(['status' => 'required|in:passed,failed']);

        $participant = $challenge->participants()->where('user_id', $student->id)->first();
        if (!$participant) abort(404);

        $oldStatus = $participant->pivot->status;
        $newStatus = $request->status;

        if ($oldStatus !== 'passed' && $newStatus === 'passed') {
            $student->increment('xp', $challenge->xp_reward);
            
            $student->increment('credits', $challenge->credit_reward);
        } elseif ($oldStatus === 'passed' && $newStatus !== 'passed') {
            $student->decrement('xp', $challenge->xp_reward);
            $student->decrement('credits', $challenge->credit_reward);
        }

        $challenge->participants()->updateExistingPivot($student->id, [
            'status' => $newStatus
        ]);

        return redirect()->back();
    }
}
