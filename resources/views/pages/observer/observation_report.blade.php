@extends('layouts.app')

@section('title')
    Observer Dashboard
@endsection

@php
    $menuItems = [
        ['label' => 'Observations', 'icon' => 'fi fi-rr-table-rows', 'route' => route('observer.dashboard')],
        ['label' => 'Observations Report', 'icon' => 'fi fi-rr-table-rows', 'route' => route('observer.report')],
    ];
@endphp

@section('sidebar')
    @include('components.sidebar', ['menuItems' => $menuItems])
@endsection

@section('content')
    <div id="content-to-export">
        <div class="p-3 text-[#667085] my-8" style="padding:20px">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Observations Report</h1>
                <div class="flex">
                    <button id="export-pdf" class="px-4 py-2 text-white rounded-md hover:bg-blue-700 ml-3"
                        style="background-color:#667085;">Export as PDF</button>
                    <button id="filter-modal-btn" class="px-4 py-2 text-white rounded-md hover:bg-blue-700 ml-3"
                        style="background-color:#667085;">
                        Filters
                    </button>
                </div>
            </div>


            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                    role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                    <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-green-700"
                        onclick="this.parentElement.remove();">
                        <svg class="fill-current h-6 w-6 text-green-700" role="button" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20">
                            <title>Close</title>
                            <path
                                d="M14.348 5.652a1 1 0 10-1.414-1.414L10 7.586 7.066 4.652a1 1 0 10-1.414 1.414L8.586 10l-2.93 2.934a1 1 0 101.414 1.414L10 12.414l2.934 2.934a1 1 0 101.414-1.414L11.414 10l2.934-2.934z" />
                        </svg>
                    </button>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                    <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-red-700"
                        onclick="this.parentElement.remove();">
                        <svg class="fill-current h-6 w-6 text-red-700" role="button" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20">
                            <title>Close</title>
                            <path
                                d="M14.348 5.652a1 1 0 10-1.414-1.414L10 7.586 7.066 4.652a1 1 0 10-1.414 1.414L8.586 10l-2.93 2.934a1 1 0 101.414 1.414L10 12.414l2.934 2.934a1 1 0 101.414-1.414L11.414 10l2.934-2.934z" />
                        </svg>
                    </button>
                </div>
            @endif
            @if (isset($data))
                <div class="flex mb-4" style="gap:10px; padding:10px; justify-content:space-between">
                    <div class="mb-4" style="gap:10px; padding:10px; max-width:75%; flex:1">
                        <div class="questions mb-3" style="max-width: 100%;">
                            @foreach ($data as $header)
                                <h1 class="text-lg font-semibold text-[#667085] mb-4" style="font-size:20px">
                                    {{ $header['name'] }}</h1>
                                @foreach ($header['questions'] as $question)
                                    <h3 class="text-base font-medium text-gray-700 mb-2" style="font-size:16px">-
                                        {{ $question['name'] }}</h3>

                                    <div class="rating-bar" style="margin-bottom: 10px;">
                                        <div
                                            style="position: relative; background-color: #e5e7eb; border-radius: 8px; height: 20px; width: 100%;">
                                            <div
                                                style="background-color: #667085; height: 100%; border-radius: 8px; width: {{ ($question['avg_rating'] / $question['max_rating']) * 100 }}%;">
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-600 mt-2">Average Rating: {{ $question['avg_rating'] }}
                                            / {{ $question['max_rating'] }}</p>
                                    </div>
                                @endforeach
                                <br>
                            @endforeach
                            {{-- @if (isset($overallComments) && count($overallComments) > 0)
                                <div class="comments-section bg-gray-100 p-4 rounded-lg shadow-md mt-6">
                                    <h2 class="text-xl font-semibold text-gray-800">Overall Comments</h2>
                                    <ul class="list-disc pl-4 mt-2">
                                        @foreach ($overallComments as $comment)
                                            <li class="text-gray-700">{{ $comment }} -- </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif --}}
                            @if (isset($overallComments) && count($overallComments) > 0)
                                <div class="comments-section bg-gray-100 p-4 rounded-lg shadow-md mt-6">
                                    <h2 class="text-xl font-semibold text-gray-800">Overall Comments</h2>
                                    <ul class="list-disc pl-4 mt-2">
                                        @foreach ($overallComments as $comment)
                                            @php
                                                $teacherName =
                                                    App\Models\Teacher::find($comment->teacher_id)?->name ??
                                                    'Unknown Teacher';
                                            @endphp
                                            <li class="text-gray-700">
                                                <strong>{{ $teacherName }}:</strong> {{ $comment->note }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="rounded-lg" style="width: 21%;">
                        <div class="mb-3">
                            <label for="observation_name" class="block text-sm font-medium text-gray-700">Observer
                                Name</label>
                            <input class="w-full p-2 border border-gray-300 rounded" type="text" style="font-size:14px"
                                name="observation_name" value="{{ Auth::guard('observer')->user()->name }}" disabled
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="teacher" class="block text-sm font-medium text-gray-700">Teacher</label>
                            <input class="w-full p-2 border border-gray-300 rounded" type="text" name="observation_name"
                                style="font-size:14px"
                                value="{{ request('teacher_id') ? App\Models\Teacher::find(request('teacher_id'))->name : 'All Teachers' }}"
                                disabled required>
                        </div>
                        <div class="mb-3">
                            <label for="school" class="block text-sm font-medium text-gray-700">School</label>
                            <div class="w-full p-2 border border-gray-300 rounded" style="font-size:14px">
                                @if (request('school_id'))
                                    @php
                                        $filteredSchools = App\Models\School::whereIn(
                                            'id',
                                            request('school_id'),
                                        )->pluck('name');
                                    @endphp
                                    @foreach ($filteredSchools as $schoolName)
                                        <div>{{ $schoolName }}</div>
                                    @endforeach
                                @else
                                    <div>All Schools</div>
                                @endif
                            </div>
                        </div>

                    </div>

                </div>
            @else
                <p>No Observation Questions Found</p>
            @endif
        </div>
    </div>
    <div id="filter-modal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full sm:w-3/4 md:w-1/2 lg:w-1/3 max-h-[90vh] overflow-y-auto">
            <h2 class="text-xl font-bold mb-4">Filters</h2>
            <form id="filter-form-modal" action="{{ route('observer.report') }}" method="GET">
                <div class="mb-4">
                    <label for="teacher_id_modal" class="block text-sm font-medium text-gray-700">Teacher</label>
                    <select name="teacher_id" id="teacher_id_modal" class="w-full p-2 border border-gray-300 rounded">
                        <option value="">All Teachers</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}"
                                {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="school_id" class="block text-sm font-medium text-gray-700">School</label>
                    <select name="school_id[]" id="school_id" class="w-full p-2 border border-gray-300 rounded" multiple>
                        <!-- <option value="" disabled selected>Select Schools</option> -->
                        @foreach ($schools as $school)
                            <option value="{{ $school->id }}"
                                {{ is_array(request('school_id')) && in_array($school->id, request('school_id')) ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                    <select name="city[]" id="city" class="w-full p-2 border border-gray-300 rounded" multiple>
                        <!-- <option value="" disabled selected>Select Schools</option> -->
                        @foreach ($cities as $city)
                            <option value="{{ $city }}"
                                {{ is_array(request('city')) && in_array($city, request('city')) ? 'selected' : '' }}>
                                {{ $city }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label for="school_id" class="block text-sm font-medium text-gray-700">Stage</label>
                    <select name="stage_id" id="stage_id" class="w-full p-2 border border-gray-300 rounded">
                        <option value="">All Stages</option>
                        @foreach ($stages as $stage)
                            <option value="{{ $stage->id }}"
                                {{ request('stage_id') == $stage->id ? 'selected' : '' }}>
                                {{ $stage->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="lesson_segment_filter" class="block text-sm font-medium text-gray-700">Lesson
                        Segment</label>
                    <select class="w-full p-2 border border-gray-300 rounded" name="lesson_segment_filter"
                        id="lesson_segment_filter">
                        <option value="">All</option>
                        <option value="Beginning" {{ request('lesson_segment_filter') == 'Beginning' ? 'selected' : '' }}>
                            Beginning</option>
                        <option value="Middle" {{ request('lesson_segment_filter') == 'Middle' ? 'selected' : '' }}>Middle
                        </option>
                        <option value="End" {{ request('lesson_segment_filter') == 'End' ? 'selected' : '' }}>End
                        </option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="from_date" class="block text-sm font-medium text-gray-700">Date From</label>
                    <input type="date" name="from_date" id="from_date"
                        class="w-full p-2 border border-gray-300 rounded" value="{{ request('from_date') }}">
                </div>
                <div class="mb-4">
                    <label for="to_date" class="block text-sm font-medium text-gray-700">Date To</label>
                    <input type="date" name="to_date" id="to_date"
                        class="w-full p-2 border border-gray-300 rounded" value="{{ request('to_date') }}">
                </div>
                <input type="checkbox" name="include_comments" value="1"
                    {{ request('include_comments') ? 'checked' : '' }}> Includes Comments
                <div class="flex justify-end">
                    <button type="button" id="close-modal-btn"
                        class="px-4 py-2 bg-gray-500 text-white rounded mr-2">Close</button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded"
                        style="background-color: rgb(63, 134, 249)">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page_js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>


    <script>
        // Modal toggle
        document.getElementById('filter-modal-btn').addEventListener('click', function() {
            document.getElementById('filter-modal').classList.remove('hidden');
        });

        document.getElementById('close-modal-btn').addEventListener('click', function() {
            document.getElementById('filter-modal').classList.add('hidden');
        });
    </script>

    <script>
        document.getElementById('export-pdf').addEventListener('click', function() {
            // Get the content for export
            const originalContent = document.getElementById('content-to-export');

            // Clone the original content
            const clonedContent = originalContent.cloneNode(true);

            // Remove the filter button and export button from the cloned content
            const filterButton = clonedContent.querySelector('#filter-modal-btn');
            const exportButton = clonedContent.querySelector('#export-pdf');
            if (filterButton) {
                filterButton.remove();
            }
            if (exportButton) {
                exportButton.remove();
            }

            // Create a temporary container to hold the cloned content
            const tempContainer = document.createElement('div');
            tempContainer.style.display = 'none'; // Hide the container
            tempContainer.appendChild(clonedContent);
            document.body.appendChild(tempContainer);

            // Define options for html2pdf
            const options = {
                margin: [0.5, 0.5, 0.5, 0.5], // Decrease margins to match the website
                filename: 'Observations_Report.pdf',
                pagebreak: {
                    mode: ['avoid-all', 'css', 'legacy'] // Avoid breaking inside elements
                },
                html2canvas: {
                    scale: 2, // Increase scale for better text quality
                    useCORS: true // Handle cross-origin images and fonts
                },
                jsPDF: {
                    unit: 'in',
                    format: 'letter',
                    orientation: 'portrait'
                }
            };

            // Generate the PDF using html2pdf
            html2pdf()
                .set(options)
                .from(clonedContent)
                .toPdf() // Convert to PDF directly
                .save() // Save the file
                .then(() => {
                    // Clean up the temporary container after PDF generation
                    document.body.removeChild(tempContainer);
                })
                .catch((err) => {
                    console.error('PDF generation failed:', err);
                    // Ensure the temporary container is removed even if there's an error
                    document.body.removeChild(tempContainer);
                });
        });
    </script>


    <script>
        document.getElementById('export-excel').addEventListener('click', function() {
            // Create an array to store the data
            const data = [];

            // Select the content to export
            const content = document.getElementById('content-to-export');

            // Loop through the headers and questions to build the data
            const headers = content.querySelectorAll('.questions h1');
            headers.forEach(header => {
                const headerText = header.textContent.trim();
                const questions = header.nextElementSibling.querySelectorAll('h3');

                // Push header and questions into the data array
                data.push([headerText]); // Add header as a row
                questions.forEach(question => {
                    const questionText = question.textContent.trim();
                    const avgRating = question.nextElementSibling.querySelector('.text-sm')
                        .textContent.trim();
                    data.push([questionText, avgRating]); // Add question and rating
                });

                data.push([]); // Add an empty row for spacing
            });

            // Convert data array to a worksheet
            const worksheet = XLSX.utils.aoa_to_sheet(data);

            // Create a new workbook and append the worksheet
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, 'Observations Report');

            // Export the workbook as an Excel file
            XLSX.writeFile(workbook, 'Observations_Report.xlsx');
        });
    </script>
    @if (!isset($data))
        <script>
            const exportButton = document.getElementById('export-pdf');
            const filterButton = document.getElementById('filter-modal-btn');
            exportButton.disabled = true;
            filterButton.disabled = true;
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    @endif
@endsection
