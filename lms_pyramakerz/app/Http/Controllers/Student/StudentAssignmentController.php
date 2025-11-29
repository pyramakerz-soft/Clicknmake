<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Assignment;
use Carbon\Carbon;
use DB;

class StudentAssignmentController extends Controller
{
    public function index()
    {
        $userAuth = auth()->guard('student')->user();

        if ($userAuth) {
            $assignments = \DB::table('assignments')
                ->join('assignment_student', 'assignments.id', '=', 'assignment_student.assignment_id')
                ->where('assignment_student.student_id', $userAuth->id)
                ->select('assignments.*', 'assignment_student.marks as student_marks')
                ->get();

            return view('pages.student.assignment.index', compact('assignments', 'userAuth'));
        } else {
            return redirect()->route('login')->withErrors(['error' => 'Unauthorized access']);
        }
    }


    public function show($assignmentId)
    {
        $userAuth = auth()->guard('student')->user();

        if ($userAuth) {
            $assignment = Assignment::find($assignmentId);

            $studentAssignment = DB::table('assignment_student')
                ->where('assignment_id', $assignmentId)
                ->where('student_id', $userAuth->id)
                ->first();

            if (file_exists($assignment->path_file)) {
                $assignment->file_size = filesize($assignment->path_file);
            } else {
                $assignment->file_size = 'File not found';
            }
            return view('pages.student.assignment.show', compact('assignment', 'userAuth', 'studentAssignment'));
        } else {
            return redirect()->route('login')->withErrors(['error' => 'Unauthorized access']);
        }
    }

    // public function store(Request $request)
    // {
    //     $userAuth = auth()->guard('student')->user();

    //     if ($userAuth) {
    //         $request->validate([
    //             'file_upload' => 'required|file|mimes:xlsx,xls,pdf',
    //             'assignment_id' => 'required|exists:assignments,id',
    //         ]);

    //         $currentTime = Carbon::now();
    //         $filePath = $request->file('file_upload')->store('assignments', 'public');

    //         $assignmentId = $request->assignment_id;
    //         $studentId = $userAuth->id;
    //         $studentAssignment = $userAuth->assignments()->where('assignment_id', $assignmentId)->first();



    //         if ($studentAssignment) {
    //             $studentAssignment->updateExistingPivot->submitted_at = $currentTime->toDateString();
    //             $studentAssignment->updateExistingPivot->path_file = $filePath;
    //             $studentAssignment->updateExistingPivot->save();

    //         }

    //         return redirect()->route('student.assignment.show', $assignmentId)->with('success', 'Assignment submitted successfully.');
    //     } else {
    //         return redirect()->route('login')->withErrors(['error' => 'Unauthorized access']);
    //     }
    // }

    public function store(Request $request)
    {
        $userAuth = auth()->guard('student')->user();

        if (!$userAuth) {
            return redirect()->route('login')->withErrors(['error' => 'Unauthorized access']);
        }

        $request->validate([
            'file_upload' => 'required|file|mimes:xlsx,xls,pdf',
            'assignment_id' => 'required|exists:assignments,id',
        ]);

        $currentTime = Carbon::now();
        $filePath = $request->file('file_upload')->store('assignments', 'public');

        $assignmentId = $request->assignment_id;
        $studentId = $userAuth->id;

        $studentAssignment = DB::table('assignment_student')
            ->where('assignment_id', $assignmentId)
            ->where('student_id', $studentId)
            ->first();

        if ($studentAssignment) {
            DB::table('assignment_student')
                ->where('assignment_id', $assignmentId)
                ->where('student_id', $studentId)
                ->update([
                    'submitted_at' => $currentTime->toDateString(),
                    'path_file' => $filePath,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('assignment_student')->insert([
                'assignment_id' => $assignmentId,
                'student_id' => $studentId,
                'submitted_at' => $currentTime->toDateString(),
                'path_file' => $filePath,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('student.assignment.show', $assignmentId)->with('success', 'Assignment submitted successfully.');
    }
}
