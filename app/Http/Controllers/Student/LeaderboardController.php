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
        // 1. Ambil semua data Student, urutkan berdasarkan XP terbanyak
        $allStudents = User::where('role', 'student')
            ->orderBy('xp', 'desc')
            ->get();

        // 2. Format ulang data agar rapi saat dikirim ke Frontend
        // Kita juga menambahkan "rank" manual sesuai urutan loop
        $formattedStudents = $allStudents->map(function ($student, $index) {
            return [
                'id' => $student->id,
                'rank' => $index + 1, // Rank 1, 2, 3, dst..
                'name' => $student->name,
                'xp' => $student->xp,
                'image' => $student->avatar,
                'initials' => substr($student->name, 0, 2), // Ambil 2 huruf pertama untuk avatar default
                'level' => 'Lvl ' . (floor($student->xp / 500) + 1), // Contoh simple perhitungan level
                'badges' => [] // Nanti bisa diisi jika ada tabel badges
            ];
        });

        // 3. Pisahkan Top 3 (Podium) dan sisanya (Tabel)
        $topThree = $formattedStudents->take(3); // Ambil ranking 1-3
        $rankings = $formattedStudents->slice(3); // Ambil ranking 4 dan seterusnya

        // Karena tampilan podium membutuhkan susunan [Rank 2, Rank 1, Rank 3]
        $podium = [];
        if (isset($topThree[1])) $podium[] = $topThree[1]; // Rank 2 di kiri
        if (isset($topThree[0])) $podium[] = $topThree[0]; // Rank 1 di tengah
        if (isset($topThree[2])) $podium[] = $topThree[2]; // Rank 3 di kanan

        // 4. Cari informasi ranking user yang sedang login
        $currentUserInfo = null;
        $currentUserRank = $formattedStudents->firstWhere('id', $request->user()->id);
        
        if ($currentUserRank) {
            $currentUserInfo = $currentUserRank;
            // Modifikasi sedikit khusus untuk tampilan di bawah tabel
            $currentUserInfo['name'] = $currentUserRank['name'] . ' (You)';
        }

        return Inertia::render('Student/Leaderboard', [
            'podium' => $podium,
            'rankings' => array_values($rankings->toArray()), // Reset array keys
            'currentUserInfo' => $currentUserInfo
        ]);
    }
}