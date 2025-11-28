<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\School;
use App\Models\Stage;
use App\Models\TeacherResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class TeacherResourceController extends Controller
{

    public function index()
    {
        $resources = TeacherResource::with('stage', 'school')->get();
        return view('admin.teacher_resources.index', compact('resources'));
    }

    public function create()
    {
        $stages = Stage::all();
        $schools = School::all();
        return view('admin.teacher_resources.create', compact('stages', 'schools'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif|max:2048',
            'file_path' => 'nullable|mimes:pdf',
            'stage_id' => 'required|exists:stages,id',
            'school_id' => 'required|exists:schools,id',
            'type' => 'required|in:pdf,ebook',
            
        ]);

        $imagePath = $request->file('image') ? $request->file('image')->store('teacher_resources', 'public') : null;
        $filePath = $request->file('file_path')->store('teacher_resources', 'public');

        TeacherResource::create([
            'name' => $request->name,
            'image' => $imagePath,
            'file_path' => $filePath,
            'stage_id' => $request->stage_id,
            'school_id' => $request->school_id,
            'type' => $request->type,
        ]);

        return redirect()->route('teacher_resources.index')->with('success', 'Resource added successfully.');
    }

    public function destroy($id)
    {
        $resource = TeacherResource::findOrFail($id);
        $resource->delete();

        return redirect()->route('teacher_resources.index')->with('success', 'Resource deleted successfully.');
    }

    public function edit($id)
    {
        $resource = TeacherResource::findOrFail($id);

        // Admin sees all schools, stages, and materials
        $stages = Stage::all();
        $materials = Material::all();

        return view('admin.teacher_resources.edit', compact('resource', 'stages', 'materials'));
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

        $resource = TeacherResource::findOrFail($id);

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

        return redirect()->route('teacher_resources.index')
            ->with('success', 'Resource updated successfully!');
    }

}
