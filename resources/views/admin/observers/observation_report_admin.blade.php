@extends('admin.layouts.layout')

@section('content')
<div class="wrapper">
    @include('admin.layouts.sidebar')

    <div class="main">
        @include('admin.layouts.navbar')

        <main class="content">
            <div class="container-fluid p-0">

                @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                <div id="content-to-export" style="margin-top: -20px;">
                    <div class="p-3 text-secondary my-4" style="padding: 20px;">
                        <div class="d-flex" style="justify-content: space-between;">
                            <h1 class="">Observations Report</h1>
                            <div class="d-flex">
                                <button id="export-pdf" class="btn btn-primary me-2">Export as PDF</button>
                                <button id="filter-modal-btn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filter-modal">Filters</button>
                            </div>
                        </div>
                        @if (isset($data))
                        <div class="d-flex mb-4" style="gap: 10px; padding: 10px; justify-content: space-between;">
                            <div class="flex-grow-1" style="max-width: 75%;">
                                <div class="questions mb-3" style="max-width: 100%;">
                                    @foreach ($data as $header)
                                    <h2 class="h5 fw-semibold text-secondary mb-3" style="font-size:18px">{{ $header['name'] }}</h2>
                                    @foreach ($header['questions'] as $question)
                                    <h3 class="h6 fw-medium text-muted mb-2" style="font-size:14px">- {{ $question['name'] }}</h3>
                                    <div class="progress mb-2">
                                        <div class="progress-bar bg-secondary" role="progressbar"
                                            style="width: {{ ($question['avg_rating'] / $question['max_rating']) * 100 }}%;"
                                            aria-valuenow="{{ $question['avg_rating'] }}"
                                            aria-valuemin="0"
                                            aria-valuemax="{{ $question['max_rating'] }}"></div>
                                    </div>
                                    <p class="text-muted small">Average Rating: {{ $question['avg_rating'] }} / {{ $question['max_rating'] }}</p>
                                    @endforeach
                                    <br>
                                    @endforeach
                                </div>
                            </div>

                            <div class="rounded">
                                <div class="mb-3">
                                    <label for="teacher" class="form-label">Observer</label>
                                    <input type="text" name="observation_name" class="form-control" style="font-size: 14px;"
                                        value="{{ request('observer_id') ? App\Models\Observer::find(request('observer_id'))->name : 'All Observers' }}"
                                        disabled required>
                                </div>
                                <div class="mb-3">
                                    <label for="teacher" class="form-label">Teacher</label>
                                    <input type="text" name="observation_name" class="form-control" style="font-size: 14px;"
                                        value="{{ request('teacher_id') ? App\Models\Teacher::find(request('teacher_id'))->name : 'All Teachers' }}"
                                        disabled required>
                                </div>
                                <div class="mb-3">
                                    <label for="school" class="form-label">School</label>
                                    <div class="form-control" style="font-size: 14px;">
                                        @if(request('school_id'))
                                        @php
                                        $filteredSchools = App\Models\School::whereIn('id', request('school_id'))->pluck('name');
                                        @endphp
                                        @foreach($filteredSchools as $schoolName)
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

                <div id="filter-modal" class="modal fade" tabindex="-1" aria-labelledby="filter-modal-label" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="filter-modal-label">Filters</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="filter-form-modal" action="{{ route('observers.obsReport') }}" method="GET">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="observer_id" class="form-label">Observer</label>
                                        <select name="observer_id" id="observer_id" class="form-select">
                                            <option value="">All Observers</option>
                                            @foreach ($observers as $observer)
                                            <option value="{{ $observer->id }}" {{ request('observer_id') == $observer->id ? 'selected' : '' }}>
                                                {{ $observer->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="teacher_id_modal" class="form-label">Teacher</label>
                                        <select name="teacher_id" id="teacher_id_modal" class="form-select">
                                            <option value="">All Teachers</option>
                                            @foreach ($teachers as $teacher)
                                            <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                                {{ $teacher->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="school_id" class="form-label">School</label>
                                        <select name="school_id[]" id="school_id" class="form-select" multiple>
                                            @foreach ($schools as $school)
                                            <option value="{{ $school->id }}" {{ is_array(request('school_id')) && in_array($school->id, request('school_id')) ? 'selected' : '' }}>
                                                {{ $school->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="city" class="form-label">City</label>
                                        <select name="city[]" id="city" class="form-select" multiple>
                                            @foreach ($cities as $city)
                                            <option value="{{ $city }}" {{ is_array(request('city')) && in_array($city, request('city')) ? 'selected' : '' }}>
                                                {{ $city }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="stage_id" class="form-label">Stage</label>
                                        <select name="stage_id" id="stage_id" class="form-select">
                                            <option value="">All Stages</option>
                                            @foreach ($stages as $stage)
                                            <option value="{{ $stage->id }}" {{ request('stage_id') == $stage->id ? 'selected' : '' }}>
                                                {{ $stage->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="lesson_segment_filter" class="form-label">Lesson Segment</label>
                                        <select class="form-select" name="lesson_segment_filter" id="lesson_segment_filter">
                                            <option value="">All</option>
                                            <option value="Beginning" {{ request('lesson_segment_filter') == 'Beginning' ? 'selected' : '' }}>Beginning</option>
                                            <option value="Middle" {{ request('lesson_segment_filter') == 'Middle' ? 'selected' : '' }}>Middle</option>
                                            <option value="End" {{ request('lesson_segment_filter') == 'End' ? 'selected' : '' }}>End</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="from_date" class="form-label">Date From</label>
                                        <input type="date" name="from_date" id="from_date" class="form-control" value="{{ request('from_date') }}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="to_date" class="form-label">Date To</label>
                                        <input type="date" name="to_date" id="to_date" class="form-control" value="{{ request('to_date') }}">
                                    </div>
                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" name="include_comments" id="include_comments" value="1" {{ request('include_comments') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="include_comments">Include Comments</label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

        </main>

    </div>
</div>

@endsection
@section('page_js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
    document.getElementById('export-pdf').addEventListener('click', function() {
        const originalContent = document.getElementById('content-to-export');
        const clonedContent = originalContent.cloneNode(true);
        const filterButton = clonedContent.querySelector('#filter-modal-btn');
        const exportButton = clonedContent.querySelector('#export-pdf');
        if (filterButton) filterButton.remove();
        if (exportButton) exportButton.remove();

        const tempContainer = document.createElement('div');
        tempContainer.style.display = 'none';
        tempContainer.appendChild(clonedContent);
        document.body.appendChild(tempContainer);

        const options = {
            margin: [0.5, 0.5, 0.5, 0.5],
            filename: 'Observations_Report.pdf',
            pagebreak: {
                mode: ['avoid-all', 'css', 'legacy']
            },
            html2canvas: {
                scale: 2,
                useCORS: true
            },
            jsPDF: {
                unit: 'in',
                format: 'letter',
                orientation: 'portrait'
            }
        };

        html2pdf()
            .set(options)
            .from(clonedContent)
            .toPdf()
            .save()
            .then(() => document.body.removeChild(tempContainer))
            .catch((err) => {
                console.error('PDF generation failed:', err);
                document.body.removeChild(tempContainer);
            });
    });
</script>

<script>
    document.getElementById('export-excel').addEventListener('click', function() {
        const data = [];
        const content = document.getElementById('content-to-export');
        const headers = content.querySelectorAll('.questions h1');
        headers.forEach(header => {
            const headerText = header.textContent.trim();
            const questions = header.nextElementSibling.querySelectorAll('h3');
            data.push([headerText]);
            questions.forEach(question => {
                const questionText = question.textContent.trim();
                const avgRating = question.nextElementSibling.querySelector('.text-sm').textContent.trim();
                data.push([questionText, avgRating]);
            });
            data.push([]);
        });

        const worksheet = XLSX.utils.aoa_to_sheet(data);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Observations Report');
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