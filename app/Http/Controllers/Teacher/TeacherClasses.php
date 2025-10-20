<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Student_assessment;
use App\Models\TeacherClass;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherClasses extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($stage_id)
    {
        $userAuth = auth()->guard('teacher')->user();

        if (!$userAuth) {
            return redirect()->route('login')->withErrors(['error' => 'Unauthorized access']);
        }

        $classesTeachers = TeacherClass::where('teacher_id', $userAuth->id)
            ->whereHas('class', function ($query) use ($stage_id) {
                $query->where('stage_id', $stage_id);
            })
            ->with('class')
            ->get();
            

        return view('pages.teacher.Class.index', compact('classesTeachers', 'userAuth'));
    }
    public function students(string $class_id)
    {
        $userAuth = auth()->guard('teacher')->user();

        if (!$userAuth) {
            return redirect()->route('login')->withErrors(['error' => 'Unauthorized access']);
        }
        $class = TeacherClass::with(['students.studentAssessment'])
            ->where('class_id', $class_id)
            ->where('teacher_id', $userAuth->id)
            ->first();
        if (!$class) {
            abort(403, 'You do not have permission to view this class');
        }


        $students = $class->students;
        $numberOfWeeks = 8;
        $weeks = range(1, $numberOfWeeks);


        return view('components.GradesTable', compact('students', 'class', 'weeks'));
    }

    public function storeAssessment(Request $request)
    {

        $request->validate([

            'student_id' => 'required|exists:students,id',
            // 'week' => 'required|integer|min:1|max:8',
            'attendance_score' => 'nullable|integer|min:0|max:10',
            'classroom_participation_score' => 'nullable|integer|min:0|max:20',
            'classroom_behavior_score' => 'nullable|integer|min:0|max:20',
            'homework_score' => 'nullable|integer|min:0|max:10',
            'final_project_score' => 'nullable|integer|min:0|max:50',
        ]);


        $teacherId = auth()->guard('teacher')->id();

        $assessment = Student_assessment::firstOrNew([
            'student_id' => $request->student_id,
            'teacher_id' => $teacherId,
            'week' => $request->week,
        ]);
        foreach (['attendance_score', 'classroom_participation_score', 'classroom_behavior_score', 'homework_score', 'final_project_score'] as $field) {
            if ($request->has($field)) {
                $assessment->$field = $request->$field;
            }
        }

        $assessment->save();

        return response()->json(['success' => true, 'message' => 'Assessment saved successfully']);


    }


    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $userAuth = auth()->guard('teacher')->user();

        if (!$userAuth) {
            return redirect()->route('login')->withErrors(['error' => 'Unauthorized access']);
        }

        $StudentAssessment = Student_assessment::where('student_id', $id)->get();

        if (!$StudentAssessment) {
            return redirect()->route('teacher_classes')->withErrors(['error' => 'Class not found or unauthorized access.']);
        }

        dd($StudentAssessment);


        return view('components.GradeTableForOneStudent', compact('StudentAssessment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
