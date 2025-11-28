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

    .resource-card h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #17253e;
    }

    .resource-card p {
        color: #525d6f;
        font-size: 0.9rem;
    }

    .resource-card .badge {
        font-size: 0.75rem;
        border-radius: 6px;
    }

    .btn-outline-primary,
    .btn-outline-info,
    .btn-primary {
        border-radius: 8px;
    }

    .text-muted {
        font-style: italic;
    }

    .filter-form select,
    .filter-form button {
        max-width: 250px;
        border-radius: 8px;
    }

    .actions .btn {
        border-radius: 8px;
    }

    .modal-header h5 {
        font-weight: bold;
        color: #17253e;
    }

    hr {
        border-color: #e0e0e0;
    }
</style>
@endsection

@section('title')
Teacher Resources
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
            <h1 class="text-2xl font-bold">Teacher Resources</h1>
            <div class="flex" style="justify-content:center; align-items:center;gap:15px">
                <a href="{{ route('observer.admin_resources') }}"
                    class="px-4 py-2 text-white rounded-md hover:bg-blue-700"
                    style="background-color:#667085; display:block; text-decoration: none;">
                    Admin Resources
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

        <form method="GET" action="{{ route('observer.teacherResources') }}" class="filter-form mb-4 d-flex flex-wrap gap-2">

            {{-- Grade Filter --}}
            <select name="grade" class="form-select">
                <option value="">All Grades</option>
                @foreach ($stages as $stage)
                <option value="{{ $stage->id }}" {{ request('grade') == $stage->id ? 'selected' : '' }}>
                    {{ $stage->name }}
                </option>
                @endforeach
            </select>

            {{-- Instructor Filter --}}
            <select name="instructor" class="form-select">
                <option value="">All Instructors</option>
                @foreach ($instructors as $teacher)
                <option value="{{ $teacher->id }}" {{ request('instructor') == $teacher->id ? 'selected' : '' }}>
                    {{ $teacher->name }} ({{ $teacher->school ? $teacher->school->name : 'No School' }})
                </option>
                @endforeach
            </select>

            {{-- Resource Type Filter --}}
            <select name="type" class="form-select">
                <option value="">All Types</option>
                @foreach ($types as $type)
                <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                    {{ ucfirst($type) }}
                </option>
                @endforeach
            </select>


            {{-- Theme Filter --}}
            <select name="theme" class="form-select">
                <option value="">All Themes</option>
                @foreach ($themes as $theme)
                <option value="{{ $theme->id }}" {{ request('theme') == $theme->id ? 'selected' : '' }}>
                    {{ $theme->title }}
                </option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 text-white rounded-md hover:bg-blue-700"
                style="background-color:#667085;">Filter</button>

            {{-- Reset button --}}
            <a href="{{ route('observer.teacherResources') }}" class="btn btn-light">Reset</a>
        </form>

        <div class="row">
            {{-- GROUPED VIEW: Stage → Theme → Resources --}}
            @forelse ($groupedResources as $stageName => $themes)
            <div class="stage-section mb-4">
                <div class="stage-header mb-2">
                    <i class="fa-solid fa-layer-group me-2"></i>{{ $stageName }}
                </div>

                <div class="row mt-3">
                    @forelse ($themes as $themeName => $items)
                    <div class="col-md-12 mb-4">
                        <div class="resource-card" onclick="toggleDetails('theme-{{ Str::slug($stageName) }}-{{ Str::slug($themeName) }}')">
                            <h5><strong style="color: #17253e;">Theme:</strong>
                                <span class="title">{{ $themeName }}</span>
                            </h5>

                            <div class="mt-3" id="theme-{{ Str::slug($stageName) }}-{{ Str::slug($themeName) }}" style="display: none;">
                                <div class="row">
                                    @foreach ($items as $res)
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 shadow-sm p-2">

                                            {{-- IMAGE --}}
                                            @if ($res->image)
                                            <img src="{{ asset($res->image) }}" class="card-img-top rounded" style="height:140px; object-fit:cover;">
                                            @else
                                            <div class="d-flex justify-content-center align-items-center bg-light rounded"
                                                style="height:140px; font-size:14px; color:#888;">
                                                No Image
                                            </div>
                                            @endif

                                            <div class="card-body d-flex flex-column">
                                                <h6 class="fw-bold">{{ $res->name }}</h6>
                                                @if($res->teacher)
                                                <p class="text-muted small mb-1">
                                                    <i>By {{ $res->teacher->name }}</i>
                                                </p>
                                                @endif
                                                <p class="text-muted small mb-2">
                                                    {{ $res->video_url ? 'VIDEO' : strtoupper(pathinfo($res->file_path, PATHINFO_EXTENSION)) }}
                                                </p>

                                                <div class="mt-auto d-flex gap-2">
                                                    <a href="{{ $res->video_url ?: route('observer.resources.view', [$res->id, "teacher"]) }}" target="_blank"
                                                        class="btn btn-sm btn-outline-primary flex-grow-1"
                                                        title="{{ $res->video_url ? 'Watch Video' : 'View File' }}">
                                                        <i class="{{ $res->video_url ? 'fas fa-play' : 'fas fa-eye' }}"></i>
                                                    </a>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted">No themes found for this grade.</p>
                    @endforelse
                </div>
            </div>
            @empty
            <p class="text-center mt-4 text-muted">No resources available.</p>
            @endforelse

        </div>
    </div>
</div>

@endsection

@section('page_js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
    function toggleDetails(id) {
        const el = document.getElementById(id);
        el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
    }
</script>

@endsection