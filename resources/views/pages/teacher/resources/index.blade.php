@php
$userAuth = auth()->guard('teacher')->user();
@endphp

@extends('layouts.app')

@section('title', 'Resources')

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

@section('sidebar')
@include('components.sidebar', [
'menuItems' => [
['label' => 'Dashboard', 'icon' => 'fi fi-rr-table-rows', 'route' => route('teacher.dashboard')],
['label' => 'Resources', 'icon' => 'fi fi-rr-table-rows', 'route' => route('teacher.resources.index')],
['label' => 'Ticket', 'icon' => 'fa-solid fa-ticket', 'route' => route('teacher.tickets.index')],
['label' => 'Chat', 'icon' => 'fa-solid fa-message', 'route' => route('chat.all')],
],
])
@endsection

@section('content')
@include('components.profile')

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="section-title">My Resources</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('teacher.resources.admin') }}" class="btn btn-outline-primary ">
                <i class="fa-solid fa-folder-open me-1"></i> View Admin Resources
            </a>
            <a href="{{ route('teacher.resources.create') }}" class="btn btn-outline-primary">
                <i class="fa-solid fa-plus me-1"></i> Add Resource
            </a>
        </div>
    </div>

    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('teacher.resources.index') }}" class="filter-form mb-4 d-flex gap-2">
        <select id="grade" name="grade" class="form-select">
            <option value="">All Grades</option>
            @foreach ($stages as $stage)
            <option value="{{ $stage->id }}" {{ request('grade') == $stage->id ? 'selected' : '' }}>
                {{ $stage->name }}
            </option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
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
                                            <p class="text-muted small mb-2">
                                                {{ $res->video_url ? 'VIDEO' : strtoupper(pathinfo($res->file_path, PATHINFO_EXTENSION)) }}
                                            </p>

                                            <div class="mt-auto d-flex gap-2">
                                                <a href="{{ $res->video_url ?: asset($res->file_path) }}" target="_blank"
                                                    class="btn btn-sm btn-outline-primary flex-grow-1"
                                                    title="{{ $res->video_url ? 'Watch Video' : 'View File' }}">
                                                    <i class="{{ $res->video_url ? 'fas fa-play' : 'fas fa-eye' }}"></i>
                                                </a>

                                                <a href="{{ route('teacher.resources.edit', $res->id) }}"
                                                    class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <form action="{{ route('teacher.resources.destroy', $res->id) }}" method="POST"
                                                    onsubmit="return confirmDelete()">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </form>
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

@endsection

@section('page_js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const lessonSelect = document.getElementById('download_lesson_id');
        const themeSelect = document.getElementById('download_theme_id');
        const resourceTypeInput = document.getElementById('resource_type');
        const form = document.getElementById('downloadForm');

        lessonSelect.addEventListener('change', function() {
            if (this.value) themeSelect.value = '';
        });

        themeSelect.addEventListener('change', function() {
            if (this.value) lessonSelect.value = '';
        });

        form.addEventListener('submit', function(e) {
            const lessonId = lessonSelect.value;
            const themeId = themeSelect.value;

            if (!lessonId && !themeId) {
                e.preventDefault();
                alert('Please select a lesson or a theme to download.');
                return;
            }

            if (lessonId && themeId) {
                e.preventDefault();
                alert('Please select only one option (Lesson or Theme).');
                return;
            }

            resourceTypeInput.value = lessonId ? 'lesson' : 'theme';
        });
    });
</script>
@section('page_js')
<script>
    function toggleDetails(id) {
        const el = document.getElementById(id);
        el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
    }
</script>
@endsection
@endsection