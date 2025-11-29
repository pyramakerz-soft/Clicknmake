@extends('admin.layouts.layout')

@section('content')
<div class="wrapper">
    @include('admin.layouts.sidebar')

    <div class="main">
        @include('admin.layouts.navbar')

        <main class="content">
            <div class="container-fluid p-0">
                <div class="d-flex" style="justify-content: space-between;">
                    <h1 class="">Comapare Assignment Report</h1>
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

                <form id="compare-form" action="{{ route('admin.compareReport') }}" method="GET" enctype="multipart/form-data" class="mb-4 flex flex-column" style="gap:10px; padding:10px">
                    <!-- Compare By Field -->
                    <div class="mb-3">
                        <label for="compare_by">Compare By</label>
                        <select id="compare_by" name="compare_by" class="form-control" required>
                            <option value="" disabled selected>Select Option</option>
                            <option value="teachers" {{ request('compare_by') == 'teachers' ? 'selected' : '' }}>Teachers</option>
                            <option value="schools" {{ request('compare_by') == 'schools' ? 'selected' : '' }}>Schools</option>
                            <option value="classes" {{ request('compare_by') == 'classes' ? 'selected' : '' }}>Classes</option>
                        </select>
                    </div>
                    <!-- School Comparison Fields -->
                    <div id="school-fields" class="comparison-fields mb-3" style="display: none;">
                        <label for="school_id">First School</label>
                        <select name="school_id" id="school_id" class="form-control mb-3">
                            <option value="" selected disabled>Select School</option>
                            @foreach ($schools as $school)
                            <option value="{{ $school->id }}"
                                {{ request('school_id') == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                            @endforeach
                        </select>
                        <label for="school_id2">Second School</label>
                        <select name="school_id2" id="school_id2" class="form-control">
                            <option value="" selected disabled>Select School</option>
                            @foreach ($schools as $school)
                            <option value="{{ $school->id }}"
                                {{ request('school_id2') == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Teacher Comparison Fields -->
                    <div id="teacher-fields" class="comparison-fields mb-3" style="display: none;">
                        <label for="teacher_id">First Teacher</label>
                        <select name="teacher_id" id="teacher_id" class="form-control mb-3">
                            <option selected disabled value="">Please Select First School</option>
                        </select>
                        <label for="teacher_id2">Second Teacher</label>
                        <select name="teacher_id2" id="teacher_select2" class="form-control">
                            <option selected disabled value="">Please Select First School</option>
                        </select>
                    </div>



                    <!-- Class Comparison Fields -->
                    <div id="class-fields" class="comparison-fields mb-3" style="display: none;">
                        <label for="class_id">First Class</label>
                        <select name="class_id" id="class_id" class="form-control mb-3">
                            <option value="" selected disabled>Select Class</option>
                            @foreach ($classes as $class)
                            <option value="{{ $class->id }}"
                                {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                            @endforeach
                        </select>
                        <label for="class_id2">Second Class</label>
                        <select name="class_id2" id="class_id2" class="form-control">
                            <option value="" selected disabled>Select Class</option>
                            @foreach ($classes as $class)
                            <option value="{{ $class->id }}"
                                {{ request('class_id2') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit" class="btn btn-primary" style="background-color:#222e3c; border-color: #222e3c;">Compare</button>
                    </div>
                </form>
            </div>
            <div id="pdf-content">
                @if (isset($chartData))
                <div class="container mt-3">
                    <canvas id="groupedBarChart" width="400" height="200"></canvas>
                </div>
                <div class="container mt-3">
                    <h4 style="color:#495057">{{$msg1}} Assignments</h4>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Grade</th>
                                <th>Theme</th>
                                <th>Assignment Name</th>
                                <th>Students</th>
                                <th>Students Average</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data1 as $stage)
                            <!-- Rowspan for Grade and Theme based on assignments -->
                            <tr>
                                <td rowspan="{{ max(count($stage['assignments']), 1) }}">
                                    {{ $stage['stage_name'] }}
                                </td>
                                <td rowspan="{{ max(count($stage['assignments']), 1) }}">
                                    {{ App\Models\Material::where('stage_id', $stage['stage_id'])->first()->title ?? '-' }}
                                </td>

                                <!-- Check if assignments exist -->
                                @if (count($stage['assignments']) > 0)
                                @foreach ($stage['assignments'] as $assignment)
                                @if (!$loop->first)
                            <tr>
                                @endif

                                <!-- Assignment Name -->
                                <td>{{ $assignment['assignment_name'] }}</td>

                                <!-- Students -->
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

                                <!-- Students Average -->
                                <td>{{ $assignment['students_average'] }}</td>
                            </tr>
                            @endforeach
                            @else
                            <!-- No Assignments -->
                            <td colspan="3">No Assignments</td>
                            @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="container mt-3">
                    <h4 style="color:#495057">{{$msg2}} Assignments</h4>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Grade</th>
                                <th>Theme</th>
                                <th>Assignment Name</th>
                                <th>Students</th>
                                <th>Students Average</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data2 as $stage)
                            <!-- Rowspan for Grade and Theme based on assignments -->
                            <tr>
                                <td rowspan="{{ max(count($stage['assignments']), 1) }}">
                                    {{ $stage['stage_name'] }}
                                </td>
                                <td rowspan="{{ max(count($stage['assignments']), 1) }}">
                                    {{ App\Models\Material::where('stage_id', $stage['stage_id'])->first()->title ?? '-' }}
                                </td>

                                <!-- Check if assignments exist -->
                                @if (count($stage['assignments']) > 0)
                                @foreach ($stage['assignments'] as $assignment)
                                @if (!$loop->first)
                            <tr>
                                @endif

                                <!-- Assignment Name -->
                                <td>{{ $assignment['assignment_name'] }}</td>

                                <!-- Students -->
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

                                <!-- Students Average -->
                                <td>{{ $assignment['students_average'] }}</td>
                            </tr>
                            @endforeach
                            @else
                            <!-- No Assignments -->
                            <td colspan="3">No Assignments</td>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
    document.getElementById("export-pdf").addEventListener("click", function() {
        const pdf = new jspdf.jsPDF("p", "mm", "a4"); // Create a new PDF document

        // Add the title at the top of the PDF
        const title = "Compare Assignment Report";
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
            pdf.save("Compare_Assignment_Report.pdf");
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const compareBySelect = document.getElementById('compare_by');
        const teacherFields = document.getElementById('teacher-fields');
        const schoolFields = document.getElementById('school-fields');
        const classFields = document.getElementById('class-fields');

        // Function to show relevant fields and set required attributes based on the selected option
        const showFields = (selectedOption) => {
            // Hide all fields initially and remove required attributes
            teacherFields.style.display = 'none';
            schoolFields.style.display = 'none';
            classFields.style.display = 'none';

            const allSelects = document.querySelectorAll('.comparison-fields select');
            allSelects.forEach(select => select.removeAttribute('required'));

            // Show the relevant fields and set required attributes based on the selected option
            if (selectedOption === 'teachers') {
                schoolFields.style.display = 'block';
                teacherFields.style.display = 'block';
                document.getElementById('school_id').setAttribute('required', 'required');
                document.getElementById('school_id2').setAttribute('required', 'required');
                document.getElementById('teacher_id').setAttribute('required', 'required');
                document.getElementById('teacher_select2').setAttribute('required', 'required');
            } else if (selectedOption === 'schools') {
                schoolFields.style.display = 'block';
                document.getElementById('school_id').setAttribute('required', 'required');
                document.getElementById('school_id2').setAttribute('required', 'required');
            } else if (selectedOption === 'classes') {
                schoolFields.style.display = 'block';
                classFields.style.display = 'block';
                document.getElementById('school_id').setAttribute('required', 'required');
                document.getElementById('school_id2').setAttribute('required', 'required');
                document.getElementById('class_id').setAttribute('required', 'required');
                document.getElementById('class_id2').setAttribute('required', 'required');
            }
        };

        // Check the current value of request('compare_by') and show the corresponding fields
        const initialCompareByValue = '{{ request("compare_by") }}';
        if (initialCompareByValue) {
            showFields(initialCompareByValue);
        }

        // Listen for changes in the 'compare_by' select field
        compareBySelect.addEventListener('change', function() {
            showFields(this.value);
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@if (isset($chartData))
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartData = @json($chartData);
    const legendLabels = @json($labels); // Array of 2 names passed from the controller
    const colors = ['#9e9fdc', '#0d6efd'];

    const grades = chartData.map(item => item.grade);
    const datasets = [];
    var index = 0;

    chartData.forEach((gradeData, gradeIndex) => {
        gradeData.assignments.forEach((assignment, assignmentIndex) => {
            console.log(gradeData, colors[index]);
            datasets.push({
                label: `${gradeData.grade} - ${assignment.name}`,
                data: chartData.map((_, index) =>
                    index === gradeIndex ? assignment.degree : null
                ),
                backgroundColor: gradeData.color,
                borderColor: gradeData.color,
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
                    display: true,
                    position: 'top',
                    labels: {
                        generateLabels: () => {
                            return legendLabels.map((label, i) => ({
                                text: label,
                                fillStyle: colors[i],
                                hidden: false
                            }));
                        }
                    }
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
<style>
    /* Style for dimmed options */
    .dim-option {
        color: gray;
        /* Dimmed text color */
        background-color: #ddd;
        /* Optional: dimmed background color */
        pointer-events: none;
        /* Prevent interaction */
        cursor: not-allowed;
        /* Change cursor to indicate non-clickable */
    }
</style>
<script>
    $(document).ready(function() {
        $('.js-select2').select2();

        // function updateOptions(selectedValue, selectToUpdate) {
        //     // Enable all options first
        //     $(`#${selectToUpdate} option`).prop("disabled", false);

        //     // Disable the matching option in the other select
        //     if (selectedValue) {
        //         $(`#${selectToUpdate} option[value="${selectedValue}"]`)
        //             .prop("disabled", true)
        //             .addClass("dim-option");
        //     }
        // }



        $('#school_id').change(function() {
            var schoolId = $('#school_id').val();
            var compareBy = $('#compare_by').val();
            // updateOptions(schoolId, "school_id2");

            switch (compareBy) {
                case 'classes':
                    var selectedClassId = "{{$request['class_id'] ?? '' }}";
                    getSchoolClasses(schoolId, selectedClassId);
                    break;
                case 'teachers':
                    var selectedTeacherId = "{{$request['teacher_id'] ?? '' }}";
                    getSchoolTeachers(schoolId, selectedTeacherId);
                    break;
                default:
                    console.log('No matching report type selected.');
            }
        });
        $('#school_id2').change(function() {
            var schoolId2 = $('#school_id2').val();
            var compareBy = $('#compare_by').val();
            // updateOptions(schoolId2, "school_id");

            switch (compareBy) {
                case 'classes':
                    var selectedClassId2 = "{{$request['class_id2'] ?? '' }}";
                    getSchoolClasses2(schoolId2, selectedClassId2);
                    break;
                case 'teachers':
                    var selectedTeacherId2 = "{{$request['teacher_id2'] ?? '' }}";
                    getSchoolTeachers2(schoolId2, selectedTeacherId2);
                    break;
                default:
                    console.log('No matching report type selected.');
            }
        });

        $('#compare_by').change(function() {
            $('#school_id').trigger('change');
            $('#school_id2').trigger('change');
        });
        $('#school_id').trigger('change');
        $('#school_id2').trigger('change');
    });


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
                        '<option value="" selected disabled>Select First Teacher</option>'
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

    function getSchoolTeachers2(schoolId, selectedTeacherId) {
        $.ajax({
            url: '/LMS/lms_pyramakerz/public/admin/get-teachers-school/' + schoolId,
            type: "GET",
            dataType: "json",
            success: function(data) {
                // Clear the existing options
                $('select[name="teacher_id2"]').empty();
                if (!data || data.length === 0) {
                    $('select[name="teacher_id2"]').append(
                        '<option value="" selected disabled>No Available Teachers</option>'
                    );
                } else {
                    $('select[name="teacher_id2"]').append(
                        '<option value="" selected disabled>Select Second Teacher</option>'
                    );
                    $.each(data, function(key, value) {
                        $('select[name="teacher_id2"]').append(
                            '<option value="' + value.id + '">' + value.name + '</option>'
                        );
                    });
                    if (selectedTeacherId) {
                        $('select[name="teacher_id2"]').val(selectedTeacherId).trigger('change');
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
                        '<option value="" selected disabled>Select First Class</option>'
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

    function getSchoolClasses2(schoolId, selectedClassId) {
        $.ajax({
            url: '/LMS/lms_pyramakerz/public/admin/get-classes-school/' + schoolId,
            type: "GET",
            dataType: "json",
            success: function(data) {
                // Clear the existing options
                $('select[name="class_id2"]').empty();
                if (!data || data.length === 0) {
                    $('select[name="class_id2"]').append(
                        '<option value="" selected disabled>No Available Class</option>'
                    );
                } else {
                    $('select[name="class_id2"]').append(
                        '<option value="" selected disabled >Select Second Class</option>'
                    );
                    $.each(data, function(key, value) {
                        $('select[name="class_id2"]').append(
                            '<option value="' + value.id + '">' + value.name + '</option>'
                        );
                    });
                    if (selectedClassId) {
                        $('select[name="class_id2"]').val(selectedClassId).trigger('change');
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