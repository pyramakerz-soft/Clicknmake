<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StudentsExport;
use App\Http\Controllers\Controller;
use App\Imports\StudentsImport;
use App\Models\Group;
use App\Models\School;
use App\Models\Stage;
use App\Models\Student;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Storage;

class ClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Group::query();

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        $classes = $query->paginate(20);
        $schools = School::all();

        return view('admin.classes.index', compact('classes', 'schools'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $schools = School::all();
        $stages = Stage::all();
        return view('admin.classes.create', compact('schools', 'stages'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'school_id' => 'required|exists:schools,id',
            'stage_id' => 'required|exists:stages,id',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('classes', 'public');
        }
        $class = Group::create([
            'name' => $request->input('name'),
            'school_id' => $request->input('school_id'),
            'stage_id' => $request->input('stage_id'),
            'image' => $imagePath,
        ]);

        return redirect()->route('classes.index')->with('success', 'Class created successfully.');
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
    public function edit(string $id)
    {
        $class = Group::findOrFail($id);
        $schools = School::all();
        $stages = Stage::all();

        return view('admin.classes.edit', compact('class', 'schools', 'stages'));
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, string $id)
    {
        $class = Group::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'school_id' => 'required|exists:schools,id',
            'stage_id' => 'required|exists:stages,id',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('classes', 'public');
            if ($class->image) {
                Storage::disk('public')->delete($class->image);
            }
            $class->image = $imagePath;
        }

        $class->update([
            'name' => $request->input('name'),
            'school_id' => $request->input('school_id'),
            'stage_id' => $request->input('stage_id'),
        ]);

        $class->save();

        return redirect()->route('classes.index')->with('success', 'Class updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $class = Group::findOrFail($id);
        $class->delete();
        return redirect()->route('classes.index')->with('success', 'Class deleted successfully.');
    }
    public function showImportForm($id)
    {
        $class = Group::findOrFail($id);
        return view('admin.classes.import', compact('class'));
    }
    public function importStudents(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        $import = new StudentsImport($id);

        // Import the file
        Excel::import($import, $request->file('file'));

        // Check if there are any duplicate usernames
        if (!empty($import->duplicateUsernames)) {
            $duplicates = implode(', ', $import->duplicateUsernames);
            // return back()->withErrors(['file' => "Students Imported except duplicate usernames found: $duplicates"]);
            return redirect()->route('classes.index')->with('success', "Students imported successfully and the following exisitng students classes has been updated $duplicates.");
        }
        return redirect()->route('classes.index')->with('success', 'Students imported successfully.');
    }

    public function exportStudents($id)
    {
        $class = Group::findOrFail($id);
        $safeClassName = preg_replace('/[\/\\\\:*?"<>|]/', '-', $class->name);
        $fileName = 'students_' . $safeClassName . '.xlsx';

        return Excel::download(new StudentsExport($id), $fileName);
    }

    public function promoteMultiple(Request $request)
    {
        $request->validate([
            'class_ids' => 'required|array|min:1',
        ]);

        $classIds = $request->class_ids;
        $classes = Group::with(['stage', 'students.stage'])->whereIn('id', $classIds)->get();

        $promotedCount = 0;
        $skippedClasses = [];

        foreach ($classes as $class) {
            // 🔹 Extract numeric grade level (e.g., "Grade 3" → 3)
            $currentGradeNum = (int) filter_var($class->stage->name, FILTER_SANITIZE_NUMBER_INT);

            if (!$currentGradeNum) {
                $skippedClasses[] = $class->name . ' (' . $class->stage->name . ')';
                continue;
            }

            // 🔹 Find next stage by incrementing number
            $nextStageName = 'Grade ' . ($currentGradeNum + 1);
            $nextStage = Stage::whereRaw('LOWER(name) = ?', [strtolower($nextStageName)])->first();

            if (!$nextStage) {
                // No higher grade — skip this class
                $skippedClasses[] = $class->name . ' (' . $class->stage->name . ')';
                continue;
            }

            // ✅ Promote class to next stage
            $class->update(['stage_id' => $nextStage->id]);
            foreach ($class->students as $student) {
                if (isset($student->stage)) {
                    $student->update(['stage_id' => $nextStage->id]);
                }
            }

            $promotedCount++;
        }

        // ✅ Build success message
        $message = "{$promotedCount} class(es) promoted successfully.";
        if (count($skippedClasses) > 0) {
            $message .= " The following class(es) were skipped (no higher grade): " . implode(', ', $skippedClasses) . ".";
        }

        return response()->json(['message' => $message]);
    }



}
