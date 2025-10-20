<?php

namespace App\Http\Controllers\Observer;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Material;
use App\Models\Observation;
use App\Models\ObservationHeader;
use App\Models\ObservationHistory;
use App\Models\ObservationQuestion;
use App\Models\Observer;
use App\Models\School;
use App\Models\Stage;
use App\Models\Teacher;
use App\Models\Unit;
use Auth;
use Illuminate\Http\Request;
use DB;

class ObserverDashboardController extends Controller
{
    public function index(Request $request)
    {
        // dd($request->all());
        $observer = Auth::guard('observer')->user();
        // dd($observer);
        $teachers = Teacher::with('school')->whereNull('alias_id')->get();
        $schools = School::all();
        $cities = School::distinct()->whereNotNull('city')->pluck('city');
        $observers = Observer::all();
        $stages = Stage::all();
        // $query = Observation::where('observer_id', $observer->id);
        $query = Observation::with(['school', 'subject', 'stage', 'teacher', 'observer', 'histories.observation_question'])
            ->where('observer_id', $observer->id);

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

        $observations = $query->get();


        return view('pages.observer.observer', compact('teachers', 'stages', 'cities', 'observers', 'schools', 'observer', 'observations'));
    }
    public function exportObservations()
    {
        $observer = Auth::guard('observer')->user();

        $observations = Observation::with(['school', 'subject', 'stage', 'teacher', 'observer', 'histories.observation_question'])
            ->where('observer_id', $observer->id)
            ->get()
            ->map(function ($observation) {
                return [
                    'id' => $observation->id,
                    'name' => $observation->name,
                    'teacher_name' => $observation->teacher->username ?? 'N/A',
                    'coteacher_name' => $observation->coteacher_name ?? 'N/A',
                    'school' => $observation->school->name ?? 'N/A',
                    'city' => $observation->school->city ?? 'N/A',
                    'subject' => $observation->subject->title ?? $observation->subject->name ?? 'N/A',
                    'stage' => $observation->stage->name ?? 'N/A',
                    'activity' => $observation->activity,
                    'note' => $observation->note,
                    'questions' => $observation->histories->map(function ($history) {
                        $question = optional($history->observation_question);
                        return [
                            'name' => $question->question ?? 'N/A',
                            'avg_rating' => $history->rate ?? 0,
                            'max_rating' => $question->max_rate ?? 'N/A'
                        ];
                    })->toArray()
                ];
            });

        return response()->json($observations);
    }
    public function exportSingleObservation($id)
    {
        $observation = Observation::with([
            'school',
            'subject',
            'stage',
            'teacher',
            'observer',
            'histories.observation_question'
        ])->find($id);

        if (!$observation) {
            return response()->json(['error' => 'Observation not found'], 404);
        }

        return response()->json([
            'id' => $observation->id,
            'name' => $observation->name,
            'teacher_name' => optional($observation->teacher)->username ?? 'N/A',
            'coteacher_name' => $observation->coteacher_name ?? 'N/A',
            'school' => optional($observation->school)->name ?? 'N/A',
            'city' => optional($observation->school)->city ?? 'N/A',
            'subject' => optional($observation->subject)->title ?? optional($observation->subject)->name ?? 'N/A',
            'stage' => optional($observation->stage)->name ?? 'N/A',
            'activity' => $observation->activity,
            'note' => $observation->note,
            'questions' => $observation->histories->map(function ($history) {
                return [
                    'name' => optional($history->observation_question)->question ?? 'N/A',
                    'avg_rating' => $history->rate ?? 0,
                    'max_rating' => optional($history->observation_question)->max_rate ?? 'N/A'
                ];
            })->toArray()
        ]);
    }

    public function createObservation()
    {
        $observer = Auth::guard('observer')->user();
        
        $observations = Observation::all();
        $teachers = Teacher::with('school')->whereNull('alias_id')->get();
        $headers = ObservationHeader::all();
        $cities = School::distinct()->whereNotNull('city')->pluck('city');
        $grades = Stage::all();

        return view('pages.observer.create_observation', compact('observer', 'grades', 'cities', 'observations', 'teachers', 'headers'));
    }
    public function getSchool($teacherId)
    {
        $schools = [];
        $teacher = Teacher::find($teacherId);
        if (!$teacher) {
            return response()->json(['error' => 'Teacher not found'], 404);
        }
        $school = School::find($teacher->school_id);
        $schools[] = $school;

        $teachers = Teacher::where('alias_id', $teacherId)->get();
        foreach ($teachers as $teacher) {
            $school = School::find($teacher->school_id);
            $schools[] = $school;
        }

        if (!$schools) {
            return response()->json(['error' => 'School not found'], 404);
        }
        // dd($schools);
        return response()->json($schools);
        // return response()->json($school);
    }
    public function getCoteachers($school_id)
    {
        $coteachers = Teacher::where('school_id', $school_id)

            ->get();

        return response()->json($coteachers);
    }
    public function getStages($teacherId)
    {
        $teacher = Teacher::find($teacherId);
        if (!$teacher) {
            return response()->json(['error' => 'Teacher not found'], 404);
        }
        $stages = DB::table('teacher_stage')
            ->where('teacher_id', $teacherId)
            ->join('stages', 'teacher_stage.stage_id', '=', 'stages.id')
            ->select('stages.id', 'stages.name')
            ->get();

        return response()->json($stages);
    }
    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'observation_name' => 'required|string',
            'observer_id' => 'required|integer',
            'teacher_id' => 'required|integer',
            'coteacher_id' => 'nullable|integer',
            'grade_id' => 'required|integer',
            'date' => 'required|date',
            'lesson_segment' => 'required',
            '_token' => 'required',
        ]);

        // $teacher = Teacher::find($request->teacher_id);
        $teacher = Teacher::where('id', $request->teacher_id)
            ->where('school_id', $request->school_id)
            ->orWhere('alias_id', $request->teacher_id)
            ->where('school_id', $request->school_id)->first();
        // dd($request->all(), $teacher);
        $coteacher = Teacher::find($request->coteacher_id);

        $observation = Observation::create([
            'name' => $request->observation_name,
            'teacher_name' => $teacher->name,
            'observer_id' => $request->observer_id,
            'teacher_id' => $teacher->id,
            'stage_id' => $request->grade_id,
            'school_id' => $teacher->school_id,
            'activity' => $request->date,
            'coteacher_id' => $request->coteacher_id,
            'coteacher_name' => $coteacher->name ?? null,
            'material_id' => null,
            'note' => $request->note,
            'subject_area' => $request->subject_area,
            'lesson_segment' => json_encode($request->lesson_segment),
        ]);

        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'question-')) {
                $questionId = str_replace('question-', '', $key);
                ObservationHistory::create([
                    'observation_id' => $observation->id,
                    'question_id' => $questionId,
                    'rate' => $value,
                ]);
            }
        }

        return redirect()->route('observer.dashboard')->with('success', 'Observation created successfully!');
    }
    public function destroy(string $id)
    {
        $observation = Observation::findOrFail($id);
        $observation->delete();
        return redirect()->route('observer.dashboard')->with('success', 'Observation deleted successfully.');
    }
    public function view(string $id)
    {
        $observer = Auth::guard('observer')->user();
        $headers = ObservationHeader::all();
        $observation = Observation::find($id);
        $answers = ObservationHistory::where('observation_id', $id)->get();
        return view('pages.observer.view_observation', compact('observer', 'headers', 'observation', 'answers'));
    }
    public function report(Request $request)
    {
        $observer = Auth::guard('observer')->user();
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

        $query = Observation::where('observer_id', $observer->id);
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
            $overallComments = Observation::whereIn('id', $observations)
                ->whereNotNull('note')
                ->with('teacher')
                ->get(['note', 'teacher_id']);

            $cities = School::distinct()->whereNotNull('city')->pluck('city');
            return view('pages.observer.observation_report', compact('stages', 'cities', 'teachers', 'observer', 'observers', 'schools', 'headers', 'data', 'overallComments'));
        } else {
            $cities = School::distinct()->whereNotNull('city')->pluck('city');
            return view('pages.observer.observation_report', compact('stages', 'cities', 'teachers', 'observer', 'observers', 'schools', 'headers'));
        }
    }
}
