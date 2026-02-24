<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeletionRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeletionRequestController extends Controller
{
    public function index(Request $request)
    {
        // Fungsi untuk memformat (mapping) data agar rapi dan bisa dipakai ulang
        $mapRequest = function ($req) {
            return [
                'id' => $req->id,
                'user' => [
                    'id' => $req->user->id,
                    'name' => $req->user->name,
                    'email' => $req->user->email,
                    'role' => ucfirst($req->user->role),
                    'avatar' => $req->user->avatar,
                    'subject' => $req->user->subject, 
                    'class' => $req->user->class, // Tambahkan class untuk Student
                ],
                'reason' => $req->reason,
                'created_at' => Carbon::parse($req->created_at)->format('M d, Y'),
            ];
        };

        // 1. Query khusus STUDENT
        $studentQuery = DeletionRequest::with('user')
            ->where('status', 'pending')
            ->whereHas('user', function ($q) use ($request) {
                $q->where('role', 'student');
                // Fitur Search khusus Student
                if ($request->search) {
                    $q->where(function ($subQ) use ($request) {
                        $subQ->where('name', 'like', '%' . $request->search . '%')
                             ->orWhere('email', 'like', '%' . $request->search . '%');
                    });
                }
            });

        $studentRequests = $studentQuery->latest()
            ->paginate(10, ['*'], 'student_page') // Bedakan nama paginasinya
            ->through($mapRequest)
            ->withQueryString();


        // 2. Query khusus TEACHER
        $teacherQuery = DeletionRequest::with('user')
            ->where('status', 'pending')
            ->whereHas('user', function ($q) use ($request) {
                $q->where('role', 'teacher');
                // Fitur Search khusus Teacher
                if ($request->search) {
                    $q->where(function ($subQ) use ($request) {
                        $subQ->where('name', 'like', '%' . $request->search . '%')
                             ->orWhere('email', 'like', '%' . $request->search . '%');
                    });
                }
            });

        $teacherRequests = $teacherQuery->latest()
            ->paginate(10, ['*'], 'teacher_page') // Bedakan nama paginasinya
            ->through($mapRequest)
            ->withQueryString();


        return Inertia::render('Admin/Deletions', [
            'studentRequests' => $studentRequests,
            'teacherRequests' => $teacherRequests,
            'filters' => $request->only(['search']),
        ]);
    }

    public function approve(DeletionRequest $deletionRequest)
    {
        $user = $deletionRequest->user;
        
        if ($user) {
            // Hapus user (Request otomatis terhapus karena cascade on delete di migration)
            $user->delete();
        }
        
        return redirect()->back()->with('message', 'User account permanently deleted.');
    }

    public function reject(DeletionRequest $deletionRequest)
    {
        // Ubah status jadi rejected (agar history tetap ada)
        // Pastikan migration enum status sudah ada 'rejected'
        $deletionRequest->update(['status' => 'rejected']);
        
        return redirect()->back()->with('message', 'Deletion request rejected.');
    }
}