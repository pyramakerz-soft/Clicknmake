<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StudentsExport;
use App\Http\Controllers\Controller;
use App\Imports\StudentsImport;
use App\Models\Group;
use App\Models\Observation;
use App\Models\ObservationHeader;
use App\Models\ObservationHistory;
use App\Models\ObservationQuestion;
use App\Models\ObservationTemplate;
use App\Models\School;
use App\Models\Stage;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Observer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ObserverController extends Controller
{
    public function index(Request $request)
    {
        $observers = Observer::query()
            ->paginate(30)
            ->appends($request->query());
        return view('admin.observers.index', compact('observers'));
    }


    public function create()
    {
        $schools = School::all();
        return view('admin.observers.create', compact("schools"));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'username' => [
                'required',
                'regex:/^[\p{Arabic}A-Za-z][\p{Arabic}A-Za-z0-9_\s]*$/u',
                Rule::unique('observers'),
            ],
            'gender' => 'required|string',
            'password' => 'required|string|confirmed|min:6',
            'school_id' => 'required|array',
            'school_id.*' => 'exists:schools,id',
        ]);

        $observer = Observer::create([
            'name' => $request->name,
            'username' => $request->input('username'),
            'password' => Hash::make($request->password),
            'gender' => $request->input('gender'),
            'is_active' => 1
        ]);

        $observer->schools()->attach($request->input('school_id'));

        return redirect()->route('observers.index')->with('success', 'Observer created successfully.');
    }


    public function edit(string $id)
    {
        $observer = Observer::findOrFail($id);

        return view('admin.observers.edit', compact('observer'));
    }
    public function update(Request $request, string $id)
    {
        $student = Observer::findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'gender' => 'required|string',
            'password' => 'required|string|confirmed|min:6',
        ]);

        $student->update([
            'name' => $request->input('name'),
            // 'username' => $request->input('username'),
            'gender' => $request->input('gender'),
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('observers.index')->with('success', 'Observer updated successfully.');
    }

    public function destroy(string $id)
    {
        $observer = Observer::findOrFail($id);
        $observer->delete();
        return redirect()->route('observers.index')->with('success', 'Observer deleted successfully.');
    }

    public function show(string $id)
    {
        $observer = Observer::findOrFail($id);
        return view('admin.observers.show', compact('observer'));
    }
    public function addQuestions(Request $request)
    {
        $templates = ObservationTemplate::with('headers.questions')->get();

        if ($templates->isEmpty()) {
            return view('admin.observers.observation_questions', [
                'templates' => $templates,
                'data' => [],
                'selectedTemplate' => null
            ]);
        }

        // Determine selected template
        $selectedTemplateId = $request->get('template_id', $templates->first()->id);
        $selectedTemplate = $templates->firstWhere('id', $selectedTemplateId);

        $data = [];

        foreach ($selectedTemplate->headers as $header) {
            $data[$header->id] = [
                'header_id' => $header->id,
                'name' => $header->header,
                'questions' => $header->questions->map(function ($q) {
                    return [
                        'question_id' => $q->id,
                        'name' => $q->question,
                        'max_rating' => $q->max_rate
                    ];
                })->toArray()
            ];
        }

        return view('admin.observers.observation_questions', [
            'templates' => $templates,
            'data' => $data,
            'selectedTemplate' => $selectedTemplateId
        ]);
    }

    public function storeTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        ObservationTemplate::create([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Template added successfully.');
    }

    public function storeHeader(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'template_id' => 'required|exists:observation_templates,id'
        ]);


        ObservationHeader::create([
            'header' => $request->name,
            'observation_template_id' => $request->template_id
        ]);

        return redirect()->back()->with('success', 'Header added successfully.');
    }

    public function editHeader(Request $request)
    {
        $request->validate([
            'header_name' => 'required|string',
            'header_id' => 'required|exists:observation_headers,id',
        ]);

        $header = ObservationHeader::findOrFail($request->header_id);

        $header->update([
            'header' => $request->header_name,
        ]);

        return redirect()->back()->with('success', 'Header updated successfully.');
    }

    public function deleteHeader($id)
    {
        $header = ObservationHeader::findOrFail($id);
        $header->questions()->delete(); // delete questions first
        $header->delete();

        return redirect()->back()->with('success', 'Header deleted successfully.');
    }

    public function storeQuestion(Request $request)
    {
        $request->validate([
            'header_id' => 'required|exists:observation_headers,id',
            'name' => 'required|string|max:255',
            'max_rating' => 'required|integer|min:1',
        ]);

        ObservationQuestion::create([
            'observation_header_id' => $request->header_id,
            'question' => $request->name,
            'max_rate' => $request->max_rating,
        ]);

        return redirect()->back()->with('success', 'Question added successfully.');
    }

    public function editQuestion(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:observation_questions,id',
            'question_name' => 'required|string',
            'max_rating' => 'required|integer|min:1',
        ]);

        $question = ObservationQuestion::findOrFail($request->question_id);

        $question->update([
            'question' => $request->question_name,
            'max_rate' => $request->max_rating,
        ]);

        return redirect()->back()->with('success', 'Question updated successfully.');
    }

    public function deleteQuestion($id)
    {
        $question = ObservationQuestion::findOrFail($id);
        $question->delete();

        return redirect()->back()->with('success', 'Question deleted successfully.');
    }


    public function observationReport(Request $request)
    {
        $Obs = Observation::whereNull('observation_template_id')->get();
        foreach ($Obs as $ob) {
            $ob->observation_template_id = 1;
            $ob->save();
        }
        $teachers = Teacher::with('school')->whereNull('alias_id')->get();
        $schools = School::all();
        $observers = Observer::all();
        $stages = Stage::all();
        $cities = School::distinct()->whereNotNull('city')->pluck('city');
        $templates = ObservationTemplate::with('headers.questions')->get();

        if ($templates->isEmpty()) {
            return back()->with('error', 'No templates found. Please create a template first.');
        }

        // FORCE-SELECT TEMPLATE – default to first template
        $selectedTemplateId = $request->get('template_id', $templates->first()->id);
        $selectedTemplate = ObservationTemplate::with('headers.questions')->findOrFail($selectedTemplateId);
        // dd($selectedTemplate);

        $data = [];

        // Build structure for only selected template headers & questions
        foreach ($selectedTemplate->headers as $header) {
            $data[$header->id] = [
                'header_id' => $header->id,
                'name' => $header->header,
                'questions' => [],
            ];

            foreach ($header->questions as $question) {
                $data[$header->id]['questions'][$question->id] = [
                    'question_id' => $question->id,
                    'name' => $question->question,
                    'avg_rating' => 0,
                    'max_rating' => $question->max_rate,
                ];
            }
        }

        $query = Observation::query();

        if ($query->count() == 0) {
            return back()->with('error', 'No observations found');
        }

        // Apply filters
        if ($request->filled('teacher_id')) {
            $teacherIds = Teacher::where('id', $request->teacher_id)
                ->orWhere('alias_id', $request->teacher_id)->pluck('id');

            $query->whereIn('teacher_id', $teacherIds);
        }

        if ($request->filled('school_id')) {
            $schoolIds = $request->school_id;
            if (!in_array('all', $schoolIds)) {
                $query->whereIn('school_id', $schoolIds);
            }
        }

        if ($request->filled('observer_id')) {
            $query->where('observer_id', $request->observer_id);
        }

        if ($request->filled('city')) {
            $query->whereHas('school', fn($q) => $q->whereIn('city', $request->city));
        }

        if ($request->filled('stage_id')) {
            $query->where('stage_id', $request->stage_id);
        }

        if ($request->filled('lesson_segment_filter')) {
            $query->whereJsonContains('lesson_segment', $request->lesson_segment_filter);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('activity', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('activity', '<=', $request->to_date);
        }

        if ($request->has('include_comments')) {
            $query->whereNotNull('note');
        }

        $observations = $query->pluck('id');

        if ($observations->isEmpty()) {
            return back()->with('error', 'No observations found for selected filters.');
        }

        // $obsCount = $observations->count();
        $obsCount = $selectedTemplate->observations->count();

        // Get history ONLY for questions in this template
        $questionIds = $selectedTemplate->headers->flatMap->questions->pluck('id');

        $histories = ObservationHistory::whereIn('observation_id', $observations)
            ->whereIn('question_id', $questionIds)
            ->get();

        foreach ($histories as $history) {
            $headerId = ObservationQuestion::find($history->question_id)->observation_header_id;
            $data[$headerId]['questions'][$history->question_id]['avg_rating'] += $history->rate;
        }

        // Calculate averages
        foreach ($data as $headerId => $header) {
            foreach ($header['questions'] as $qId => $question) {
                $total = $question['avg_rating'];
                $avg = ($obsCount > 0) ? round($total / $obsCount, 2) : 0;

                $data[$headerId]['questions'][$qId]['avg_rating'] = $avg;
            }
        }

        return view('admin.observers.observation_report_admin', compact(
            'stages',
            'cities',
            'teachers',
            'observers',
            'schools',
            'templates',
            'selectedTemplateId',
            'data'
        ));
    }
}
