<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        
        $allStudents = User::where('role', 'student')
            ->orderBy('xp', 'desc')
            ->get();

        
        $formattedStudents = $allStudents->map(function ($student, $index) {
            return [
                'id' => $student->id,
                'rank' => $index + 1, 
                'name' => $student->name,
                'xp' => $student->xp,
                'image' => $student->avatar,
                'initials' => substr($student->name, 0, 2), 
                'level' => 'Lvl ' . (floor($student->xp / 500) + 1), 
                'badges' => [] 
            ];
        });

        
        $topThree = $formattedStudents->take(3); 
        $rankings = $formattedStudents->slice(3); 

        
        $podium = [];
        if (isset($topThree[1])) $podium[] = $topThree[1]; 
        if (isset($topThree[0])) $podium[] = $topThree[0]; 
        if (isset($topThree[2])) $podium[] = $topThree[2];

       
        $currentUserInfo = null;
        $currentUserRank = $formattedStudents->firstWhere('id', $request->user()->id);
        
        if ($currentUserRank) {
            $currentUserInfo = $currentUserRank;
            
            $currentUserInfo['name'] = $currentUserRank['name'] . ' (You)';
        }

        return Inertia::render('Student/Leaderboard', [
            'podium' => $podium,
            'rankings' => array_values($rankings->toArray()), 
            'currentUserInfo' => $currentUserInfo
        ]);
    }
}