@extends('layouts.app')

@section('title', $resource->name)

@section('page_css')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

<style>
    body {
        background-color: #f8fafc;
        user-select: none !important;
        -webkit-user-select: none !important;
        -ms-user-select: none !important;
    }

    /* Disable interaction with media, but allow scrolling */
    img,
    video {
        pointer-events: none !important;
    }

    iframe {
        pointer-events: auto !important;
    }

    .no-download-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 50;
        background: transparent;
        pointer-events: none !important;
    }

    /* Disable print */
    @media print {
        body * {
            display: none !important;
        }
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