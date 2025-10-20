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
            'username' => [
                'required',
                'regex:/^[\p{Arabic}A-Za-z][\p{Arabic}A-Za-z0-9_ ]*$/u',
                Rule::unique('observers')->ignore($student->id),
            ],
            'gender' => 'required|string',
            'password' => 'required|string|confirmed|min:6',
        ]);

        $student->update([
            'name' => $request->input('name'),
            'username' => $request->input('username'),
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
        $headers = ObservationHeader::all();
        $observers = Observer::query()
            ->paginate(30)
            ->appends($request->query());


        foreach ($headers as $header) {
            if (!isset($data[$header->id])) {
                $data[$header->id] = [
                    'header_id' => $header->id,
                    'name' => $header->header,
                    'questions' => [],
                ];
            }
            $questions = ObservationQuestion::where('observation_header_id', $header->id)->get();
            $headerQuestions = [];
            foreach ($questions as $question) {
                if (!isset($headerQuestions[$question->id])) {
                    $headerQuestions[$question->id] = [
                        'question_id' => $question->id,
                        'name' => $question->question,
                        'avg_rating' => 0,
                        'max_rating' => $question->max_rate,
                    ];
                }
            }
            $data[$header->id]['questions'] = $headerQuestions;
        }
        if (isset($data)) {
            return view('admin.observers.observation_questions', compact('observers', 'data'));
        } else {

            return view('admin.observers.observation_questions', compact('observers'));
        }
    }
    public function deleteQuestion($id)
    {
        $question = ObservationQuestion::findOrFail($id);
        $question->delete();

        return redirect()->back()->with('success', 'Question deleted successfully.');
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
    public function storeHeader(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);
        $obs = ObservationHeader::create([
            'header' => $request->name,
        ]);
        return redirect()->route('observers.addQuestions')->with('success', 'Header added successfully.');
    }
    public function editHeader(Request $request)
    {
        $request->validate([
            'header_name' => 'required|string',
        ]);
        $obs = ObservationHeader::findOrFail($request->header_id);

        $obs->update([
            'header' => $request->header_name,
        ]);

        return redirect()->route('observers.addQuestions')->with('success', 'Header Updated successfully.');
    }
    public function editQuestion(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'question_name' => 'required|string',
            'max_rating' => 'required|integer|min:1',
        ]);
        $obs = ObservationQuestion::findOrFail($request->question_id);

        $obs->update([
            'question' => $request->question_name,
            'max_rate' => $request->max_rating,
        ]);

        return redirect()->route('observers.addQuestions')->with('success', 'Question Updated successfully.');
    }

    public function deleteHeader($id)
    {
        $header = ObservationHeader::findOrFail($id);
        $header->delete();

        return redirect()->back()->with('success', 'Header deleted successfully.');
    }

    public function observationReport(Request $request)
    {
        // $observer = Auth::guard('observer')->user();
        $teachers = Teacher::with('school')->whereNull('alias_id')->get();
        $schools = School::all();
        $observers = Observer::all();
        $stages = Stage::all();
        $headers = ObservationHeader::all();

        foreach ($headers as $header) {
            if (!isset($data[$header->id])) {
                $data[$header->id] = [
                    'header_id' => $header->id,
                    'name' => $header->header,
                    'questions' => [],
                ];
            }
            $questions = ObservationQuestion::where('observation_header_id', $header->id)->get();
            $headerQuestions = [];
            foreach ($questions as $question) {
                if (!isset($headerQuestions[$question->id])) {
                    $headerQuestions[$question->id] = [
                        'question_id' => $question->id,
                        'name' => $question->question,
                        'avg_rating' => 0,
                        'max_rating' => $question->max_rate,
                    ];
                }
            }
            $data[$header->id]['questions'] = $headerQuestions;
        }
        // dd($data);

        $query = Observation::query();
        if ($query->get()->count() == 0) {
            return redirect()->back()->with('error', 'No observations found for this observer');
        }
        if ($request->filled('teacher_id')) {
            $teacherIds = Teacher::where('id', $request->teacher_id)
                ->orWhere('alias_id', $request->teacher_id)->pluck('id');
            $query->whereIn('teacher_id', $teacherIds);
        }

        if ($request->filled('school_id')) {
            $schoolIds = $request->school_id;
            if (in_array('all', $schoolIds)) {
            } else {
                $query->whereIn('school_id', $schoolIds);
            }
        }
        if ($request->filled('observer_id')) {
            $query->where('observer_id', $request->observer_id);
        }
        if ($request->filled('city')) {
            $query->whereHas('school', function ($query) use ($request) {
                $query->whereIn('city', $request->city);
            });
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
        if ($query->get()->count() == 0) {
            return redirect()->back()->with('error', 'No observations found for set filters');
        }

        $observations = $query->pluck('id');

        $obsCount = $observations->count();


        // dd($obsCount);
        $histories = ObservationHistory::whereIn('observation_id', $observations)->get();
        // dd($history);
        foreach ($histories as $history) {
            $headerId = ObservationQuestion::find($history->question_id)->observation_header_id;
            $data[$headerId]['questions'][$history->question_id]['avg_rating'] += $history->rate;
        }
        if (isset($data)) {
            foreach ($data as $header) {
                foreach ($header['questions'] as $question) {
                    $data[$header['header_id']]['questions'][$question['question_id']]['avg_rating'] = round($data[$header['header_id']]['questions'][$question['question_id']]['avg_rating'] / $obsCount, 2);
                }
            }
            $cities = School::distinct()->whereNotNull('city')->pluck('city');
            return view('admin.observers.observation_report_admin', compact('stages', 'cities', 'teachers', 'observers', 'schools', 'headers', 'data'));
        } else {
            $cities = School::distinct()->whereNotNull('city')->pluck('city');
            return view('admin.observers.observation_report_admin', compact('stages', 'cities', 'teachers', 'observers', 'schools', 'headers'));
        }
    }
}
