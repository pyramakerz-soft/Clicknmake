@extends('layouts.app')

@section('title', $resource->name)
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
<div class="container mt-4" style="max-width: 1450px !important;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="section-title">{{ $resource->name }}</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('teacher.resources.admin') }}" class="btn btn-outline-primary ">
                <i class="fa-solid fa-folder-open me-1"></i> View Admin Resources
            </a>
            <a href="{{ route('teacher.resources.create') }}" class="btn btn-outline-primary">
                <i class="fa-solid fa-plus me-1"></i> Add Resource
            </a>
        </div>
    </div>

    @php
    $file = $resource->file_path;
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    @endphp

    <div class="h-[100vh] mt-2">
        @if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
        <img src="{{ asset($file) }}" class="w-full h-full object-contain" />

        @elseif($ext === 'pdf')
        <iframe src="{{ asset($file) }}#toolbar=0" class="w-full h-full" frameborder="0"></iframe>

        @elseif(in_array($ext, ['mp4','mov','webm']))
        <video class="w-full h-full" controls controlsList="nodownload">
            <source src="{{ asset($file) }}">
            Your browser does not support the video tag.
        </video>

        @elseif(in_array($ext, ['doc','docx','ppt','pptx']))
        <iframe src="https://docs.google.com/gview?url=https://pyramakerz-artifacts.com/LMS/lms_pyramakerz/public/resources/qoGJLxwcJgrZVZ1MeWkLEGfxX8UDmPfZK6efFcKD.pptx&embedded=true"
            class="w-full h-full" frameborder="0">
        </iframe>

        @else
        <p class="text-center text-gray-600">
            Preview not supported for this file type.
        </p>
        @endif
    </div>

    <a href="{{ route('teacher.resources.index') }}" class="btn btn-secondary mt-3">Back to Resources</a>
</div>
@endsection