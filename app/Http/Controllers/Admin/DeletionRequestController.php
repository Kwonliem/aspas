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
                    'class' => $req->user->class, 
                ],
                'reason' => $req->reason,
                'created_at' => Carbon::parse($req->created_at)->format('M d, Y'),
            ];
        };

        
        $studentQuery = DeletionRequest::with('user')
            ->where('status', 'pending')
            ->whereHas('user', function ($q) use ($request) {
                $q->where('role', 'student');
                
                if ($request->search) {
                    $q->where(function ($subQ) use ($request) {
                        $subQ->where('name', 'like', '%' . $request->search . '%')
                             ->orWhere('email', 'like', '%' . $request->search . '%');
                    });
                }
            });

        $studentRequests = $studentQuery->latest()
            ->paginate(10, ['*'], 'student_page') 
            ->through($mapRequest)
            ->withQueryString();


       
        $teacherQuery = DeletionRequest::with('user')
            ->where('status', 'pending')
            ->whereHas('user', function ($q) use ($request) {
                $q->where('role', 'teacher');
                
                if ($request->search) {
                    $q->where(function ($subQ) use ($request) {
                        $subQ->where('name', 'like', '%' . $request->search . '%')
                             ->orWhere('email', 'like', '%' . $request->search . '%');
                    });
                }
            });

        $teacherRequests = $teacherQuery->latest()
            ->paginate(10, ['*'], 'teacher_page') 
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
           
            $user->delete();
        }
        
        return redirect()->back()->with('message', 'User account permanently deleted.');
    }

    public function reject(DeletionRequest $deletionRequest)
    {
        
        $deletionRequest->update(['status' => 'rejected']);
        
        return redirect()->back()->with('message', 'Deletion request rejected.');
    }
}