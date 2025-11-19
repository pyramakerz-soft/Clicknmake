<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Lesson;
use App\Models\LessonResource;
use App\Models\Material;
use App\Models\MaterialResource;
use App\Models\School;
use App\Models\Stage;
use App\Models\TeacherResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Str;
use ZipArchive;
use Imagick;

class MaterialResourceController extends Controller
{
    public function index(Request $request)
    {
        $query = MaterialResource::query();
        if ($request->filled('theme_id')) {
            $query->where('material_id', $request->theme_id);
        }
        if ($request->filled('stage_id')) {
            $query->whereHas('material.stage', function ($q) use ($request) {
                $q->where('id', $request->stage_id);
            });
        }
        $resources = $query->get();
        $themes = Material::all();
        $stages = Stage::all();

        return view('admin.theme_resources.index', compact('stages', 'themes', 'resources'));
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

        // $themes = Material::all();
        $stages = Stage::all();


        return view('admin.theme_resources.create', compact('stages'));
    }
    public function getThemesByStage($stageId)
    {
        $themes = Material::where('stage_id', $stageId)
            ->select('id', 'title')
            ->get();

        return response()->json($themes);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'theme_id' => 'required|exists:materials,id',
            'file_path' => 'required|file|max:204800',
            'title' => 'required|string|max:255',
        ]);

        $file = $request->file('file_path');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = 'material_resources/' . $fileName;
        $fileType = $file->getClientOriginalExtension();

        $file->move(public_path('material_resources'), $fileName);

        $resource = MaterialResource::create([
            'material_id' => $request->theme_id,
            'title' => $request->title,
            'path' => $filePath,
            'type' => $fileType,
        ]);

        // Decode selected schools from request
        $selectedSchools = json_decode($request->selected_schools ?? '[]', true);

        // 🟢 If no schools were selected, assign the resource to ALL schools by default
        if (empty($selectedSchools)) {
            $selectedSchools = School::pluck('id')->toArray();
        }

        // Sync school visibility
        $resource->schools()->sync($selectedSchools);

        return redirect()->back()->with('success', 'Material resource uploaded successfully.');
    }


    public function getSchoolsByTheme($themeId)
    {
        $schools = School::whereHas('materials', function ($query) use ($themeId) {
            $query->where('materials.id', $themeId);
        })->get();

        return response()->json($schools);
    }

    public function download(Request $request)
    {
        $resources = MaterialResource::where('material_id', $request->download_theme_id)->get();

        if ($resources->isEmpty()) {
            return redirect()->back()->with('error', 'No resources available for this theme.');
        }
        $material = Material::query()->where('id', $request->download_theme_id)->first();

        $zipFileName = $material->title . '_resources.zip';
        $zipPath = public_path('material_resources/' . $zipFileName);

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
    // public function downloadThemeResources(Request $request)
    // {
    //     $themeId = $request->download_theme_id;

    //     $resources = MaterialResource::where('material_id', $themeId)->get();

    //     if ($resources->isEmpty()) {
    //         return redirect()->back()->with('error', 'No resources available for this theme.');
    //     }

    //     $material = Material::find($themeId);
    //     $zipFileName = $material->title . '_resources.zip';
    //     $zipPath = public_path('material_resources/' . $zipFileName);

    //     $zip = new \ZipArchive;
    //     if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
    //         foreach ($resources as $resource) {
    //             $filePath = public_path($resource->path);
    //             if (\File::exists($filePath)) {
    //                 $fileNameInZip = $resource->title . '.' . $resource->type;
    //                 $zip->addFile($filePath, $fileNameInZip);
    //             }
    //         }
    //         $zip->close();
    //     }

    //     return response()->download($zipPath)->deleteFileAfterSend(true);
    // }


    public function downloadResources(Request $request)
    {
        if ($request->resource_type === 'lesson') {
            return $this->downloadLessonResources($request->download_lesson_id);
        }

        if ($request->resource_type === 'theme') {
            return $this->downloadThemeResources($request->download_theme_id);
        }

        return back()->with('error', 'Invalid resource selection.');
    }


    public function downloadLessonResources($lessonId)
    {
        $resources = LessonResource::where('lesson_id', $lessonId)->get();

        if ($resources->isEmpty()) {
            return redirect()->back()->with('error', 'No resources available for this lesson.');
        }

        $lesson = Lesson::find($lessonId);
        $zipFileName = Str::slug($lesson->title) . '_lesson_resources.zip';
        $zipPath = public_path('lesson_resources/' . $zipFileName);

        if (\File::exists($zipPath)) {
            \File::delete($zipPath);
        }

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            foreach ($resources as $resource) {
                $filePath = public_path($resource->path);
                if (\File::exists($filePath)) {
                    $fileNameInZip = $resource->title . '.' . $resource->type;
                    $zip->addFile($filePath, $fileNameInZip);
                }
            }
            $zip->close();
        }

        if (!\File::exists($zipPath)) {
            return redirect()->back()->with('error', 'Failed to generate the ZIP file. Please try again.');
        }

        try {
            return response()->download($zipPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error downloading file: ' . $e->getMessage());
        }
    }


    public function downloadThemeResources($themeId)
    {
        $resources = MaterialResource::where('material_id', $themeId)->get();

        if ($resources->isEmpty()) {
            return redirect()->back()->with('error', 'No resources available for this theme.');
        }

        $material = Material::find($themeId);
        if (!$material) {
            return redirect()->back()->with('error', 'Material not found.');
        }

        $safeTitle = Str::slug($material->title, '_');
        $zipFileName = $safeTitle . '_theme_resources.zip';
        $zipPath = public_path('material_resources/' . $zipFileName);

        if (File::exists($zipPath)) {
            File::delete($zipPath);
        }

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            foreach ($resources as $resource) {
                $filePath = public_path($resource->path);
                if (File::exists($filePath)) {
                    $fileNameInZip = $resource->title . '.' . $resource->type;
                    $zip->addFile($filePath, $fileNameInZip);
                }
            }
            $zip->close();
        }

        if (!File::exists($zipPath)) {
            return redirect()->back()->with('error', 'Failed to create ZIP file. Please try again.');
        }

        try {
            return response()->download($zipPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error during download: ' . $e->getMessage());
        }
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
        $resource = MaterialResource::with('material.stage', 'schools')->findOrFail($id);
        $themes = Material::all();
        $schools = School::all();

        return view('admin.theme_resources.edit', compact('resource', 'themes', 'schools'));
    }


    public function update(Request $request, $id)
    {
        $resource = MaterialResource::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'theme_id' => 'required|exists:materials,id',
            'selected_schools' => 'nullable|string'
        ]);

        // Update basic info
        $resource->update([
            'title' => $request->title,
            'material_id' => $request->theme_id,
        ]);

        // Update school visibility
        $selectedSchools = json_decode($request->selected_schools, true) ?? [];

        // If none are selected, default to all
        if (empty($selectedSchools)) {
            $selectedSchools = School::pluck('id')->toArray();
        }

        $resource->schools()->sync($selectedSchools);

        return redirect()->route('theme_resource.index')->with('success', 'Resource updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $resource = MaterialResource::findOrFail($id);

        $filePath = public_path($resource->path);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        $resource->delete();

        return redirect()->back()->with('success', 'Resource deleted successfully.');
    }
    public function viewResource($id, $type)
    {
        if ($type === "teacher") {
            $resource = TeacherResource::findOrFail($id);
            $file = $resource->file_path;
        } elseif ($type === "lesson") {
            $resource = LessonResource::findOrFail($id);
            $file = $resource->path;
        } elseif ($type === "theme") {
            $resource = MaterialResource::findOrFail($id);
            $file = $resource->path;
        }

        $fullPath = public_path($file);
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        $converted = null;
        $convertedType = null;

        // Detect platform (Windows / Linux)
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        // LibreOffice path
        $soffice = $isWindows
            ? '"C:\\Program Files\\LibreOffice\\program\\soffice.com"'
            : 'libreoffice';

        if (!$isWindows) {
            putenv("HOME=/tmp");
            putenv("TMPDIR=" . storage_path("temp"));
        }

        // ============================
        // PPT/PPTX → SLIDES (PNG/JPG)
        // ============================
        if (in_array($ext, ['ppt', 'pptx'])) {
            $outputDir = storage_path('app/public/converted/slides/' . pathinfo($file, PATHINFO_FILENAME));
            if (!is_dir($outputDir)) mkdir($outputDir, 0777, true);

            $existingSlides = array_merge(glob($outputDir . '/*.png'), glob($outputDir . '/*.jpg'));

            if (count($existingSlides) === 0) {
                $format = $isWindows ? 'jpg' : 'png';
                $cmd = $soffice . " --headless --convert-to $format --outdir \"$outputDir\" \"$fullPath\"";
                exec($cmd . " 2>&1", $output, $result);
                if ($result !== 0) dd("LibreOffice conversion failed.", $cmd, $output);

                $existingSlides = array_merge(glob($outputDir . '/*.png'), glob($outputDir . '/*.jpg'));
            }

            $slides = [];
            foreach ($existingSlides as $img) {
                $slides[] = str_replace(storage_path('app/public/'), 'storage/', $img);
            }

            $converted = $slides;
            $convertedType = 'slides';
        }

        // ============================
        // PDF → IMAGES
        // ============================
        if ($ext === 'pdf') {
            $outputDir = storage_path('app/public/converted/pdf_images/' . pathinfo($file, PATHINFO_FILENAME));
            if (!is_dir($outputDir)) mkdir($outputDir, 0777, true);

            $existing = glob($outputDir . '/*.png');

            if (count($existing) === 0) {
                try {
                    $imagick = new Imagick();
                    $imagick->setResolution(150, 150);
                    $imagick->readImage($fullPath);

                    foreach ($imagick as $index => $page) {
                        $page->setImageFormat("png");
                        $page->writeImage($outputDir . "/page_" . ($index + 1) . ".png");
                    }

                    $imagick->clear();
                    $imagick->destroy();
                } catch (\Exception $e) {
                    dd("PDF → Image conversion failed", $e->getMessage());
                }

                $existing = glob($outputDir . '/*.png');
            }

            $pages = [];
            foreach ($existing as $img) {
                $pages[] = str_replace(storage_path('app/public/'), 'storage/', $img);
            }

            $converted = $pages;
            $convertedType = 'slides';
        }

        // ============================
        // DOC/DOCX → PDF → IMAGES
        // ============================
        if (in_array($ext, ['doc', 'docx'])) {
            $tempPdfDir = storage_path('app/public/converted/docx_pdf/');
            if (!is_dir($tempPdfDir)) mkdir($tempPdfDir, 0777, true);

            $pdfPath = $tempPdfDir . pathinfo($file, PATHINFO_FILENAME) . ".pdf";

            // Convert DOCX → PDF via LibreOffice
            if (!file_exists($pdfPath)) {
                $cmd = $soffice . " --headless --convert-to pdf --outdir \"$tempPdfDir\" \"$fullPath\"";
                exec($cmd . " 2>&1", $output, $result);
                if ($result !== 0) dd("DOCX → PDF conversion failed", $cmd, $output);
            }

            // Convert PDF → IMAGES
            $outputDir = storage_path('app/public/converted/docx_images/' . pathinfo($file, PATHINFO_FILENAME));
            if (!is_dir($outputDir)) mkdir($outputDir, 0777, true);

            $existing = glob($outputDir . '/*.png');

            if (count($existing) === 0) {
                try {
                    $imagick = new Imagick();
                    $imagick->setResolution(150, 150);
                    $imagick->readImage($pdfPath);

                    foreach ($imagick as $index => $page) {
                        $page->setImageFormat("png");
                        $page->writeImage($outputDir . "/page_" . ($index + 1) . ".png");
                    }

                    $imagick->clear();
                    $imagick->destroy();
                } catch (\Exception $e) {
                    dd("DOCX PDF → Image conversion failed", $e->getMessage());
                }

                $existing = glob($outputDir . '/*.png');
            }

            $pages = [];
            foreach ($existing as $img) {
                $pages[] = str_replace(storage_path('app/public/'), 'storage/', $img);
            }

            $converted = $pages;
            $convertedType = 'slides';
        }

        // ============================
        // IMAGES + VIDEO (direct)
        // ============================
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'webm'])) {
            return view('admin.resources.view', compact('resource', 'file', 'ext', 'converted', 'convertedType'));
        }

        return view('admin.resources.view', compact('resource', 'file', 'ext', 'converted', 'convertedType'));
    }
}
