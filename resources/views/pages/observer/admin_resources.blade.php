@extends('layouts.app')

@section('page_css')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

<style>
    body {
        background-color: #f8fafc;
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #17253e;

        border-bottom: 3px solid #525d6f;
        display: inline-block;
        margin-bottom: 1rem;
        padding-bottom: 0.3rem;
    }

    .stage-section {
        background: #fff;
        border-radius: 16px;
        padding: 0px;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
        margin-bottom: 40px;
        transition: 0.3s ease;
    }

    .stage-section:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    }

    .stage-header {
        background: linear-gradient(90deg, #17253e, #17253ebd);
        color: white;
        padding: 10px 18px;
        border-radius: 10px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .resource-card {
        background-color: #ffffff;
        border: none;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
    }

    .resource-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    .resource-card h5 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
    }

    .resource-card span.title {
        color: #525d6f;
        font-weight: 600;
    }

    .resource-type {
        margin-top: 4px;
        display: inline-block;
        background-color: #eef2ff;
        color: #4f46e5;
        padding: 3px 10px;
        font-size: 12px;
        border-radius: 6px;
        font-weight: 500;
    }

    .resource-list li {
        list-style: none;
        padding: 8px 0;
        border-bottom: 1px solid #f1f1f1;
    }

    .resource-list li:last-child {
        border-bottom: none;
    }

    .btn-outline-primary {
        border-radius: 8px;
    }

    .text-muted {
        font-style: italic;
    }

    /* Smooth fade for expand/collapse */
    ul[id^="theme-"],
    ul[id^="lesson-"] {
        transition: all 0.3s ease;
    }
</style>
@endsection

@section('title')
Admin Resources
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

<div class="container mt-4">
    <div class="p-3 text-[#667085] my-8" style="padding:20px">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Lesson Resources</h1>
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
        <!-- FILTER BAR -->
        <form method="GET" action="{{ route('observer.admin_resources') }}" class="mb-4">
            <div class="row g-2">

                <!-- Grade Filter -->
                <div class="col-md-3">
                    <select name="grade" class="form-select">
                        <option value="">All Grades</option>
                        @foreach ($stages as $stage)
                        <option value="{{ $stage->id }}" {{ $selectedGrade == $stage->id ? 'selected' : '' }}>
                            {{ $stage->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Theme Filter -->
                <div class="col-md-3">
                    <select name="theme" class="form-select">
                        <option value="">All Themes</option>
                        @foreach ($themes as $theme)
                        <option value="{{ $theme->id }}" {{ $selectedTheme == $theme->id ? 'selected' : '' }}>
                            {{ $theme->title }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Type Filter -->
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        @foreach ($types as $type)
                        <option value="{{ $type }}" {{ $selectedType == $type ? 'selected' : '' }}>
                            {{ strtoupper($type) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Button -->
                <div class="col-md-3">
                    <button type="submit" class="px-4 py-2 text-white rounded-md hover:bg-blue-700"
                        style="background-color:#667085;">Filter</button>

                    <a href="{{ route('observer.admin_resources') }}" class="btn btn-light">Reset</a>
                </div>




            </div>
        </form>


        <!-- LESSON RESOURCES -->
        <div class="row">
            @forelse ($groupedLessons as $lessonId => $resources)
            @php
            $lesson = $resources->first()->lesson ?? null;
            $chapter = $lesson?->chapter;
            $unit = $chapter?->unit;
            $material = $unit?->material;
            @endphp

            <div class="col-md-4 mb-4">
                <div class="resource-card" onclick="toggleDetails('lesson-{{ $lessonId }}')">


                    <!-- Lesson hierarchy -->
                    <div class="mt-2 text-muted" style="font-size: 0.9rem;">
                        <div><strong>Grade:</strong> {{ $material?->stage->name ?? 'N/A' }}</div>
                        <div><strong>Theme:</strong> {{ $material?->title ?? 'N/A' }}</div>
                        <div><strong>Unit:</strong> {{ $unit?->title ?? 'N/A' }}</div>
                        <div><strong>Chapter:</strong> {{ $chapter?->title ?? 'N/A' }}</div>
                        <div><strong>Lesson:</strong> {{ $lesson?->title  ?? 'N/A' }}</div>
                    </div>

                    <ul class="mt-3 resource-list p-0" id="lesson-{{ $lessonId }}" style="display: none;">
                        @foreach ($resources as $res)
                        <li class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">{{ $res->title }}</div>
                                <div class="resource-type">{{ strtoupper($res->type) }}</div>
                            </div>
                            <!-- <a href="{{ route('observer.resources.view', [$res->id, "lesson"]) }}" target="_blank"
                                class="btn btn-sm btn-outline-primary ms-3">
                                {{ strtoupper($res->type) === 'ZIP' ? 'Download' : 'View' }}
                            </a> -->
                            <a href="{{ asset($res->path) }}" target="_blank"
                                class="btn btn-sm btn-outline-primary ms-3">
                                {{ strtoupper($res->type) === 'ZIP' ? 'Download' : 'View' }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @empty
            <p class="text-muted">No lesson resources found.</p>
            @endforelse
        </div>


        <hr class="my-5">

        <!-- THEME RESOURCES -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Theme Resources</h1>
        </div>
        @forelse ($themeStages as $stageName => $themes)
        <div class="stage-section">
            <div class="stage-header mb-3">
                <i class="fa-solid fa-layer-group me-2"></i>{{ $stageName }}
            </div>

            <div class="row mt-3">
                @forelse ($themes as $theme)
                <div class="col-md-4 mb-4">
                    <div class="resource-card" onclick="toggleDetails('theme-{{ $theme->id }}')">
                        <h5><strong style="color: #17253e;">Theme:</strong> <span class="title">{{ $theme->title }}</span></h5>
                        <ul class="mt-3 resource-list p-0" id="theme-{{ $theme->id }}" style="display: none;">
                            @forelse ($theme->resources as $res)
                            <li class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">{{ $res->title }}</div>
                                    <div class="resource-type">{{ strtoupper($res->type) }}</div>
                                </div>
                                <!-- <a href="{{ route('observer.resources.view', [$res->id, "theme"]) }}" target="_blank"
                                    class="btn btn-sm btn-outline-primary ms-3">
                                    {{ strtoupper($res->type) === 'ZIP' ? 'Download' : 'View' }}
                                </a> -->
                                <a href="{{ asset($res->path) }}" target="_blank"
                                    class="btn btn-sm btn-outline-primary ms-3">
                                    {{ strtoupper($res->type) === 'ZIP' ? 'Download' : 'View' }}
                                </a>
                            </li>
                            @empty
                            <li class="text-muted">No resources found for this theme.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
                @empty
                <p class="text-muted">No themes found for this stage.</p>
                @endforelse
            </div>
        </div>
        @empty
        <p class="text-muted">No resources found.</p>
        @endforelse
    </div>
</div>
@endsection

@section('page_js')
<script>
    function toggleDetails(id) {
        const el = document.getElementById(id);
        el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
    }
</script>
@endsection