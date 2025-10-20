@php
    $userAuth = auth()->guard('teacher')->user();
@endphp

@extends('layouts.app')

@section('title', 'Admin Resources')

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

@section('content')
    @include('components.profile')

    <div class="container mt-4">

        <!-- LESSON RESOURCES -->
        <h4 class="section-title">Lesson Resources</h4>
        <div class="row">
            @forelse ($groupedLessons as $lessonId => $resources)
                <div class="col-md-4 mb-4">
                    <div class="resource-card" onclick="toggleDetails('lesson-{{ $lessonId }}')">
                        <h5><strong style="color: #17253e;">Lesson:</strong>
                            <span class="title">{{ $resources->first()->lesson->title ?? 'N/A' }}</span>
                        </h5>
                        <ul class="mt-3 resource-list p-0" id="lesson-{{ $lessonId }}" style="display: none;">
                            @foreach ($resources as $res)
                                <li class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold">{{ $res->title }}</div>
                                        <div class="resource-type">{{ strtoupper($res->type) }}</div>
                                    </div>
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
        <h4 class="section-title">Theme Resources</h4>
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
@endsection

@section('page_js')
    <script>
        function toggleDetails(id) {
            const el = document.getElementById(id);
            el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
        }
    </script>
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
        <h4>Lesson Resources</h4>
        <div class="row">
            @forelse ($groupedLessons as $lessonId => $resources)
                <div class="col-md-4 mb-4">
                    <div class="resource-card" onclick="toggleDetails('lesson-{{ $lessonId }}')">
                        <h5><strong>Lesson:</strong>
                            <span class="title">
                                {{ $resources->first()->lesson->title ?? 'N/A' }}
                            </span>
                        </h5>
                        <ul class="mt-2 p-0" id="lesson-{{ $lessonId }}" style="display: none;">
                            @foreach ($resources as $res)
                                <li class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <div class="fw-bold">{{ $res->title }}</div>
                                        <div class="resource-type">{{ strtoupper($res->type) }}</div>
                                    </div>
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

        <hr>

        <h4>Theme Resources</h4>
        <div class="row">
        @forelse ($themeStages as $stageName => $themes)
            <div class="stage-section mb-5">
                <h4 class="mb-3 text-primary"><strong>Stage:</strong> {{ $stageName }}</h4>

                <div class="row">
                    @forelse ($themes as $theme)
                        <div class="col-md-4 mb-4">
                            <div class="resource-card" onclick="toggleDetails('theme-{{ $theme->id }}')">
                                <h5><strong>Theme:</strong>
                                    <span class="title">{{ $theme->title }}</span>
                                </h5>
                                <ul class="mt-2 p-0" id="theme-{{ $theme->id }}" style="display: none;">
                                    @forelse ($theme->resources as $res)
                                        <li class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <div class="fw-bold">{{ $res->title }}</div>
                                                <div class="resource-type">{{ strtoupper($res->type) }}</div>
                                            </div>
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
            if (el.style.display === 'none') {
                el.style.display = 'block';
            } else {
                el.style.display = 'none';
            }
        }
    </script>
@endsection
