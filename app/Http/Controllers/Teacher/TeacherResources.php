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

        // Get teacher's stages
        $stages = Stage::whereHas('teachers', function ($query) use ($user) {
            $query->where('teacher_id', $user->id);
        })->get();

        // Resources filtered by teacher & grade
        $resourcesQuery = TeacherResource::with(['stage', 'material'])
            ->where('teacher_id', $user->id);

        if ($selectedGrade) {
            $resourcesQuery->where('stage_id', $selectedGrade);
        }

        $resources = $resourcesQuery->get();

        // Group by Stage → Material (Theme)
        $groupedResources = $resources
            ->groupBy(fn($res) => $res->stage?->name ?? 'Other')
            ->map(
                fn($stageGroup) =>
                $stageGroup->groupBy(fn($res) => $res->material?->title ?? 'No Theme')
            );

        return view('pages.teacher.resources.index', compact('stages', 'selectedGrade', 'groupedResources'));
    }

    public function adminResources()
    {
        $teacher = Auth::guard('teacher')->user();

        $stageIds = $teacher->stages->pluck('id');
        $school = School::find($teacher->school_id);

        // Filter materials by both school and stage visibility
        $MaterialIds = $school->materials->whereIn('stage_id', $stageIds)->pluck('id');

        // Filter lessons visible to this school
        $lessonIds = Lesson::whereHas('chapter.unit.material', function ($query) use ($MaterialIds) {
            $query->whereIn('id', $MaterialIds);
        })->pluck('id');

        // ✅ Lesson Resources (filtered by school visibility)
        $groupedLessons = LessonResource::with('lesson.chapter.unit.material')
            ->whereIn('lesson_id', $lessonIds)
            ->whereHas('schools', function ($query) use ($school) {
                $query->where('schools.id', $school->id);
            })
            ->get()
            ->groupBy('lesson_id');

        $themeStages = Material::whereIn('id', $MaterialIds)
            ->with(['resources' => function ($query) use ($school) {
                $query->whereHas('schools', function ($q) use ($school) {
                    $q->where('schools.id', $school->id);
                });
            }, 'stage'])
            ->get()
            ->filter(fn($material) => $material->resources->isNotEmpty())
            ->groupBy(fn($material) => $material->stage->name);

        return view('pages.teacher.resources.admin_resources', compact('groupedLessons', 'themeStages'));
    }

    public function create()
    {
        $teacher = Auth::guard('teacher')->user();

        // Get teacher assigned stages
        $stageIds = $teacher->stages->pluck('id');

        // Get teacher's school
        $school = School::find($teacher->school_id);

        // Filter materials by school & stage
        $materials = $school->materials->whereIn('stage_id', $stageIds);

        // Stages for the dropdown
        $stages = Stage::whereIn('id', $stageIds)->get();

        return view('pages.teacher.resources.create', compact('stages', 'materials'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'resources' => 'required|array|min:1',
            'resources.*.name' => 'required|string|max:255',
            'resources.*.description' => 'nullable|string',
            'resources.*.stage_id' => 'required|exists:stages,id',
            'resources.*.material_id' => 'required|exists:materials,id',
            'resources.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'resources.*.file_path' => 'nullable|file|mimes:pdf,ppt,pptx,doc,docx,zip,mp4,jpg,jpeg,png|max:51200',
            'resources.*.video_url' => 'nullable|url',
            'resources.*.date' => 'required|date',
        ]);

        $user = Auth::guard('teacher')->user();

        foreach ($request->resources as $index => $res) {

            if (isset($res['file_path']) && isset($res['video_url'])) {
                return back()->withErrors(['resources.' . $index . '.file_path' => 'Upload file OR URL, not both.']);
            }

            $filePath = null;
            $fileType = null;

            if (isset($res['file_path'])) {
                $file = $res['file_path'];
                $extension = strtolower($file->getClientOriginalExtension());

                if ($extension === 'zip') {
                    $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extractPath = public_path('resources/ebooks/' . $fileName);

                    if (!file_exists($extractPath)) mkdir($extractPath, 0777, true);

                    $zip = new ZipArchive;
                    if ($zip->open($file->getRealPath()) === true) {
                        $zip->extractTo($extractPath);
                        $zip->close();
                        $filePath = 'resources/ebooks/' . $fileName;
                        $fileType = 'ebook';
                    }
                } else {
                    $filePath = $file->store('resources', 'public');
                    $fileType = $extension;
                }
            }

            $imagePath = isset($res['image'])
                ? $res['image']->store('resources/images', 'public')
                : null;

            TeacherResource::create([
                'teacher_id' => $user->id,
                'school_id' => $user->school_id,
                'stage_id' => $res['stage_id'],
                'material_id' => $res['material_id'],
                'name' => $res['name'],
                'date' => $res['date'],
                'description' => $res['description'] ?? null,
                'image' => $imagePath,
                'file_path' => $filePath,
                'video_url' => $res['video_url'] ?? null,
                'type' => $fileType ?? ($res['video_url'] ? 'url' : null),
            ]);
        }

        return redirect()->route('teacher.resources.index')->with('success', 'Resources uploaded successfully!');
    }

   public function edit($id)
    {
        $user = Auth::guard('teacher')->user();

        // Ensure teacher owns this resource
        $resource = TeacherResource::where('teacher_id', $user->id)->findOrFail($id);

        // Get teacher assigned stages
        $stageIds = $user->stages->pluck('id');

        // Get teacher's school
        $school = School::find($user->school_id);

        // Filter materials by school & stage
        $materials = $school->materials->whereIn('stage_id', $stageIds);

        // Stages for the dropdown
        $stages = Stage::whereIn('id', $stageIds)->get();

        return view('pages.teacher.resources.edit', compact('resource', 'stages', 'materials'));
    }

   public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'stage_id' => 'required|exists:stages,id',
            'material_id' => 'required|exists:materials,id',
            'date' => 'required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'file_path' => 'nullable|file|mimes:pdf,ppt,pptx,doc,docx,zip,mp4,jpg,jpeg,png|max:51200',
            'video_url' => 'nullable|url',
        ]);

        $user = Auth::guard('teacher')->user();
        $resource = TeacherResource::where('teacher_id', $user->id)->findOrFail($id);

        $imagePath = $resource->image;
        $filePath = $resource->file_path;
        $fileType = $resource->type;
        $videoUrl = $resource->video_url;

        // ✅ IMAGE UPDATE (Independent)
        if ($request->hasFile('image')) {
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('resources/images', 'public');
        }

        if ($request->remove_image ?? false) {
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = null;
        }

        // ✅ FILE UPDATE (Mutually exclusive with video)
        if ($request->hasFile('file_path')) {
            // Remove old file if exists
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            $file = $request->file('file_path');
            $ext = strtolower($file->getClientOriginalExtension());

            if ($ext === 'zip') {
                $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extractPath = public_path('resources/ebooks/' . $fileName);

                if (!file_exists($extractPath)) mkdir($extractPath, 0777, true);

                $zip = new ZipArchive;
                if ($zip->open($file->getRealPath()) === true) {
                    $zip->extractTo($extractPath);
                    $zip->close();
                    $filePath = 'resources/ebooks/' . $fileName;
                    $fileType = 'ebook';
                }
            } else {
                $filePath = $file->store('resources', 'public');
                $fileType = $ext;
            }

            // remove video if file is uploaded
            $videoUrl = null;
        }

        if ($request->remove_file ?? false) {
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = null;
            $fileType = null;
        }

        // ✅ VIDEO URL UPDATE (Mutually exclusive with file)
        if ($request->video_url) {
            // remove existing file if video provided
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = null;
            $fileType = 'url';
            $videoUrl = $request->video_url;
        }

        if ($request->remove_video ?? false) {
            $videoUrl = null;
            if (!$filePath) {
                $fileType = null;
            }
        }

        $resource->update([
            'stage_id' => $request->stage_id,
            'material_id' => $request->material_id,
            'name' => $request->name,
            'description' => $request->description,
            'date' => $request->date,
            'image' => $imagePath,
            'file_path' => $filePath,
            'video_url' => $videoUrl,
            'type' => $fileType,
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
