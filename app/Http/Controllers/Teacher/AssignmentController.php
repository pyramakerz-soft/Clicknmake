<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\School;
use App\Models\Stage;
use App\Models\Student_assessment;
use App\Models\TeacherClass;
use DB;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $userAuth = auth()->guard('teacher')->user();

        // if ($userAuth) {
        //     $Assignment = Assignment::where("teacher_id", auth()->user()->id)->with(relations: 'school')->with('lesson')->orderBy("created_at", "desc")->get();

        //     return view("pages.teacher.Assignment.index", compact("Assignment", "userAuth"));
        // } else {
        //     return redirect()->route('login')->withErrors(['error' => 'Unauthorized access']);
        // }
    }
    public function showAssignments($id)
    {
        $userAuth = auth()->guard('teacher')->user();
        $stage = Stage::findOrFail($id);
        if ($userAuth) {
            // Fetch assignments filtered by stage ID
            $assignments = Assignment::where('teacher_id', $userAuth->id)
                ->with(['school', 'lesson.chapter.unit.material'])
                ->whereHas('stages', function ($q) use ($id) {
                    $q->where('stage_id', $id);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            return view('pages.teacher.Assignment.index', compact('assignments', 'stage', 'userAuth'));
        } else {
            return redirect()->route('login')->withErrors(['error' => 'Unauthorized access']);
        }
    }
    // public function showAssignments($id)
    // {
    //     $userAuth = auth()->guard('teacher')->user();

    //     if (!$userAuth) {
    //         abort(401, 'Unauthorized');
    //     }

    //     $hasAccess = Assignment::where('teacher_id', $userAuth->id)
    //         ->whereHas('stages', function ($q) use ($id) {
    //             $q->where('stage_id', $id);
    //         })
    //         ->exists();

    //     if (!$hasAccess) {
    //         abort(403, 'You do not have access to this stage');
    //     }

    //     $stage = Stage::findOrFail($id);

    //     $assignments = Assignment::where('teacher_id', $userAuth->id)
    //         ->with(['school', 'lesson.chapter.unit.material'])
    //         ->whereHas('stages', function ($q) use ($id) {
    //             $q->where('stage_id', $id);
    //         })
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     return view('pages.teacher.Assignment.index', compact('assignments', 'stage', 'userAuth'));
    // }


    /**
     * Show the form for creating a new resource.
     */
    // public function create()
    // {
    //     $userAuth = auth()->guard('teacher')->user();

    //     if ($userAuth) {
    //         $lessons = Lesson::all();
    //         $schools = School::all();

    //         return view('pages.teacher.Assignment.create', compact('lessons', 'schools', "userAuth"));
    //     } else {
    //         return redirect()->route('login')->withErrors(['error' => 'Unauthorized access']);
    //     }
    // }
    // public function create()
    // {
    //     $userAuth = auth()->guard('teacher')->user();

    //     if ($userAuth) {
    //         $lessons = Lesson::all();
    //         $schools = School::all();

    //         // Fetch all classes for the authenticated teacher's school
    //         $classes = Group::where('school_id', $userAuth->school_id)->get();

    //         return view('pages.teacher.Assignment.create', compact('lessons', 'schools', 'classes', 'userAuth'));
    //     } else {
    //         return redirect()->route('login')->withErrors(['error' => 'Unauthorized access']);
    //     }
    // }
    public function create(Request $request)
    {
        $userAuth = auth()->guard('teacher')->user();

        if ($userAuth) {
            $lessons = Lesson::with('chapter.unit.material')->get();
            $stages = $userAuth->stages;
            $classes = TeacherClass::where('teacher_id', $userAuth->id)->with('class')->get();
            $selectedStage = $request->query('stageId');
            // dd($selectedStage);
            return view('pages.teacher.Assignment.create', compact('lessons', 'stages', 'classes', 'userAuth', 'selectedStage'));
        } else {
            return redirect()->route('login')->withErrors(['error' => 'Unauthorized access']);
        }
    }
    public function getClassesByStage($stageId)
    {
        // Get the authenticated teacher
        $userAuth = auth()->guard('teacher')->user();

        if (!$userAuth) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        // Ensure the teacher has the selected stage
        $hasStage = $userAuth->stages()->where('stage_id', $stageId)->exists();
        if (!$hasStage) {
            return response()->json(['error' => 'Teacher does not have access to this stage'], 403);
        }

        // Fetch classes associated with the teacher and the selected stage
        $classes = $userAuth->classes()
            ->whereHas('stage', function ($query) use ($stageId) {
                $query->where('id', $stageId);
            })
            ->get()
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                ];
            });

        return response()->json($classes);
    }



    public function store(Request $request)
    {
        $userAuth = auth()->guard('teacher')->user();

        if (!$userAuth) {
            return redirect()->route('login')->withErrors(['error' => 'Unauthorized access']);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lesson_id' => 'required|exists:lessons,id',
            'stage_id' => 'required|exists:stages,id',
            'class_ids' => 'required|array',
            'class_ids.*' => 'exists:groups,id',
            'week' => 'required',
            'path_file' => 'nullable|file|required_without:link|mimes:pdf,xlsx,xls,doc,docx',
            'link' => 'nullable|url|required_without:path_file',
            'start_date' => 'required|date|after_or_equal:today',
            'due_date' => 'required|date|after_or_equal:start_date',
            'marks' => 'required|integer|min:1|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        $filePath = $request->hasFile('path_file')
            ? $request->file('path_file')->store('assignments', 'public')
            : null;

        $assignment = Assignment::create([
            'title' => $request->title,
            'description' => $request->description,
            'path_file' => $filePath,
            'link' => $request->link,
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'lesson_id' => $request->lesson_id,
            'school_id' => $userAuth->school_id,
            'marks' => $request->marks,
            'is_active' => $request->is_active ?? 0,
            'teacher_id' => $userAuth->id,
            'week' => $request->week,
        ]);

        DB::table('assignment_stage')->insert([
            'assignment_id' => $assignment->id,
            'stage_id' => $request->stage_id,
            'school_id' => $userAuth->school_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($request->class_ids as $classId) {
            DB::table('assignment_class')->insert([
                'assignment_id' => $assignment->id,
                'class_id' => $classId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $students = DB::table('students')->where('class_id', $classId)->get();
            foreach ($students as $student) {
                DB::table('assignment_student')->insert([
                    'assignment_id' => $assignment->id,
                    'student_id' => $student->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $assignments = Assignment::where('teacher_id', $userAuth->id)
            ->with(['school', 'lesson'])

            ->orderBy('created_at', 'desc')
            ->get();

        return redirect()->back()->with('success', 'Assignment created successfully.');
    }






    /**
     * Display the specified resource.
     */

    public function show(string $id)
    {
        $assignment = Assignment::with(['lesson', 'school', 'students'])->findOrFail($id);
        return view('admin.assignments.show', compact('assignment'));
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $userAuth = auth()->guard('teacher')->user();

        if ($userAuth) {
            $assignment = Assignment::findOrFail($id);
            $selectedWeek = $assignment->week;
            $lessons = Lesson::all();

            $school = School::find($userAuth->school_id);

            $stages = DB::table('teacher_stage')
                ->where('teacher_id', $userAuth->id)
                ->join('stages', 'teacher_stage.stage_id', '=', 'stages.id')
                ->select('stages.id', 'stages.name')
                ->get();

            $selectedStage = DB::table('assignment_stage')
                ->where('assignment_id', $assignment->id)
                ->value('stage_id');

            $classes = Group::where('school_id', $userAuth->school_id)->get();

            $selectedClasses = DB::table('assignment_class')
                ->where('assignment_id', $assignment->id)
                ->pluck('class_id')
                ->toArray();

            return view('pages.teacher.Assignment.Edit', compact('assignment', 'lessons', 'stages', 'classes', 'selectedWeek', 'selectedStage', 'selectedClasses', 'userAuth'));
        } else {
            return redirect()->route('login')->withErrors(['error' => 'Unauthorized access']);
        }
    }

    public function update(Request $request, string $id)
    {
        // Get the authenticated teacher
        $userAuth = auth()->guard('teacher')->user();

        if (!$userAuth) {
            return redirect()->route('login')->withErrors(['error' => 'Unauthorized access']);
        }

        // Find the assignment
        $assignment = Assignment::findOrFail($id);

        // Validate the incoming request
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'lesson_id' => 'required|exists:lessons,id',
            'stage_id' => 'required|exists:stages,id',
            'class_ids' => 'required|array',
            'class_ids.*' => 'exists:groups,id',
            'path_file' => 'nullable|file',
            'link' => 'nullable|url',
            'start_date' => 'required|date',
            'due_date' => 'required|date',
            'marks' => 'required|integer',
            'is_active' => 'nullable|boolean',
            'week' => 'required',


        ]);

        // Handle file upload if exists
        if ($request->hasFile('path_file')) {
            $filePath = $request->file('path_file')->store('assignments', 'public');
            $assignment->path_file = $filePath;
        }

        // Update the assignment details
        $assignment->update([
            'title' => $request->title,
            'description' => $request->description,
            'link' => $request->link,
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'lesson_id' => $request->lesson_id,
            'school_id' => $userAuth->school_id, // Automatically set the school_id based on the teacher's school
            'marks' => $request->marks,
            'is_active' => $request->is_active ?? 0,
            'week' => $request->week,

        ]);

        // Sync the assignment with selected stages and classes
        DB::table('assignment_stage')
            ->updateOrInsert(
                ['assignment_id' => $assignment->id],
                ['stage_id' => $request->stage_id, 'updated_at' => now()]
            );

        // Update classes related to the assignment
        DB::table('assignment_class')->where('assignment_id', $assignment->id)->delete();
        DB::table('assignment_student')->where('assignment_id', $assignment->id)->delete();

        foreach ($request->class_ids as $classId) {
            DB::table('assignment_class')->insert([
                'assignment_id' => $assignment->id,
                'class_id' => $classId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Attach assignment to all students in the class
            $students = DB::table('students')->where('class_id', $classId)->get();
            foreach ($students as $student) {
                DB::table('assignment_student')->insert([
                    'assignment_id' => $assignment->id,
                    'student_id' => $student->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('assignments.index')->with('success', 'Assignment updated successfully.');
    }



    /**
     * Remove the specified resource from storage.
     */

    public function destroy(string $id)
    {
        // Find the assignment by id
        $assignment = Assignment::find($id);

        // Check if the assignment exists
        if (!$assignment) {
            return redirect()->route('assignments.index')->with('error', 'Assignment not found.');
        }

        // Delete the assignment
        $assignment->delete();

        // Redirect back with a success message
        return redirect()->route('assignments.index')->with('success', 'Assignment deleted successfully.');
    }

    public function viewAssignedStudents(string $id)
    {
        $currentTeacher = Auth::guard('teacher')->user();

        $assignments = Assignment::where('teacher_id', $currentTeacher->id)
            ->get();

        if (! in_array($id, $assignments->pluck('id')->toArray())) {
            abort(403, 'You do not have access to this assignment');
        }   
        $assignment = Assignment::with(['lesson', 'school'])->findOrFail($id);

        $students = DB::table('assignment_student')
            ->join('students', 'assignment_student.student_id', '=', 'students.id')
            ->where('assignment_student.assignment_id', $id)
            ->select('students.id as student_id', 'students.username as student_name', 'assignment_student.marks', 'assignment_student.path_file', 'assignment_student.submitted_at')
            ->simplePaginate(5);

        return view('pages.teacher.Assignment.students', compact('assignment', 'students'));
    }
    public function updateStudentMarks(Request $request, string $id, string $studentId)
    {
        $request->validate([
            'marks' => 'required|integer|min:0',
        ]);

        DB::table('assignment_student')
            ->where('assignment_id', $id)
            ->where('student_id', $studentId)
            ->update(['marks' => $request->marks]);
        $assignment = DB::table('assignment_student')
            ->where('assignment_id', $id)
            ->where('student_id', $studentId)->first();
        // dd($assignment->id);
        //$student_assignments = Student_assessment::where('assignment_student_id', $assignment->id)->first();
        //$student_assignments->homework_score = $request->marks / 3;
        //$student_assignments->save();
        // dd($student_assignments);
        // $student_assignments


        return redirect()->route('assignments.students', $id)->with('success', 'Marks updated successfully.');
    }
}
