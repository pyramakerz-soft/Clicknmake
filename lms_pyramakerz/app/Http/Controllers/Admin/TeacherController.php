<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\School;
use App\Models\Stage;
use App\Models\Teacher;
use App\Models\TeacherClass;
use Hash;
use Illuminate\Http\Request;
use Str;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $teacherQuery = Teacher::with('school')->whereNull('alias_id');

        $schools = School::all();

        if ($request->filled('school')) {
            $teacherQuery->where('school_id', $request->school);
        }

        if ($request->filled('search')) {
            $teacherQuery->where('name', 'LIKE', '%' . $request->search . '%');
        }

        $teachers = $teacherQuery->paginate(10)->appends($request->query());

        return view('admin.teachers.index', compact('teachers', 'schools'));
    }
    /**
     * Show the form for creating a new resource.
     */

    // function updateTeacherNames()
    // {
    //     // Fetch all teachers
    //     $teachers = Teacher::all();

    //     foreach ($teachers as $teacher) {
    //         // Format the username into a name
    //         if ($teacher->username) {
    //             $formattedName = collect(explode('_', $teacher->username))
    //                 ->map(fn($part) => ucfirst($part)) // Capitalize each part
    //                 ->join(' '); // Join the parts with a space

    //             // Update the name column
    //             $teacher->update(['name' => $formattedName]);
    //         }
    //     }
    // }
    public function create()
    {
        $schools = School::all();
        $stages = Stage::all();
        return view('admin.teachers.create', compact('schools', 'stages'));
    }
    public function addSchool(string $id)
    {
        $mainteacher = Teacher::findOrFail($id);
        $schools = School::all();
        $stages = Stage::all();
        $teacherAlias = Teacher::where('alias_id', $mainteacher->id)->get();
        return view('admin.teachers.add_school', compact('mainteacher', 'teacherAlias', 'schools', 'stages'));
    }
    /**
     * Store a newly created resource in storage.
     */
    public function storeSchool(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'stage_ids' => 'required|array',
            'stage_ids.*' => 'exists:stages,id',
            'class_id' => 'required|array',
            'class_id.*' => 'exists:groups,id'
        ]);
        $existingTeacher = Teacher::find($request->mainteacher);
        $school = School::find($request->school_id);

        $username = strtolower(str_replace(' ', '_', $existingTeacher->name)) . '_' . strtolower(str_replace(' ', '_', School::find($request->school_id)->name . $existingTeacher->id));

        // dd($username);
        // $username = $existingTeacher->username . '_' . $school->name;
        $existingTeacherSchool  = Teacher::where('username', $username)->get();
        if ($existingTeacherSchool->count() > 0) {
            return redirect()->back()->with('error', 'Teacher already added to this school.');
        }
        $teacher = Teacher::create([
            'name' => $existingTeacher->name,
            'username' => $username,
            'password' => Hash::make($existingTeacher->plain_password),
            'plain_password' => $existingTeacher->plain_password,
            'gender' => $existingTeacher->gender,
            'school_id' => $request->input('school_id'),
            'is_active' => 1,
            'alias_id' => $existingTeacher->id
        ]);

        $teacher->classes()->attach($request->input('class_id'));

        $teacher->stages()->attach($request->input('stage_ids'));
        return redirect()->route('teachers.addSchool', ['teacherId' => $request->mainteacher])
            ->with('success', 'School added to teacher successfully.');
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'regex:/^[\p{Arabic}A-Za-z\s]+$/u'],
            'gender' => 'required',
            'school_id' => 'required|exists:schools,id',
            'stage_ids' => 'required|array',
            'stage_ids.*' => 'exists:stages,id',
            'class_id' => 'required|array',
            'class_id.*' => 'exists:groups,id',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $password = Str::random(8);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('teachers', 'public');
        }
        $teacher = Teacher::create([
            'name' => $request->name,
            'school_id' => $request->input('school_id'),
            'password' => Hash::make($password),
            'plain_password' => $password,
            'gender' => $request->input('gender'),
            'is_active' => 1,
            'image' => $imagePath,
        ]);

        $username = strtolower(str_replace(' ', '_', $request->input('name'))) . '_' . School::find($request->school_id)->name . $teacher->id;
        $teacher->update([
            'username' => $username,
        ]);

        $teacher->classes()->attach($request->input('class_id'));

        $teacher->stages()->attach($request->input('stage_ids'));
        // dd($teacher);
        return redirect()->route('teachers.index')->with('success', 'Teacher created successfully.');
    }
    public function generate(Request $request)
    {
        $request->validate([
            'number_of_teachers' => 'required|integer|min:1',
            'school_id' => 'required|exists:schools,id',
        ]);

        $numberOfTeachers = $request->input('number_of_teachers');
        $schoolId = $request->input('school_id');
        $school = School::findOrFail($schoolId);

        $currentCount = Teacher::where('school_id', $schoolId)->count();

        for ($i = 1; $i <= $numberOfTeachers; $i++) {
            $username = str_replace(' ', '_', strtolower($school->name)) . '_' . ($currentCount + $i);
            $password = Str::random(8);

            Teacher::create([
                'name' => 'Teacher' . ($currentCount + $i) . '_' . $school->name,
                'username' => $username,
                'password' => Hash::make($password),
                'plain_password' => $password,
                'school_id' => $schoolId,
                'is_active' => 1,
            ]);
        }

        return redirect()->route('teachers.index')->with('success', "$numberOfTeachers teachers generated successfully.");
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    // public function edit(string $id)
    // {
    //     $teacher = Teacher::findOrFail($id);
    //     $schools = School::all();
    //     $stages = Stage::all();

    //     $classes = Group::where('school_id', $teacher->school_id)->get();

    //     return view('admin.teachers.edit', compact('teacher', 'schools', 'classes', 'stages'));
    // }

    public function edit(string $id)
    {
        $teacher = Teacher::findOrFail($id);


        $schools = School::all();

        $stages = Stage::whereHas('schools', function ($query) use ($teacher) {
            $query->where('school_id', $teacher->school_id);
        })->get();

        // Fetch classes related to the teacher's school.
        $classes = Group::where('school_id', $teacher->schools)->get();
        return view('admin.teachers.edit', compact('teacher', 'schools', 'classes', 'stages'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $teacher = Teacher::findOrFail($id);

        $request->validate([
            'school_id' => 'sometimes|exists:schools,id',
            'stage_ids' => 'sometimes|array',
            'stage_ids.*' => 'exists:stages,id',
            'class_id' => 'sometimes|array',
            'class_id.*' => 'exists:groups,id',
            'password' => 'nullable|string|confirmed|min:6',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $username = str_replace(' ', '_', $request->input('username'));

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('teachers', 'public');
            $teacher->image = $imagePath;
        }
        if ($request->has('school_id')) {
            $teacher->update([
                'school_id' => $request->input('school_id'),
                'password' => Hash::make($request->password),
                'plain_password' => $request->password,
                'is_active' => $request->input('is_active') ?? 1,
            ]);

            $teacher->classes()->sync($request->input('class_id'));
            $teacher->stages()->sync($request->input('stage_ids'));
        } else {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('teachers', 'public');
            }
            $teacher->update([
                'gender' => $request->input('gender'),
                'name' => $request->name,
                'image' => $imagePath,
            ]);
        }



        if ($teacher->alias_id != null) {
            $returnId = $teacher->alias_id;
        } else {
            $returnId = $teacher->id;
        }
        return redirect()->route('teachers.addSchool', ['teacherId' => $returnId])
            ->with('success', 'Teacher Updated Successfully.');

        // return redirect()->route('teachers.index')->with('success', 'Teacher updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {
        if ($id != $request->mainteacher) {
            $teacher = Teacher::findOrFail($id);
            $teacher->delete();
            return redirect()->route('teachers.addSchool', ['teacherId' => $request->mainteacher])
                ->with('success', 'School removed from this teacher successfully.');
        } else {
            // $teacherAliases = json_decode($request->teacher_aliases);
            $teacherAliases = Teacher::where('alias_id', $id)->get();
            if ($teacherAliases->isNotEmpty()) {

                // dd($teacherAliases);
                $newMainTeacherId = $teacherAliases[0]->id;
                // dd($newMainTeacherId);
                foreach (json_decode($request->teacher_aliases) as $teacherAlias) {
                    // dd($teacherAlias);
                    $teacher = Teacher::find($teacherAlias->id);
                    $teacher->update([
                        'alias_id' => $newMainTeacherId,
                    ]);
                }
                $newMainTeacher = Teacher::find($teacherAliases[0]->id);
                $newMainTeacher->update([
                    'alias_id' => null,
                ]);
                $teacher = Teacher::findOrFail($id);
                $teacher->delete();
                return redirect()->route('teachers.addSchool', ['teacherId' => $newMainTeacherId])
                    ->with('success', 'School removed from this teacher successfully.');
            } else {
                $teacher = Teacher::findOrFail($id);
                $teacher->delete();
                return redirect()->route('teachers.index')->with('success', 'Teacher removed successfully.');
            }
        }

        // if ($request->school_list_flag == 1) {
        //     return redirect()->route('teachers.addSchool', ['teacherId' => $request->mainteacher])
        //         ->with('success', 'School removed from this teacher successfully.');
        // }
    }
}
