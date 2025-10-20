<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonResource;
use App\Models\Material;
use App\Models\MaterialResource;
use App\Models\School;
use App\Models\Stage;
use App\Models\TeacherResource;
use Auth;
use Illuminate\Http\Request;
use Str;
use ZipArchive;

class TeacherResources extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::guard('teacher')->user();

        $selectedGrade = $request->get('grade');

        $stages = Stage::whereHas('teachers', function ($query) use ($user) {
            $query->where('teacher_id', $user->id);
        })->get();

        $resources = TeacherResource::where('teacher_id', $user->id);

        if ($selectedGrade) {
            $resources->where('stage_id', $selectedGrade);
        }

        $resources = $resources->get();

        $lessons = Lesson::query()
            ->whereHas('chapter.unit.material', function ($query) use ($stages) {
                $query->whereIn('stage_id', $stages->pluck('id'));
            })
            ->with('chapter.unit.material')
            ->get()
            ->sortBy([
                fn($lesson) => $lesson->chapter?->unit?->material?->title ?? 0,
                fn($lesson) => $lesson->chapter?->unit?->title ?? 0,
                fn($lesson) => $lesson->chapter?->title ?? 0,
                fn($lesson) => strtolower($lesson->title),
            ]);

        $themes = Material::whereIn('stage_id', $stages->pluck('id'))->get();

        return view('pages.teacher.resources.index', compact('resources', 'stages', 'selectedGrade', 'lessons', 'themes'));
    }
    public function adminResources()
    {
        $teacher = Auth::guard('teacher')->user();

        $stageIds = $teacher->stages->pluck('id');

        $school = School::find($teacher->school_id);

        // $MaterialIds = Material::whereIn('stage_id', $stageIds)->pluck('id');
        $MaterialIds = $school->materials->whereIn('stage_id', $stageIds)->pluck('id');

        $groupedLessons = LessonResource::with('lesson')
            ->get()
            ->groupBy('lesson_id');

        $themeStages = Material::whereIn('id', $MaterialIds)
            ->with(['resources', 'stage'])
            ->get()
            // filter out materials with no resources
            ->filter(fn($material) => $material->resources->isNotEmpty())
            // then group by stage name
            ->groupBy(fn($material) => $material->stage->name);

        return view('pages.teacher.resources.admin_resources', compact('groupedLessons', 'themeStages'));
    }
    public function create()
    {
        $user = Auth::guard('teacher')->user();

        $stages = Stage::whereHas('teachers', function ($query) use ($user) {
            $query->where('teacher_id', $user->id);
        })->get();

        return view('pages.teacher.resources.create', compact('stages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'file_path' => 'nullable|file|mimes:pdf,ppt,pptx,zip,mp4|max:51200',
            'video_url' => 'nullable|url',
            'stage_id' => 'required|exists:stages,id',
        ], [
            'file_path.required_without' => 'Please upload a file or provide a video URL.',
            'video_url.required_without' => 'Please upload a file or provide a video URL.',
        ]);

        $request->validate([
            'file_path' => 'required_without:video_url',
            'video_url' => 'required_without:file_path',
        ]);

        $user = Auth::guard('teacher')->user();
        $filePath = null;
        $fileType = null;

        if ($request->hasFile('file_path')) {
            $file = $request->file('file_path');
            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension === 'apk') {
                return back()->withErrors(['file_path' => 'APK files are not allowed.']);
            }
            if ($extension === 'zip') {
                $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extractPath = public_path('resources/ebooks/' . $fileName);

                if (!file_exists($extractPath)) {
                    mkdir($extractPath, 0777, true);
                }

                $zip = new ZipArchive;
                if ($zip->open($file->getRealPath()) === true) {
                    $zip->extractTo($extractPath);
                    $zip->close();
                    $filePath = 'resources/ebooks/' . $fileName;
                    $fileType = 'ebook';
                } else {
                    return back()->withErrors(['file_path' => 'Failed to extract the ZIP file.']);
                }
            } else {
                $stored = $file->store('resources', 'public');
                $filePath = $stored;
                $fileType = $extension;
            }
        }

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('resources/images', 'public')
            : null;

        TeacherResource::create([
            'teacher_id' => $user->id,
            'school_id' => $user->school_id,
            'stage_id' => $request->stage_id,
            'name' => $request->name,
            'description' => $request->description,
            'image' => $imagePath,
            'file_path' => $filePath,
            'video_url' => $request->video_url,
            'type' => $fileType ?? 'url',
        ]);

        return redirect()->route('teacher.resources.index')->with('success', 'Resource uploaded successfully!');
    }
    public function edit($id)
    {
        $user = Auth::guard('teacher')->user();
        $resource = TeacherResource::where('teacher_id', $user->id)->findOrFail($id);
        $stages = Stage::whereHas('teachers', function ($query) use ($user) {
            $query->where('teacher_id', $user->id);
        })->get();

        return view('pages.teacher.resources.edit', compact('resource', 'stages'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'file_path' => 'nullable|file|mimes:pdf',
            'stage_id' => 'required|exists:stages,id',
        ]);

        $user = Auth::guard('teacher')->user();
        $resource = TeacherResource::where('teacher_id', $user->id)->findOrFail($id);

        $filePath = $resource->file_path;
        if ($request->hasFile('file_path')) {

            $filePath = $request->file('file_path')->store('resources', 'public');
        }


        $imagePath = $resource->image;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('resources/images', 'public');
        }

        $resource->update([
            'stage_id' => $request->stage_id,
            'name' => $request->name,
            'description' => $request->description,
            'image' => $imagePath,
            'file_path' => $filePath,
            'type' => 'pdf',
        ]);

        return redirect()->route('teacher.resources.index')->with('success', 'Resource updated successfully!');
    }


    public function destroy($id)
    {
        $user = Auth::guard('teacher')->user();
        $resource = TeacherResource::where('teacher_id', $user->id)->findOrFail($id);
        $resource->delete();

        return redirect()->route('teacher.resources.index')->with('success', 'Resource deleted successfully!');
    }
}
