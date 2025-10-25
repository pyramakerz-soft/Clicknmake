<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LessonResource;
use App\Models\Lesson;
use App\Models\Material;
use App\Models\School;
use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use ZipArchive;
use Illuminate\Support\Str;

class LessonResourceController extends Controller
{
    public function index(Request $request)
    {

        $query = LessonResource::query();
        if ($request->filled('lesson_id')) {
            $query->where('lesson_id', $request->lesson_id);
        }
        if ($request->filled('stage_id')) {
            $query->whereHas('lesson.chapter.unit.material.stage', function ($q) use ($request) {
                $q->where('id', $request->stage_id);
            });
        }
        if ($request->filled('theme_id')) {
            $query->whereHas('lesson.chapter.unit.material', function ($q) use ($request) {
                $q->where('id', $request->theme_id);
            });
        }
        $resources = $query->get();
        $themes = Material::all();
        $stages = Stage::all();

        $lessons = Lesson::query()
            ->with('chapter.unit.material')
            ->get()
            ->sortBy([
                fn($lesson) => $lesson->chapter?->unit?->material?->title ?? 0,
                fn($lesson) => $lesson->chapter?->unit?->title ?? 0,
                fn($lesson) => $lesson->chapter?->title ?? 0,
                fn($lesson) => strtolower($lesson->title),
            ]);

        return view('admin.lesson_resources.index', compact('stages', 'themes', 'resources', 'lessons'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // $lessons = Lesson::query()->with('chapter.unit.material')->get();
        // $lessons = Lesson::query()
        //     ->with('chapter.unit.material')
        //     ->get()
        //     ->sortBy(fn($lesson) => $lesson->chapter?->unit?->material?->id ?? '');

        $lessons = Lesson::query()
            ->with('chapter.unit.material')
            ->get()
            ->sortBy([
                fn($lesson) => $lesson->chapter?->unit?->material?->title ?? 0,
                fn($lesson) => $lesson->chapter?->unit?->title ?? 0,
                fn($lesson) => $lesson->chapter?->title ?? 0,
                fn($lesson) => strtolower($lesson->title),
            ]);



        return view('admin.lesson_resources.create', compact('lessons'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'file_path' => 'required|file|max:204800',
            'title' => 'required|string|max:255',
        ]);
        

        $file = $request->file('file_path');
        $extension = $file->getClientOriginalExtension();
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $basePath = public_path('lesson_resources');

        if (!File::exists($basePath)) {
            File::makeDirectory($basePath, 0777, true, true);
        }

        if (strtolower($extension) === 'zip') {
            $zip = new ZipArchive;
            $zipPath = $file->getRealPath();

            $uniqueFolderName = Str::slug($originalName . '_' . time());
            $extractPath = $basePath . '/' . $uniqueFolderName;

            if (!File::exists($extractPath)) {
                File::makeDirectory($extractPath, 0777, true, true);
            }

            if ($zip->open($zipPath) === TRUE) {
                $zip->extractTo($extractPath);
                $zip->close();

                $lessonResource = LessonResource::create([
                    'lesson_id' => $request->lesson_id,
                    'title' => $request->title,
                    'path' => 'lesson_resources/' . $uniqueFolderName,
                    'type' => 'zip',
                ]);
                if ($request->filled('selected_schools')) {
                    $schoolIds = json_decode($request->selected_schools, true);
                    // Example: store pivot table relation
                    foreach ($schoolIds as $schoolId) {
                        DB::table('lesson_resource_school')->insert([
                            'lesson_resource_id' => $lessonResource->id,
                            'school_id' => $schoolId,
                        ]);
                    }
                }

                return redirect()->back()->with('success', 'ZIP file extracted and lesson resource saved successfully.');
            } else {
                return back()->withErrors(['file_path' => 'Failed to open the ZIP file.']);
            }
        } else {
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = 'lesson_resources/' . $fileName;

            $file->move($basePath, $fileName);

            $lessonResource = LessonResource::create([
                'lesson_id' => $request->lesson_id,
                'title' => $request->title,
                'path' => $filePath,
                'type' => $extension,
            ]);

            if ($request->filled('selected_schools')) {
                $schoolIds = json_decode($request->selected_schools, true);
                // Example: store pivot table relation
                foreach ($schoolIds as $schoolId) {
                    DB::table('lesson_resource_school')->insert([
                        'lesson_resource_id' => $lessonResource->id,
                        'school_id' => $schoolId,
                    ]);
                }
            }

            return redirect()->back()->with('success', 'Lesson resource uploaded successfully.');
        }
    }
    public function getSchoolsByLesson($lessonId)
    {
        $lesson = Lesson::with('chapter.unit.material.stage')->findOrFail($lessonId);
        $gradeId = $lesson->chapter->unit->material->stage_id ?? null;
        $schools = School::whereHas('stages', function ($query) use ($gradeId) {
            $query->where('stages.id', $gradeId);
        })->get();
        
        return response()->json($schools);
    }
    public function download(Request $request)
    {
        $resources = LessonResource::where('lesson_id', $request->download_lesson_id)->get();

        if ($resources->isEmpty()) {
            return redirect()->back()->with('error', 'No resources available for this lesson.');
        }
        $lesson = Lesson::query()->where('id', $request->download_lesson_id)
            ->with('chapter.unit.material')
            ->first();

        $zipFileName = $lesson->chapter->material->title . '_' . $lesson->chapter->unit->title . '_' . $lesson->chapter->title . '_' . $lesson->title . '_resources.zip';
        $zipPath = public_path('lesson_resources/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($resources as $resource) {
                $filePath = public_path($resource->path);
                if (File::exists($filePath)) {
                    $zipFileName = $resource->title . '.' . $resource->type;
                    $zip->addFile($filePath, $zipFileName);
                }
            }
            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
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
    public function edit($id)
    {
        $resource = LessonResource::with('lesson.chapter.unit.material', 'schools')->findOrFail($id);
        $schools = School::all();

        return view('admin.lesson_resources.edit', compact('resource', 'schools'));
    }

    public function update(Request $request, $id)
    {
        $resource = LessonResource::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $resource->update([
            'title' => $request->title,
        ]);

        if ($request->filled('selected_schools')) {
            $schoolIds = json_decode($request->selected_schools, true);
            $resource->schools()->sync($schoolIds);
        }

        return redirect()->route('lesson_resource.index')->with('success', 'Lesson resource updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $resource = LessonResource::findOrFail($id);

        $filePath = public_path($resource->path);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        $resource->delete();

        return redirect()->route('lesson_resource.index')->with('success', 'Resource deleted successfully.');
    }
}
