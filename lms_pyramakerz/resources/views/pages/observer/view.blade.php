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

<div class="no-download-overlay"></div>

<div class="container mt-4" style="max-width:1450px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="section-title">{{ $resource->name }}</h4>
    </div>

    <div style="height:90vh; position:relative; overflow:auto;">
        {{-- Slides / Converted Images --}}
        @if($convertedType === 'slides')
        @foreach($converted as $slide)
        <img src="{{ asset($slide) }}" class="w-100 mb-3 border shadow rounded">
        @endforeach
        @elseif(in_array($ext, ['mp4','mov','webm']))
        <video class="w-100 h-100" controls controlsList="nodownload" disablePictureInPicture>
            <source src="{{ asset($file) }}">
        </video>
        @elseif(in_array($ext, ['jpg','jpeg','png','gif','webp']))
        <img src="{{ asset($file) }}" class="w-100 h-100 object-contain">
        @else
        <p class="text-muted text-center mt-5">Preview not supported for this file type.</p>
        @endif
    </div>
</div>
@endsection

@section("page_js")
<script>
    // Disable right-click
    document.addEventListener("contextmenu", e => e.preventDefault());

    // Disable drag to save
    window.addEventListener("dragstart", e => e.preventDefault());

    // Disable key combos
    document.addEventListener("keydown", function(e) {
        if (e.ctrlKey && ['s', 'S', 'p', 'P', 'u', 'U', 'c', 'C'].includes(e.key)) e.preventDefault();
        if (e.key === "F12" || (e.ctrlKey && e.shiftKey && ['I', 'C', 'J'].includes(e.key.toUpperCase()))) e.preventDefault();
    });

    // Disable selection
    document.addEventListener("selectstart", e => e.preventDefault());
</script>
@endsection