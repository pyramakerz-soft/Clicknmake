@extends('admin.layouts.layout')

@section('content')
    <div class="wrapper">
        @include('admin.layouts.sidebar')

        <div class="main">
            @include('admin.layouts.navbar')

            <main class="content">
                <div class="container-fluid p-0">

                    <div class="d-flex" style="justify-content: space-between;">
                        <h1 class="">Assessment Report</h1>
                        <div class="d-flex">
                            <button id="export-pdf" class="btn btn-primary me-2">Export as PDF</button>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" adata-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.assesmentReport') }}" method="GET" enctype="multipart/form-data">
                        <div class="mb-3" id="school_select">
                            <label for="school_id" class="form-label">School</label>
                            <select name="school_id" id="school_id" class="form-control" required>
                                <option selected disabled value="">Please Select School</option>
                                @foreach ($schools as $school)
                                    <option value="{{ $school->id }}"
                                        {{ request('school_id') == $school->id ? 'selected' : '' }}>
                                        {{ $school->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex" style="display: flex; justify-content: space-between; gap:10px">
                            <div class="mb-3 col-6" id="grade_select">
                                <label for="stage_id" class="form-label">Grade</label>
                                <select name="stage_id" id="stage_id" class="form-control">
                                    <option selected disabled value="">Please Select School</option>
                                </select>
                            </div>

                            <div class="mb-3 col-6" id="class_select">
                                <label for="class_id" class="form-label">Class</label>
                                <select name="class_id" id="class_id" class="form-control">
                                    <option selected disabled value="">Please Select School</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3" id="teacher_select">
                            <label for="teacher_id" class="form-label">Teacher</label>
                            <select name="teacher_id" id="teacher_id" class="form-control">
                                <option selected disabled value="">Please Select School</option>
                            </select>
                        </div>

                        <div class="mb-3" id="student_select">
                            <label for="student_id" class="form-label">Student</label>
                            <select name="student_id" id="student_id" class="form-control">
                                <option selected disabled value="">Please Select School</option>
                            </select>
                        </div>
                        <div class="flex" style="display: flex; justify-content: space-between">
                            <div class="mb-3 col-5">
                                <label for="from_date" class="form-label">Date From</label>
                                <input type="date" name="from_date" id="from_date" class="form-control"
                                    value="{{ request('from_date') }}">
                            </div>
                            <div class="mb-3 col-5">
                                <label for="to_date" class="form-label">Date To</label>
                                <input type="date" name="to_date" id="to_date" class="form-control"
                                    value="{{ request('to_date') }}">
                            </div>
                        </div>



                        <button type="submit" class="btn btn-primary">Show Report</button>
                    </form>


                    <div id="pdf-content">
                        @if (isset($chartData) && isset($assessments))
                            <div class="container mt-3">
                                <canvas id="barChart" width="400" height="200"></canvas>
                            </div>
                            <div class="container mt-3">
                                <h4 class="mb-3">Total Assessments: {{ $totalAssesments }}</h4>
                                <table class="table table-bordered" id="report-table">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Teacher</th>
                                            <th>School</th>
                                            <th>Class</th>
                                            <th>Grade</th>
                                            <th>Homework</th>
                                            <th>Participation</th>
                                            <th>Behavior</th>
                                            <th>Attendance</th>
                                            <th>Project</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($assessments as $assessment)
                                            @php
                                                $student = App\Models\Student::find($assessment->student_id);
                                                $teacher = App\Models\Teacher::find($assessment->teacher_id);
                                            @endphp
                                            <tr>
                                                <td>
                                                    <span class="truncate-text"
                                                        title="{{ $student->username }}">{{ $student->username }}</span>
                                                </td>
                                                <td>
                                                    <span class="truncate-text"
                                                        title="{{ $teacher->name }}">{{ $teacher->name }}</span>
                                                </td>
                                                <td>{{ $student->school->name }}</td>
                                                <td>{{ $student->classes->name }}</td>
                                                <td>{{ $student->stage->name }}</td>
                                                <td>{{round(($assessment->homework_score / 10) * 100, 2) . '%' ?? 'N/A' }}</td>
                                                <td>{{round(($assessment->classroom_participation_score / 20) * 100, 2) . '%' ?? 'N/A' }}</td>
                                                <td>{{round(($assessment->classroom_behavior_score / 20) * 100, 2) . '%' ?? 'N/A' }}</td>
                                                <td>{{round(($assessment->attendance_score / 10) * 100, 2) . '%' ?? 'N/A' }}</td>
                                                <td>{{round(($assessment->final_project_score / 50) * 100, 2) . '%' ?? 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <style>
                                    .truncate-text {
                                        display: inline-block;
                                        max-width: 150px;
                                        /* Set maximum width for the cell content */
                                        white-space: nowrap;
                                        overflow: hidden;
                                        text-overflow: ellipsis;
                                        vertical-align: middle;
                                        /* Align the text properly */
                                        cursor: pointer;
                                        /* Optional: Indicate interactivity for the tooltip */
                                    }

                                    .truncate-text:hover {
                                        text-decoration: underline;
                                        /* Optional: Add underline on hover */
                                    }
                                </style>
                            </div>
                        @endif
                    </div>
                </div>
            </main>


        </div>
    </div>
@endsection

@section('page_js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        document.getElementById("export-pdf").addEventListener("click", function() {
            const pdf = new jspdf.jsPDF("p", "mm", "a4"); // Create a new PDF document

            // Add the title at the top of the PDF
            const title = "Assessment Report";
            pdf.setFont("helvetica", "bold"); // Set font to Helvetica Bold
            pdf.setFontSize(20); // Set font size
            pdf.text(title, 105, 20, {
                align: "center"
            }); // Add the title, centered at the top

            // Select the content to export
            const content = document.getElementById("pdf-content");

            // Use html2canvas to render the content
            html2canvas(content, {
                scale: 2, // Increase the scale for better quality
            }).then((canvas) => {
                const imgData = canvas.toDataURL("image/png");
                const imgWidth = 190; // Set image width (A4 page is 210mm wide, leaving margins)
                const pageHeight = 297; // A4 page height in mm
                const imgHeight = (canvas.height * imgWidth) / canvas.width; // Maintain aspect ratio
                let heightLeft = imgHeight;

                let position = 30; // Start below the title (20mm title + 10mm padding)

                // Add the image to the PDF
                pdf.addImage(imgData, "PNG", 10, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;

                // Add more pages if the content is taller than one page
                while (heightLeft > 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(imgData, "PNG", 10, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }

                // Save the PDF
                pdf.save("Assessment_Report.pdf");
            });
        });
    </script>
   @if (isset($chartData))
    <script>
        const chartData = @json($chartData);

        const labels = chartData.map(data => data.name);
        const degrees = chartData.map(data => data.degree);

        const ctx = document.getElementById('barChart').getContext('2d');
        const barChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Average Degrees',
                    data: degrees,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Student Performance Averages'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Percentage'
                        }
                    }
                }
            }
        });
    </script>
@endif

    <script>
        $(document).ready(function() {
            $('.js-select2').select2();

            $('#school_id').change(function() {
                var schoolId = $('#school_id').val();
                var selectedClassId = "{{ $request['class_id'] ?? '' }}";
                var selectedStudentId = "{{ $request['student_id'] ?? '' }}";
                var selectedTeacherId = "{{ $request['teacher_id'] ?? '' }}";
                var selectedStageId = "{{ $request['stage_id'] ?? '' }}";
                getSchoolClasses(schoolId, selectedClassId);
                getSchoolTeachers(schoolId, selectedTeacherId);
                getSchoolStudents(schoolId, selectedStudentId);
                getSchoolGrades(schoolId, selectedStageId);

            });
            $('#school_id').trigger('change');
        });


        function getSchoolStudents(schoolId, selectedStudentId) {
            $.ajax({
                url: '/LMS/lms_pyramakerz/public/admin/get-students-school/' + schoolId,
                type: "GET",
                dataType: "json",
                success: function(data) {
                    // Clear the existing options
                    $('select[name="student_id"]').empty();
                    if (!data || data.length === 0) {
                        $('select[name="student_id"]').append(
                            '<option value="" selected disabled>No Available Students</option>'
                        );
                    } else {
                        $('select[name="student_id"]').append(
                            '<option value="" selected>All Students</option>'
                        );
                        $.each(data, function(key, value) {
                            $('select[name="student_id"]').append(
                                '<option value="' + value.id + '">' + value.username + '</option>'
                            );
                        });
                        if (selectedStudentId) {
                            $('select[name="student_id"]').val(selectedStudentId).trigger('change');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });
        }

        function getSchoolTeachers(schoolId, selectedTeacherId) {
            $.ajax({
                url: '/LMS/lms_pyramakerz/public/admin/get-teachers-school/' + schoolId,
                type: "GET",
                dataType: "json",
                success: function(data) {
                    // Clear the existing options
                    $('select[name="teacher_id"]').empty();
                    if (!data || data.length === 0) {
                        $('select[name="teacher_id"]').append(
                            '<option value="" selected disabled>No Available Teachers</option>'
                        );
                    } else {
                        $('select[name="teacher_id"]').append(
                            '<option value="" selected>All Teachers</option>'
                        );
                        $.each(data, function(key, value) {
                            $('select[name="teacher_id"]').append(
                                '<option value="' + value.id + '">' + value.name + '</option>'
                            );
                        });
                        if (selectedTeacherId) {
                            $('select[name="teacher_id"]').val(selectedTeacherId).trigger('change');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });
        }

        function getSchoolGrades(schoolId, selectedStageId) {
            $.ajax({
                url: '/LMS/lms_pyramakerz/public/admin/get-grades-school/' + schoolId,
                type: "GET",
                dataType: "json",
                success: function(data) {
                    // Clear the existing options
                    $('select[name="stage_id"]').empty();
                    if (!data || data.length === 0) {
                        $('select[name="stage_id"]').append(
                            '<option value="" selected disabled>No Available Grade</option>'
                        );
                    } else {
                        $('select[name="stage_id"]').append(
                            '<option value="" selected disabled>Choose a Grade</option>'
                        );
                        $.each(data, function(key, value) {
                            $('select[name="stage_id"]').append(
                                '<option value="' + value.id + '">' + value.name + '</option>'
                            );
                        });
                        if (selectedStageId) {
                            $('select[name="stage_id"]').val(selectedStageId).trigger('change');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });
        }

        function getSchoolClasses(schoolId, selectedClassId) {
            $.ajax({
                url: '/LMS/lms_pyramakerz/public/admin/get-classes-school/' + schoolId,
                type: "GET",
                dataType: "json",
                success: function(data) {
                    // Clear the existing options
                    $('select[name="class_id"]').empty();
                    if (!data || data.length === 0) {
                        $('select[name="class_id"]').append(
                            '<option value="" selected disabled>No Available Class</option>'
                        );
                    } else {
                        $('select[name="class_id"]').append(
                            '<option value="" selected>All Classes</option>'
                        );
                        $.each(data, function(key, value) {
                            $('select[name="class_id"]').append(
                                '<option value="' + value.id + '">' + value.name + '</option>'
                            );
                        });
                        if (selectedClassId) {
                            $('select[name="class_id"]').val(selectedClassId).trigger('change');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });
        }
    </script>
    @if (!isset($chartData))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const exportButton = document.getElementById('export-pdf');
                if (exportButton) {
                    exportButton.disabled = true;
                }
            });
        </script>
    @endif
@endsection
