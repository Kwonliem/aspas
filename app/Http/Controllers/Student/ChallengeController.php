<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ChallengeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        
        $challenges = Challenge::with(['participants' => function($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->orderBy('end_date', 'desc')
            ->get()
            ->map(function ($challenge) {
                $participant = $challenge->participants->first();
                return [
                    'id' => $challenge->id,
                    'title' => $challenge->title,
                    'description' => $challenge->description,
                    'xp_reward' => $challenge->xp_reward,
                    'credit_reward' => $challenge->credit_reward,
                    'end_date' => $challenge->end_date->format('M d, Y H:i'),
                    'is_expired' => $challenge->end_date->isPast(),
                    'status' => $participant ? $participant->pivot->status : 'not_started',
                    'submission_link' => $participant ? $participant->pivot->submission_link : null,
                ];
            });

        return Inertia::render('Student/Challenges', [
            'challenges' => $challenges
        ]);
    }

    public function submit(Request $request, Challenge $challenge)
    {
        
        if ($challenge->end_date->isPast()) {
            return back()->withErrors(['message' => 'Waktu challenge ini sudah habis.']);
        }

        $request->validate([
            'link' => 'required|url|max:255'
        ]);

        $user = $request->user();
        
        
        $challenge->participants()->syncWithoutDetaching([
            $user->id => [
                'submission_link' => $request->link,
                'status' => 'pending'
            ]
        ]);

        return back();
    }

    public function certificate(Request $request, Challenge $challenge)
    {
        $user = $request->user();
        
        $participant = $challenge->participants()->where('user_id', $user->id)->first();

        if (!$participant || $participant->pivot->status !== 'passed') {
            abort(403, 'Anda belum lulus challenge ini.');
        }

        $pdf = Pdf::loadView('certificates.template', [
            'student' => $user->name,
            'course' => 'Challenge: ' . $challenge->title, 
            'date' => $participant->pivot->updated_at->format('d F Y')
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Challenge-Certificate-' . Str::slug($challenge->title) . '.pdf');
    }
}