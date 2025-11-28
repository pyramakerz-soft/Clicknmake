@extends('admin.layouts.layout')

@section('content')
<div class="wrapper">
    @include('admin.layouts.sidebar')

    <div class="main">
        @include('admin.layouts.navbar')

        <main class="content">
            <div class="container-fluid p-0">

                <h1>View Resource</h1>
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
            </div>
        </main>
    </div>
</div>
@endsection
@section('page_js')
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