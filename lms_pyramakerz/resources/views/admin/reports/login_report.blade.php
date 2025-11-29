@extends('admin.layouts.layout')

@section('content')
<div class="wrapper">
    @include('admin.layouts.sidebar')

    <div class="main">
        @include('admin.layouts.navbar')

        <main class="content">
            <div class="container-fluid p-0">
                <div class="d-flex" style="justify-content: space-between;">
                    <h1 class="">Login Report</h1>
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

                <form id="compare-form" action="{{ route('admin.loginReport') }}" method="GET" enctype="multipart/form-data" class="mb-4 flex flex-column" style="gap:10px; padding:10px">
                    <!-- Compare By Field -->
                    <div class="mb-3">
                        <label for="compare_by">Filter By</label>
                        <select id="compare_by" name="compare_by" class="form-control" required>
                            <option value="" disabled selected>Select Option</option>
                            <option value="students" {{ request('compare_by') == 'students' ? 'selected' : '' }}>Students</option>
                            <option value="teachers" {{ request('compare_by') == 'teachers' ? 'selected' : '' }}>Teachers</option>
                            <option value="observers" {{ request('compare_by') == 'observers' ? 'selected' : '' }}>Observers</option>
                        </select>
                    </div>

                    <div id="school-fields" class="mb-3" style="display: none;">
                        <label for="school_id">Select School</label>
                        <select name="school_id" id="school_id" class="form-control mb-3">
                            <option value="" selected disabled>Select School</option>
                            @foreach ($schools as $school)
                            <option value="{{ $school->id }}"
                                {{ request('school_id') == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="class-fields" class="mb-3" style="display: none;">
                        <label for="class_id">Class</label>
                        <select name="class_id" id="class_id" class="form-control mb-3">
                            <option value="" selected disabled>Select Class</option>

                        </select>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit" class="btn btn-primary" style="background-color:#222e3c; border-color: #222e3c;">Compare</button>
                    </div>
                </form>
            </div>
            <div id="pdf-content">
                @if (isset($data))
                <div class="container mt-3">
                    <canvas id="barChart" width="400" height="400"></canvas>
                </div>

                <div class="container mt-3">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">{{$user}}</th>
                                <th scope="col">Number of Logins</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $item)
                            <tr>
                                <td>{{ $item['name'] }}</td>
                                <td>{{ $item['logins'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
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
        const title = "Login Report";
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
            pdf.save("Login_Report.pdf");
        });
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const compareBy = document.getElementById("compare_by");
        const schoolFields = document.getElementById("school-fields");
        const classFields = document.getElementById("class-fields");
        const schoolSelect = document.getElementById("school_id");
        const classSelect = document.getElementById("class_id");

        function toggleFields() {
            const filterValue = compareBy.value;

            // Reset field visibility and requirement
            schoolFields.style.display = "none";
            classFields.style.display = "none";
            schoolSelect.required = false;
            classSelect.required = false;

            // Update fields based on selected filter
            if (filterValue === "students") {
                schoolFields.style.display = "block";
                classFields.style.display = "block";
                schoolSelect.required = true;
                classSelect.required = true;
            } else if (filterValue === "teachers") {
                schoolFields.style.display = "block";
                schoolSelect.required = true;
            }
        }

        // Attach event listener to the filter dropdown
        compareBy.addEventListener("change", toggleFields);

        // Run toggleFields on page load to handle pre-selected filter
        toggleFields();
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@if (isset($data))
<script>
    // Prepare data from PHP
    const data = @json($data);

    // Extract observer names and their login counts
    const labels = data.map(item => item.name); // Observer names
    const logins = data.map(item => item.logins); // Number of logins
    console.log(labels, logins);

    // Create the bar chart using Chart.js
    const ctx = document.getElementById('barChart').getContext('2d');
    const observersChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels, // x-axis labels
            datasets: [{
                label: 'Number of Logins',
                data: logins, // y-axis data
                backgroundColor: '#9e9fdc', // Bar color
                borderColor: '#9e9fdc', // Border color (optional)
                borderWidth: 1 // Bar border width
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true, // Show or hide legend
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        // Display full name in the tooltip
                        title: function(tooltipItems) {
                            const index = tooltipItems[0].dataIndex;
                            return labels[index]; // Full observer name
                        }
                    }
                }
            },
            scales: {
                x: { // x-axis configuration
                    title: {
                        display: false,
                        text: 'Observers'
                    },
                    ticks: {
                        autoSkip: false, // Ensure all labels are shown
                        callback: function(value, index) {
                            const fullName = labels[index]; // Get the full name
                            const truncatedName = fullName.length > 10 ?
                                fullName.slice(0, 10) + '...' :
                                fullName; // Truncate to 10 characters
                            return truncatedName; // Return truncated name for the x-axis
                        },
                        maxRotation: 45, // Rotate labels to prevent overlapping
                        minRotation: 0
                    }
                },
                y: { // y-axis configuration
                    title: {
                        display: true,
                        text: 'Number of Logins'
                    },
                    beginAtZero: true, // Start y-axis at zero
                    ticks: {
                        stepSize: 1, // Increment by 1
                        callback: function(value) {
                            return Number.isInteger(value) ? value : null; // Show only integers
                        }
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
            var compareBy = $('#compare_by').val();

            switch (compareBy) {
                case 'students':
                    var selectedClassId = "{{$request['class_id'] ?? '' }}";
                    getSchoolClasses(schoolId, selectedClassId);
                    break;
                default:
                    console.log('No matching report type selected.');
            }
        });

        $('#compare_by').change(function() {
            $('#school_id').trigger('change');
        });
        $('#school_id').trigger('change');
    });

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
                        '<option value="" selected disabled>Select Class</option>'
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
@if (!isset($data))
<script>
    const exportButton = document.getElementById('export-pdf');
    exportButton.disabled = true;
</script>
@endif


@endsection