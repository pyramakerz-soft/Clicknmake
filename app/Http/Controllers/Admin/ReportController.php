<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StudentsExport;
use App\Http\Controllers\Controller;
use App\Imports\StudentsImport;
use App\Models\Group;
use App\Models\School;
use App\Models\Stage;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Assignment;
use App\Models\Material;
use App\Models\Observer;
use Illuminate\Http\Request;
use DB;

class ReportController extends Controller
{


    public function getSchoolStudents($schoolId)
    {
        $students =  Student::where('school_id', $schoolId)->get();
        return response()->json($students);
    }
    public function getSchoolTeachers($schoolId)
    {
        $teachers =  Teacher::where('school_id', $schoolId)->get();
        return response()->json($teachers);
    }
    public function getSchoolGrades($schoolId)
    {
        $stageIds = DB::table('school_stage')->where('school_id', $schoolId)->pluck('stage_id');
        $stages = Stage::whereIn('id', $stageIds)->get();
        return response()->json($stages);
    }
    public function getSchoolClasses($schoolId)
    {
        $classes =  Group::where('school_id', $schoolId)->get();
        return response()->json($classes);
    }

    public function assignmentAvgReport(Request $request)
    {
        $schools = School::all();
        $stages = Stage::all();
        $classes = Group::all();
        $teachers = Teacher::all();
        $students = Student::all();
        if ($request->filled('school_id')) {
            $query = Assignment::query();
            // Filter by school
            if ($request->filled('school_id')) {
                $query->where('school_id', $request->school_id);
                if (!$query->exists()) {
                    $schoolName = School::where('id', $request->school_id)->value('name');
                    return redirect()->route('admin.assignmentAvgReport')->with('error', "No Assignments found in School: $schoolName");
                }
            }

            // Filter by teacher
            if ($request->filled('teacher_id')) {
                $query->where('teacher_id', $request->teacher_id);
                if (!$query->exists()) {
                    $teacherUsername = Teacher::where('id', $request->teacher_id)->value('username');
                    return redirect()->route('admin.assignmentAvgReport')->with('error', "No Assignments found for Teacher: $teacherUsername");
                }
            }
            if ($request->filled('from_date')) {
                $query->whereDate('created_at', '>=', $request->from_date);

                if (!$query->exists()) {
                    return redirect()->route('admin.assignmentAvgReport')->with('error', "No Assignments found after {$request->from_date}");
                }
            }
            if ($request->filled('to_date')) {
                $query->whereDate('created_at', '<=', $request->to_date);

                if (!$query->exists()) {
                    return redirect()->route('admin.assignmentAvgReport')->with('error', "No Assignments found before {$request->to_date}");
                }
            }


            $filteredAssignments = $query->pluck('id');

            if ($filteredAssignments->isEmpty()) {
                return redirect()->route('admin.assignmentAvgReport')->with('error', 'No assignments found based on the applied filters.');
            }

            $assignments = DB::table('assignment_stage')
                ->whereIn('assignment_id', $filteredAssignments)
                ->get();

            $stages = Stage::whereIn('id', $assignments->pluck('stage_id'))->get()->keyBy('id');
            $allAssignments = Assignment::whereIn('id', $assignments->pluck('assignment_id'))->get()->keyBy('id');


            $studentAssignments = DB::table('assignment_student')
                ->whereIn('assignment_id', $filteredAssignments)
                ->whereNotNull('submitted_at')
                ->get();


            if (!$studentAssignments->isEmpty()) {
                $data = [];
                foreach ($assignments as $assignment) {
                    $stageId = $assignment->stage_id;
                    $assignmentId = $assignment->assignment_id;

                    if (!isset($data[$stageId])) {
                        $data[$stageId] = [
                            'stage_id' => $stageId,
                            'stage_name' => $stages[$stageId]->name,
                            'assignments' => [],
                        ];
                    }

                    if (!isset($data[$stageId]['assignments'][$assignmentId])) {
                        $data[$stageId]['assignments'][$assignmentId] = [
                            'assignment_id' => $assignmentId,
                            'assignment_name' => $allAssignments[$assignmentId]->title,
                            'students' => [],
                            'students_average' => 0,
                        ];
                    }
                }

                foreach ($studentAssignments as $studentAssignment) {
                    $stageId = DB::table('assignment_stage')
                        ->where('assignment_id', $studentAssignment->assignment_id)
                        ->value('stage_id');

                    $assignmentId = $studentAssignment->assignment_id;
                    $data[$stageId]['assignments'][$assignmentId]['students'][] = $studentAssignment->student_id;
                    if ($studentAssignment->marks !== null) {
                        $data[$stageId]['assignments'][$assignmentId]['students_average'] += $studentAssignment->marks;
                    }
                }

                foreach ($data as $stageId => $stage) {
                    foreach ($stage['assignments'] as $assignmentId => $assignment) {
                        $studentCount = count($assignment['students']);
                        if ($studentCount > 0) {
                            $data[$stageId]['assignments'][$assignmentId]['students_average'] = round($assignment['students_average'] / $studentCount, 2);
                        }
                    }
                }

                // Prepare Chart.js data
                $chartData = [];
                usort($data, function ($a, $b) {
                    return strcmp($a['stage_name'], $b['stage_name']);
                });
                // Process each grade (stage)
                foreach ($data as $stage) {
                    $grade = [
                        'grade' => $stage['stage_name'], // Grade name
                        'assignments' => [] // Initialize assignments array
                    ];

                    // Add assignments to the grade
                    foreach ($stage['assignments'] as $assignment) {
                        $grade['assignments'][] = [
                            'name' => $assignment['assignment_name'], // Assignment name
                            'degree' => $assignment['students_average'] // Assignment degree (students average)
                        ];
                    }

                    // Add the grade with assignments to the chart data
                    $chartData[] = $grade;
                }

                // dd($studentAssignments, $data, $chartData);
                return view('admin.reports.assignment_avg_report', compact('chartData', 'request', 'data', 'schools', 'stages', 'classes', 'teachers', 'students'));
            } else {
                return redirect()->route('admin.assignmentAvgReport')->with('error', 'No Submitted Assignments Found');
            }
        } else {
            return view('admin.reports.assignment_avg_report', compact('schools', 'stages', 'classes', 'teachers', 'students'));
        }

        // return view('admin.reports.assignment_avg_report', compact('schools', 'stages', 'classes', 'teachers', 'students'))->with('error', 'No marks found for all assignments');
    }

    public function compareReport(Request $request)
    {
        $schools = School::all();
        $stages = Stage::all();
        $classes = Group::all();
        $teachers = Teacher::all();
        $students = Student::all();
        if ($request->filled('compare_by')) {

            if ($request->compare_by == 'teachers') {
                $query1 = Assignment::query();
                $query1->where('teacher_id', $request->teacher_id);
                $query2 = Assignment::query();
                $query2->where('teacher_id', $request->teacher_id2);
                $teacher1 = Teacher::where('id', $request->teacher_id)->value('username');
                $teacher2 = Teacher::where('id', $request->teacher_id2)->value('username');
                $msg1 = 'Teacher: ' . $teacher1;
                $msg2 = 'Teacher: ' . $teacher2;
                $labels = [$teacher1, $teacher2];
                // if (!$query1->exists()) {
                //     return redirect()->route('admin.compareReport')->with('error', "No Assignments found for {$msg1}");
                // }
                // if (!$query2->exists()) {
                //     return redirect()->route('admin.compareReport')->with('error', "No Assignments found for {$msg2}");
                // }
            }
            if ($request->compare_by == 'schools') {

                $query1 = Assignment::query();
                $query1->where('school_id', $request->school_id);
                $query2 = Assignment::query();
                $query2->where('school_id', $request->school_id2);

                $school1 = School::where('id', $request->school_id)->value('name');
                $school2 = School::where('id', $request->school_id2)->value('name');
                $labels = [$school1, $school2];
                $msg1 = 'School: ' . $school1;
                $msg2 = 'School: ' . $school2;
                // if (!$query1->exists()) {
                //     return redirect()->route('admin.compareReport')->with('error', "No Assignments found for {$msg1}");
                // }
                // if (!$query2->exists()) {
                //     return redirect()->route('admin.compareReport')->with('error', "No Assignments found for {$msg2}");
                // }
                // dd($query1->get(), $query2->get());
            }
            if ($request->compare_by == 'classes') {
                // $classAssignments1 = DB::table('assignment_class')
                //     ->where('class_id', 166)
                //     ->pluck('assignment_id');
                // $classAssignments2 = DB::table('assignment_class')
                //     ->where('class_id', 499)
                //     ->pluck('assignment_id');
                $classAssignments1 = DB::table('assignment_class')
                    ->where('class_id', $request->class_id)
                    ->pluck('assignment_id');
                $classAssignments2 = DB::table('assignment_class')
                    ->where('class_id', $request->class_id2)
                    ->pluck('assignment_id');


                $query1 = Assignment::whereIn('id', $classAssignments1);
                $query2 = Assignment::whereIn('id', $classAssignments2);
                $class1 = Group::where('id', $request->class_id)->value('name');
                $class2 = Group::where('id', $request->class_id2)->value('name');
                $labels = [$class1, $class2];

                $msg1 = 'Class: ' . $class1;
                $msg2 = 'Class: ' . $class2;
                // if (!$query1->exists()) {
                //     return redirect()->route('admin.compareReport')->with('error', "No Assignments found for {$msg1}");
                // }
                // if (!$query2->exists()) {
                //     return redirect()->route('admin.compareReport')->with('error', "No Assignments found for {$msg2}");
                // }
            }

            if ($request->filled('from_date')) {
                $query1->whereDate('created_at', '>=', $request->from_date);
                $query2->whereDate('created_at', '>=', $request->from_date);

                // if (!$query1->exists()) {
                //     return redirect()->route('admin.compareReport')->with('error', "No Assignments found for {$msg1} after {$request->from_date}");
                // }
                // if (!$query2->exists()) {
                //     return redirect()->route('admin.compareReport')->with('error', "No Assignments found for {$msg2} after {$request->from_date}");
                // }
            }
            if ($request->filled('to_date')) {
                $query1->whereDate('created_at', '<=', $request->to_date);
                $query2->whereDate('created_at', '<=', $request->to_date);

                // if (!$query1->exists()) {
                //     return redirect()->route('admin.compareReport')->with('error', "No Assignments found for {$msg1} before {$request->to_date}");
                // }
                // if (!$query2->exists()) {
                //     return redirect()->route('admin.compareReport')->with('error', "No Assignments found for {$msg2} before {$request->to_date}");
                // }
            }
            $filteredAssignments1 = $query1->pluck('id');
            $filteredAssignments2 = $query2->pluck('id');

            // dd($filteredAssignments1, $filteredAssignments2);

            // if ($filteredAssignments1->isEmpty()) {
            //     return redirect()->route('admin.compareReport')->with('error', 'No assignments found based on the applied filters.');
            // }
            // if ($filteredAssignments2->isEmpty()) {
            //     return redirect()->route('admin.compareReport')->with('error', 'No assignments found based on the applied filters.');
            // }

            $assignments1 = DB::table('assignment_stage')
                ->whereIn('assignment_id', $filteredAssignments1)
                ->get();
            $assignments2 = DB::table('assignment_stage')
                ->whereIn('assignment_id', $filteredAssignments2)
                ->get();

            $stages1 = Stage::whereIn('id', $assignments1->pluck('stage_id'))->get()->keyBy('id');
            $allAssignments1 = Assignment::whereIn('id', $assignments1->pluck('assignment_id'))->get()->keyBy('id');

            $stages2 = Stage::whereIn('id', $assignments2->pluck('stage_id'))->get()->keyBy('id');
            $allAssignments2 = Assignment::whereIn('id', $assignments2->pluck('assignment_id'))->get()->keyBy('id');

            $studentAssignments1 = DB::table('assignment_student')
                ->whereIn('assignment_id', $filteredAssignments1)
                ->whereNotNull('submitted_at')
                ->get();
            $studentAssignments2 = DB::table('assignment_student')
                ->whereIn('assignment_id', $filteredAssignments2)
                ->whereNotNull('submitted_at')
                ->get();
            // dd($studentAssignments1, $studentAssignments2);
            $data1 = [];
            foreach ($assignments1 as $assignment) {
                $stageId = $assignment->stage_id;
                $assignmentId = $assignment->assignment_id;

                if (!isset($data1[$stageId])) {
                    $data1[$stageId] = [
                        'stage_id' => $stageId,
                        'stage_name' => $stages1[$stageId]->name,
                        'assignments' => [],
                    ];
                }

                if (!isset($data1[$stageId]['assignments'][$assignmentId])) {
                    $data1[$stageId]['assignments'][$assignmentId] = [
                        'assignment_id' => $assignmentId,
                        'assignment_name' => $allAssignments1[$assignmentId]->title,
                        'students' => [],
                        'students_average' => 0,
                    ];
                }
            }

            foreach ($studentAssignments1 as $studentAssignment) {
                $stageId = DB::table('assignment_stage')
                    ->where('assignment_id', $studentAssignment->assignment_id)
                    ->value('stage_id');

                $assignmentId = $studentAssignment->assignment_id;
                $data1[$stageId]['assignments'][$assignmentId]['students'][] = $studentAssignment->student_id;
                if ($studentAssignment->marks !== null) {
                    $data1[$stageId]['assignments'][$assignmentId]['students_average'] += $studentAssignment->marks;
                }
            }

            // Calculate averages
            foreach ($data1 as $stageId => $stage) {
                foreach ($stage['assignments'] as $assignmentId => $assignment) {
                    $studentCount = count($assignment['students']);
                    if ($studentCount > 0) {
                        $data1[$stageId]['assignments'][$assignmentId]['students_average'] = round($assignment['students_average'] / $studentCount, 2);
                    }
                }
            }

            // dd($data1);
            // Prepare Chart.js data
            $chartData = [];

            // Process each grade (stage)
            foreach ($data1 as $stage) {
                $material = Material::where('stage_id', $stage['stage_id'])->first();
                if ($material) {
                    $material = ' - ' . $material->title;
                } else {
                    $material = ' ';
                }
                $grade = [
                    'grade' => $stage['stage_name'] . $material, // Grade name
                    'assignments' => [], // Initialize assignments array
                    'color' => '#9e9fdc'
                ];

                // Add assignments to the grade
                foreach ($stage['assignments'] as $assignment) {
                    $grade['assignments'][] = [
                        'name' => $assignment['assignment_name'], // Assignment name
                        'degree' => $assignment['students_average'] // Assignment degree (students average)
                    ];
                }

                // Add the grade with assignments to the chart data
                $chartData[] = $grade;
            }
            // dd($data1);


            $data2 = [];
            foreach ($assignments2 as $assignment) {
                $stageId = $assignment->stage_id;
                $assignmentId = $assignment->assignment_id;

                if (!isset($data2[$stageId])) {
                    $data2[$stageId] = [
                        'stage_id' => $stageId,
                        'stage_name' => $stages2[$stageId]->name,
                        'assignments' => [],
                    ];
                }

                if (!isset($data2[$stageId]['assignments'][$assignmentId])) {
                    $data2[$stageId]['assignments'][$assignmentId] = [
                        'assignment_id' => $assignmentId,
                        'assignment_name' => $allAssignments2[$assignmentId]->title,
                        'students' => [],
                        'students_average' => 0,
                    ];
                }
            }

            foreach ($studentAssignments2 as $studentAssignment) {
                $stageId = DB::table('assignment_stage')
                    ->where('assignment_id', $studentAssignment->assignment_id)
                    ->value('stage_id');

                $assignmentId = $studentAssignment->assignment_id;
                $data2[$stageId]['assignments'][$assignmentId]['students'][] = $studentAssignment->student_id;
                if ($studentAssignment->marks !== null) {
                    $data2[$stageId]['assignments'][$assignmentId]['students_average'] += $studentAssignment->marks;
                }
            }

            // Calculate averages
            foreach ($data2 as $stageId => $stage) {
                foreach ($stage['assignments'] as $assignmentId => $assignment) {
                    $studentCount = count($assignment['students']);
                    if ($studentCount > 0) {
                        $data2[$stageId]['assignments'][$assignmentId]['students_average'] = round($assignment['students_average'] / $studentCount, 2);
                    }
                }
            }


            // Prepare Chart.js data
            // $chartData = [];

            // Process each grade (stage)
            foreach ($data2 as $stage) {
                $material = Material::where('stage_id', $stage['stage_id'])->first();
                if ($material) {
                    $material = ' - ' . $material->title;
                } else {
                    $material = ' ';
                }

                $grade = [
                    'grade' => $stage['stage_name'] . $material,
                    'assignments' => [], // Initialize assignments array
                    'color' => '#0d6efd'
                ];

                // Add assignments to the grade
                foreach ($stage['assignments'] as $assignment) {
                    $grade['assignments'][] = [
                        'name' => $assignment['assignment_name'], // Assignment name
                        'degree' => $assignment['students_average'] // Assignment degree (students average)
                    ];
                }

                // Add the grade with assignments to the chart data
                $chartData[] = $grade;
            }
            // dd($chartData);
            // dd($data1, $data2);
            // dd($chartData);
            // dd($msg1, $msg2);

            $request = $request->all();
            return view('admin.reports.compare_report', compact('msg1', 'msg2', 'chartData', 'request', 'data1', 'data2', 'labels', 'schools', 'stages', 'classes', 'teachers', 'students'));
        }

        return view('admin.reports.compare_report', compact('schools', 'stages', 'classes', 'teachers', 'students'))->with('error', 'No marks found for all assignments');
    }
    public function assessmentReport(Request $request)
    {
        $stages = Stage::all();
        $classes = Group::all();
        $teachers = Teacher::all();
        $students = Student::all();
        $schools = School::all();

        if ($request->filled('school_id')) {
            $query = DB::table('student_assessments');

            if ($request->filled('school_id')) {
                $query->join('students as s1', 'student_assessments.student_id', '=', 's1.id')
                    ->where('s1.school_id', $request->school_id);
                if (!$query->exists()) {
                    $schoolName = School::where('id', $request->school_id)->value('name');
                    return redirect()->route('admin.assesmentReport')->with('error', "No Assessments found for School: $schoolName");
                }
            }

            if ($request->filled('class_id')) {
                $query->join('students as s2', 'student_assessments.student_id', '=', 's2.id')
                    ->where('s2.class_id', $request->class_id);
                if (!$query->exists()) {
                    $className = Group::where('id', $request->class_id)->value('name');
                    return redirect()->route('admin.assesmentReport')->with('error', "No Assessments found for Class: $className");
                }
            }

            if ($request->filled('teacher_id')) {
                $query->where('teacher_id', $request->teacher_id);
                if (!$query->exists()) {
                    $teacherUsername = Teacher::where('id', $request->teacher_id)->value('name');
                    return redirect()->route('admin.assesmentReport')->with('error', "No Assessments found for Teacher: $teacherUsername");
                }
            }

            if ($request->filled('student_id')) {
                $query->where('student_id', $request->student_id);
                if (!$query->exists()) {
                    $studentUsername = Student::where('id', $request->student_id)->value('username');
                    return redirect()->route('admin.assesmentReport')->with('error', "No Assessments found for Student: $studentUsername");
                }
            }

            if ($request->filled('stage_id')) {
                $query->join('students as s3', 'student_assessments.student_id', '=', 's3.id')
                    ->join('groups', 's3.class_id', '=', 'groups.id')
                    ->where('groups.stage_id', $request->stage_id);
                if (!$query->exists()) {
                    $stageName = Stage::where('id', $request->stage_id)->value('name');
                    return redirect()->route('admin.assesmentReport')->with('error', "No Assessments found for Grade: $stageName");
                }
            }

            if ($request->filled('from_date')) {
                $query->whereDate('student_assessments.created_at', '>=', $request->from_date);

                if (!$query->exists()) {
                    return redirect()->route('admin.assesmentReport')->with('error', "No Assessments found after {$request->from_date}");
                }
            }
            if ($request->filled('to_date')) {
                $query->whereDate('student_assessments.created_at', '<=', $request->to_date);

                if (!$query->exists()) {
                    return redirect()->route('admin.assesmentReport')->with('error', "No Assessments found before {$request->to_date}");
                }
            }
            $assessments = $query->get();

            if ($assessments->isEmpty()) {
                return redirect()->route('admin.assesmentReport')->with('error', 'No assessments found for the selected criteria.');
            }
            $homeworkAvg = 0;
            $participationAvg = 0;
            $attendanceAvg = 0;
            $projectAvg = 0;
            $behaviorAvg = 0;

            foreach ($assessments as $assessment) {
                if ($assessment->homework_score !== null)
                    $homeworkAvg += round(($assessment->homework_score / 10) * 100, 2);
                if ($assessment->classroom_participation_score !== null)
                    $participationAvg += round(($assessment->classroom_participation_score / 20) * 100, 2);
                if ($assessment->attendance_score !== null)
                    $attendanceAvg += round(($assessment->attendance_score / 10) * 100, 2);
                if ($assessment->final_project_score !== null)
                    $projectAvg += round(($assessment->final_project_score / 50) * 100, 2);
                if ($assessment->classroom_behavior_score !== null)
                    $behaviorAvg += round(($assessment->classroom_behavior_score / 20) * 100, 2);
            }
            $totalAssesments = count($assessments);
            $homeworkAvg = round($homeworkAvg / $totalAssesments, 2);
            $participationAvg = round($participationAvg / $totalAssesments, 2);
            $attendanceAvg = round($attendanceAvg / $totalAssesments, 2);
            $behaviorAvg = round($behaviorAvg / $totalAssesments, 2);
            $projectAvg = round($projectAvg / $totalAssesments, 2);


            $chartData = [
                [
                    'name' => 'Homework',
                    'degree' => $homeworkAvg
                ],
                [
                    'name' => 'Participation',
                    'degree' => $participationAvg
                ],
                [
                    'name' => 'Behavior',
                    'degree' => $behaviorAvg
                ],
                [
                    'name' => 'Attendance',
                    'degree' => $attendanceAvg
                ],
                [
                    'name' => 'Project',
                    'degree' => $projectAvg
                ]
            ];
            $request = $request->all();
            return view('admin.reports.assesment_report', compact('schools', 'chartData', 'assessments', 'request', 'totalAssesments', 'stages', 'classes', 'teachers', 'students'));
        } else {
            return view('admin.reports.assesment_report', compact('schools', 'request', 'stages', 'classes', 'teachers', 'students'));
        }
    }

    public function loginReport(Request $request)
    {
        $schools = School::all();
        if ($request->filled('compare_by')) {
            if ($request->compare_by == 'teachers') {
                $user = 'Teacher';
                $query = Teacher::query();
                if ($request->filled('school_id')) {
                    $query->where('school_id', $request->school_id);
                    if (!$query->exists()) {
                        $schoolName = School::where('id', $request->school_id)->value('name');
                        return redirect()->route('admin.loginReport')->with('error', "No Teachers found in School: $schoolName");
                    }
                }
                $teachers = $query->get();
                $data = [];
                foreach ($teachers as $teacher) {
                    $data[] = [
                        'name' => $teacher->name,
                        'logins' => $teacher->num_logins
                    ];
                }
            }
            if ($request->compare_by == 'students') {
                $user = 'Student';
                $query = Student::query();
                if ($request->filled('school_id')) {
                    $query->where('school_id', $request->school_id);
                    if (!$query->exists()) {
                        $schoolName = School::where('id', $request->school_id)->value('name');
                        return redirect()->route('admin.loginReport')->with('error', "No Students found in School: $schoolName");
                    }
                }
                if ($request->filled('class_id')) {
                    $query->where('class_id', $request->class_id);
                    if (!$query->exists()) {
                        $className = Group::where('id', $request->class_id)->value('name');
                        return redirect()->route('admin.loginReport')->with('error', "No Students found in Class: $className");
                    }
                }
                $students = $query->get();
                $data = [];
                foreach ($students as $student) {
                    $data[] = [
                        'name' => $student->username,
                        'logins' => $student->num_logins
                    ];
                }
            }
            if ($request->compare_by == 'observers') {
                $query = Observer::query();
                $user = 'Observer';
                if ($query->exists()) {
                    $observers = $query->get();
                    $data = [];
                    foreach ($observers as $observer) {
                        $data[] = [
                            'name' => $observer->name,
                            'logins' => $observer->num_logins
                        ];
                    }
                } else {
                    return redirect()->route('admin.loginReport')->with('error', "No Observers found");
                }
            }
            $request = $request->all();
            // dd($data);
            return view('admin.reports.login_report', compact('schools', 'data', 'user', 'request'));
        }
        $request = $request->all();
        return view('admin.reports.login_report', compact('schools', 'request'));
    }
}
