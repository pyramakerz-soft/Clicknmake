@extends('admin.layouts.layout')

@section('content')
<div class="wrapper">
    @include('admin.layouts.sidebar')

    <div class="main">
        @include('admin.layouts.navbar')

        <main class="content">
            <div class="container-fluid p-0">
                <div class="d-flex" style="justify-content: space-between;">
                    <h1 class="">Assignment Report</h1>
                    <div class="d-flex">
                        <button id="export-pdf" class="btn btn-primary me-2">Export as PDF</button>
                    </div>
                </div>
                @if (session('error'))
                <div class="alert alert-danger d-flex justify-content-between align-items-center" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif


                <form id="filter-form" action="{{ route('admin.assignmentAvgReport') }}" method="GET" enctype="multipart/form-data" class="mb-4 flex" style="gap:10px; padding:10px">
                    <div class="mb-3" id="school_select">
                        <label for="school_id" class="form-label">School</label>
                        <select name="school_id" id="school_id" class="form-control" required>
                            <option selected disabled value="">Please Select School</option>
                            @foreach ($schools as $school)
                            <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3" id="teacher_select">
                        <label for="teacher_id" class="form-label">Teacher</label>
                        <select name="teacher_id" id="teacher_id" class="form-control" required>
                            <option selected disabled value="">Please Select School</option>
                        </select>
                    </div>


                    <!-- From Date Filter -->
                    <div class="flex" style="display:flex; justify-content:space-between">
                        <div class="col-md-5">
                            <label for="from_date">From Date</label>
                            <!-- <input type="date" class="form-control" name="from_date" id="from_date"> -->
                            <input type="date" class="form-control" name="from_date" id="from_date" value="{{ old('from_date', $request['from_date'] ?? '') }}">
                        </div>

                        <!-- To Date Filter -->
                        <div class="col-md-5">
                            <label for="to_date">To Date</label>
                            <!-- <input type="date" class="form-control" name="to_date" id="to_date"> -->
                            <input type="date" class="form-control" name="to_date" id="to_date" value="{{ old('to_date', $request['to_date'] ?? '') }}">
                        </div>
                    </div>

                    <div class="col-md-4 mt-4">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </form>
            </div>


            <div id="pdf-content">
                @if (isset($chartData))
                <div class="container mt-3">
                    <canvas id="groupedBarChart" width="400" height="200"></canvas>
                </div>

                <div class="container mt-3">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Grade</th>
                                <th>Assignment Name</th>
                                <th>Students</th>
                                <th>Students Average</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $stage)
                            <tr>
                                <td rowspan="{{ count($stage['assignments']) > 0 ? count($stage['assignments']) : 1 }}">
                                    {{ $stage['stage_name'] }}
                                </td>

                                <!-- Loop through assignments -->
                                @if (count($stage['assignments']) > 0)
                                @foreach ($stage['assignments'] as $assignment)
                                @if (!$loop->first)
                            <tr>
                                @endif
                                <td>{{ $assignment['assignment_name'] }}</td>
                                <td>
                                    @if (count($assignment['students']) > 0)
                                    @foreach ($assignment['students'] as $student_id)
                                    @php
                                    $student = App\Models\Student::find($student_id);
                                    @endphp
                                    <div>{{ $student->username }}</div>
                                    @endforeach
                                    @else
                                    No Students
                                    @endif
                                </td>
                                <td>{{ $assignment['students_average'] }}</td>
                            </tr>
                            @endforeach
                            @else
                            <td colspan="4">No Assignments</td>
                            @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
                @endif
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
        const title = "Assignment Report";
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
            pdf.save("Assignment_Report.pdf");
        });
    });
</script>
@if (isset($chartData))
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartData = @json($chartData);
    const grades = chartData.map(item => item.grade);
    const datasets = [];

    chartData.forEach((gradeData, gradeIndex) => {
        gradeData.assignments.forEach((assignment, assignmentIndex) => {
            datasets.push({
                label: `${gradeData.grade} - ${assignment.name}`,
                data: chartData.map((_, index) =>
                    index === gradeIndex ? assignment.degree : null
                ),
                backgroundColor: `rgba(${50 + gradeIndex * 50}, ${100 + assignmentIndex * 40}, 200, 0.6)`,
                borderColor: `rgba(${50 + gradeIndex * 50}, ${100 + assignmentIndex * 40}, 200, 1)`,
                borderWidth: 1
            });
        });
    });

    const ctx = document.getElementById("groupedBarChart").getContext("2d");
    const groupedBarChart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: grades,
            datasets: datasets
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        title: (tooltipItems) => tooltipItems[0].dataset.label.split(" - ")[1]
                    }
                }
            },
            scales: {
                x: {
                    stacked: false,
                    title: {
                        display: true,
                        text: "Grades"
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: "Average Assignment Degree"
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
            var selectedTeacherId = "{{$request['teacher_id'] ?? '' }}";
            // getSchoolClasses(schoolId, selectedClassId);
            getSchoolTeachers(schoolId, selectedTeacherId);
            // getSchoolStudents(schoolId, selectedStudentId);

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
                        '<option selected value="" selected disabled>Select a Teacher</option>'
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
</script>
@if (!isset($chartData))
<script>
    const exportButton = document.getElementById('export-pdf');
    exportButton.disabled = true;
</script>
@endif
@endsection