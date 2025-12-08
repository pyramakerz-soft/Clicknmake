@extends('layouts.app')

@section('title')
    Observer Dashboard
@endsection

@php
    $menuItems = [
        ['label' => 'Observations', 'icon' => 'fi fi-rr-table-rows', 'route' => route('observer.dashboard')],
        ['label' => 'Observations Report', 'icon' => 'fi fi-rr-table-rows', 'route' => route('observer.report')],
        ['label' => 'Resources', 'icon' => 'fi fi-rr-table-rows', 'route' => route('observer.teacherResources')],
    ];
@endphp

@section('sidebar')
    @include('components.sidebar', ['menuItems' => $menuItems])
@endsection

@section('content')
    <div class="p-3 text-[#667085] my-8" style="padding:20px">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Observations</h1>
            <div class="flex" style="justify-content:center; align-items:center;gap:15px">
                <!-- Trigger for Filter Modal -->
                <button id="filter-modal-btn" class="px-4 py-2 text-white rounded-md hover:bg-blue-700"
                    style="background-color:#667085;">
                    Filters
                </button>
                <!-- <button id="export-all-pdf" class="px-4 py-2 text-white rounded-md hover:bg-blue-700"
                    style="background-color:#667085;">
                    Export All Observations
                </button> -->
                <a href="{{ route('observer.observation.create') }}"
                    class="px-4 py-2 text-white rounded-md hover:bg-blue-700"
                    style="background-color:#667085; display:block">
                    Add New Observation
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
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

        <div class="overflow-x-auto w-full">
            <table class="w-full border border-gray-300 text-sm text-left">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-4 py-2">Template</th>
                        <th class="border px-4 py-2">Observer Name</th>
                        <!-- <th class="border px-4 py-2">Observer Name</th> -->
                        <th class="border px-4 py-2">Teacher Name</th>
                        <th class="border px-4 py-2">Co-Teacher Name</th>
                        <th class="border px-4 py-2">School</th>
                        <th class="border px-4 py-2">City</th>
                        <th class="border px-4 py-2">Date of Observation</th>
                        <th class="border px-4 py-2" style="width: 15%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($observations as $observation)
                        <tr>
                            @php
                                $school = App\Models\School::find($observation->school_id);
                            @endphp
                            <td class="border px-4 py-2">{{ $observation->template ? $observation->template->name : '-' }}</td>
                            <td class="border px-4 py-2">{{ App\Models\Observer::find($observation->observer_id)->name }}</td>
                            <td class="border px-4 py-2">{{ $observation->teacher_name }}</td>
                            <td class="border px-4 py-2">{{ $observation->coteacher_name }}</td>
                            <td class="border px-4 py-2">{{ $school->name }}</td>
                            <td class="border px-4 py-2">{{ $school->city }}</td>
                            <td class="border px-4 py-2">{{ $observation->activity }}</td>
                            <td class="border px-4 py-2"
                                style="    display: flex
;
    flex-direction: row;
    justify-content: space-between; ">
                                <!-- <button
                                    class="text-white font-medium py-2 px-4 download-pdf rounded shadow focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-opacity-50"
                                    style="background-color:#323d53; margin-right:5px;" data-id="{{ $observation->id }}"
                                    title="Download PDF">
                                    <i class="fa-solid fa-download text-white"></i>
                                </button> -->
                                <a href="{{ route('observation.view', $observation->id) }}"
                                    class="text-white font-medium py-2 px-4 rounded shadow focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-opacity-50"
                                    style="background-color:#667085; margin-right:5px;">
                                    <i class="fa-solid fa-eye"></i>
                                </a>

                                <form action="{{ route('observation.destroy', $observation->id) }}" method="POST"
                                    style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="btn btn-danger bg-danger bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded shadow focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-opacity-50"
                                        style="background-color: #f00"
                                        onclick="return confirm('Are you sure you want to delete this Observation?');">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="border px-4 py-2 text-center">No observations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    <!-- Filter Modal -->
    <div id="filter-modal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full sm:w-3/4 md:w-1/2 lg:w-1/3 max-h-[90vh] overflow-y-auto">
            <h2 class="text-xl font-bold mb-4">Filters</h2>
            <form id="filter-form-modal" action="{{ route('observer.dashboard') }}" method="GET">
                <div class="mb-4">
                    <label for="template_id_modal" class="block text-sm font-medium text-gray-700">Template</label>
                    <select name="template_id" id="template_id_modal" class="w-full p-2 border border-gray-300 rounded">
                        <option value="">All Template</option>
                        @foreach ($templates as $template)
                            <option value="{{ $template->id }}"
                                {{ request('template_id') == $template->id ? 'selected' : '' }}>
                                {{ $template->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
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
                        style="background-color: rgb(59 130 246 / var(--tw-bg-opacity));
">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page_js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        // document.getElementById('export-all-pdf').addEventListener('click', function() {
        //     fetch('{{ route('observer.observations.export') }}')
        //         .then(response => response.json())
        //         .then(data => {
        //             if (data.length === 0) {
        //                 alert("No observations available to export.");
        //                 return;
        //             }

        //             const pdfContainer = document.createElement('div');
        //             pdfContainer.innerHTML = `
        //         <h1 style="text-align:center; font-size:18px; font-weight:bold; margin-bottom:10px;">Observation Reports</h1>
        //     `;

        //             data.forEach(observation => {
        //                 pdfContainer.innerHTML += `
        //         <div style="border:1px solid #ddd; padding:15px; margin-bottom:10px; border-radius:5px;">
        //             <h3 style="color:#333; font-size:16px; font-weight:bold;">${observation.name}</h3>
        //             <p><strong>Teacher:</strong> ${observation.teacher_name}</p>
        //             <p><strong>Co-Teacher:</strong> ${observation.coteacher_name}</p>
        //             <p><strong>School:</strong> ${observation.school}</p>
        //             <p><strong>City:</strong> ${observation.city}</p>
        //             <p><strong>Stage:</strong> ${observation.stage}</p>
        //             <p><strong>Subject:</strong> ${observation.subject}</p>
        //             <p><strong>Date:</strong> ${observation.activity}</p>
        //             <p><strong>Comments:</strong> ${observation.note || 'No comments provided'}</p>

        //             <h4 style="font-size:14px; margin-top:10px; font-weight:bold;">Ratings</h4>
        //             <table style="width:100%; border-collapse:collapse; margin-top:10px;">
        //                 <thead>
        //                     <tr style="background:#f4f4f4;">
        //                         <th style="border:1px solid #ddd; padding:6px; text-align:left;">Question</th>
        //                         <th style="border:1px solid #ddd; padding:6px; text-align:center;">Average Rating</th>
        //                         <th style="border:1px solid #ddd; padding:6px; text-align:center;">Max Rating</th>
        //                     </tr>
        //                 </thead>
        //                 <tbody>
        //                     ${observation.questions.map(q => `
        //                                                         <tr>
        //                                                             <td style="border:1px solid #ddd; padding:6px;">${q.name}</td>
        //                                                             <td style="border:1px solid #ddd; padding:6px; text-align:center;">${q.avg_rating}</td>
        //                                                             <td style="border:1px solid #ddd; padding:6px; text-align:center;">${q.max_rating}</td>
        //                                                         </tr>
        //                                                     `).join('')}
        //                 </tbody>
        //             </table>
        //         </div>
        //         `;
        //             });

        //             const options = {
        //                 margin: 10,
        //                 filename: 'Observations_Report.pdf',
        //                 pagebreak: {
        //                     mode: ['avoid-all', 'css', 'legacy']
        //                 },
        //                 html2canvas: {
        //                     scale: 2,
        //                     useCORS: true
        //                 },
        //                 jsPDF: {
        //                     unit: 'mm',
        //                     format: 'a4',
        //                     orientation: 'portrait'
        //                 }
        //             };

        //             html2pdf().set(options).from(pdfContainer).toPdf().save();
        //         })
        //         .catch(err => console.error('Error fetching observation details:', err));
        // });

        // Modal toggle
        document.getElementById('filter-modal-btn').addEventListener('click', function() {
            document.getElementById('filter-modal').classList.remove('hidden');
        });

        document.getElementById('close-modal-btn').addEventListener('click', function() {
            document.getElementById('filter-modal').classList.add('hidden');
        });
    </script>
    <!-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.download-pdf').forEach(button => {
                button.addEventListener('click', function() {
                    let observationId = this.getAttribute('data-id');

                    fetch(`{{ route('observer.observation.export', ':id') }}`.replace(':id',
                            observationId))
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(
                                    `Error ${response.status}: Observation not found.`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.error) {
                                alert(data.error);
                                return;
                            }

                            const pdfContainer = document.createElement('div');
                            pdfContainer.innerHTML = `
                    <h1 style="text-align:center; font-size:18px; font-weight:bold; margin-bottom:10px;">
                        Observation Report
                    </h1>

                    <div style="border:1px solid #ddd; padding:15px; margin-bottom:10px; border-radius:5px;">
                        <h3 style="color:#333; font-size:16px; font-weight:bold;">${data.name}</h3>
                        <p><strong>Teacher:</strong> ${data.teacher_name}</p>
                        <p><strong>Co-Teacher:</strong> ${data.coteacher_name}</p>
                        <p><strong>School:</strong> ${data.school}</p>
                        <p><strong>City:</strong> ${data.city}</p>
                        <p><strong>Stage:</strong> ${data.stage}</p>
                        <p><strong>Subject:</strong> ${data.subject}</p>
                        <p><strong>Date:</strong> ${data.activity}</p>
                        <p><strong>Comments:</strong> ${data.note || 'No comments provided'}</p>

                        <h4 style="font-size:14px; margin-top:10px; font-weight:bold;">Ratings</h4>
                        <table style="width:100%; border-collapse:collapse; margin-top:10px;">
                            <thead>
                                <tr style="background:#f4f4f4;">
                                    <th style="border:1px solid #ddd; padding:6px; text-align:left;">Question</th>
                                    <th style="border:1px solid #ddd; padding:6px; text-align:center;">Average Rating</th>
                                    <th style="border:1px solid #ddd; padding:6px; text-align:center;">Max Rating</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.questions.map(q => `
                                                <tr>
                                                    <td style="border:1px solid #ddd; padding:6px;">${q.name}</td>
                                                    <td style="border:1px solid #ddd; padding:6px; text-align:center;">${q.avg_rating}</td>
                                                    <td style="border:1px solid #ddd; padding:6px; text-align:center;">${q.max_rating}</td>
                                                </tr>
                                            `).join('')}
                            </tbody>
                        </table>
                    </div>
                    `;

                            const options = {
                                margin: 10,
                                filename: `Observation_${data.id}.pdf`,
                                pagebreak: {
                                    mode: ['avoid-all', 'css', 'legacy']
                                },
                                html2canvas: {
                                    scale: 2,
                                    useCORS: true
                                },
                                jsPDF: {
                                    unit: 'mm',
                                    format: 'a4',
                                    orientation: 'portrait'
                                }
                            };

                            html2pdf().set(options).from(pdfContainer).toPdf().save();
                        })
                        .catch(err => console.error('Error fetching observation details:', err));
                });
            });
        });
    </script> -->
@endsection
