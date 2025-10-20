@php
    $userAuth = auth()->guard('teacher')->user();
@endphp

@extends('layouts.app')

@section('title')
    Resources
@endsection

@php
    $menuItems = [
        ['label' => 'Dashboard', 'icon' => 'fi fi-rr-table-rows', 'route' => route('teacher.dashboard')],
        ['label' => 'Resources', 'icon' => 'fi fi-rr-table-rows', 'route' => route('teacher.resources.index')],
        ['label' => 'Ticket', 'icon' => 'fa-solid fa-ticket', 'route' => route('teacher.tickets.index')],

        ['label' => 'Chat', 'icon' => 'fa-solid fa-message', 'route' => route('chat.all')],
    ];
@endphp
@section('sidebar')
    @include('components.sidebar', ['menuItems' => $menuItems])
@endsection

@section('content')
    @include('components.profile')

    <div class="p-3">
        <div class="flex justify-between items-center px-5 my-8">
            <div class="text-[#667085]">
                <i class="fa-solid fa-house mx-2"></i>
                <span class="mx-2 text-[#D0D5DD]">/</span>
                <a href="#" class="mx-2 cursor-pointer">Resources</a>
            </div>

        </div>

        <div class="p-3">
            <div class="overflow-x-auto rounded-2xl border border-[#EAECF0]">
                <div class="container mx-auto px-4 py-8">
                    <h2 class="text-2xl font-bold mb-4">Add New Resource</h2>

                    <form action="{{ route('teacher.resources.store') }}" method="POST" enctype="multipart/form-data"
                        class="space-y-4">
                        @csrf

                        <div class="form-group">
                            <label for="name" class="block font-medium">Resource Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}"
                                class="w-full p-2 border border-gray-300 rounded" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description" class="block font-medium">Description</label>
                            <textarea name="description" id="description" class="w-full p-2 border border-gray-300 rounded">{{ old('description') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="stage_id" class="block font-medium">Select Stage</label>
                            <select name="stage_id" id="stage_id" class="w-full p-2 border border-gray-300 rounded"
                                required>
                                <option value="">Select a stage</option>
                                @foreach ($stages as $stage)
                                    <option value="{{ $stage->id }}"
                                        {{ old('stage_id') == $stage->id ? 'selected' : '' }}>
                                        {{ $stage->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="image" class="block font-medium"> Image</label>
                            <input type="file" name="image" id="image"
                                class="w-full p-2 border border-gray-300 rounded">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="file_path" class="block font-medium">Upload File </label>
                            <input type="file" name="file_path" id="file_path"
                                accept=".pdf,.ppt,.pptx,.zip,.mp4,.mov,.avi"
                                class="w-full p-2 border border-gray-300 rounded">
                            @error('file_path')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="video_url" class="block font-medium">Video URL </label>
                            <input type="url" name="video_url" id="video_url" placeholder="https://example.com/video"
                                class="w-full p-2 border border-gray-300 rounded">
                        </div>

                        {{-- <div class="form-group">
                            <label for="type" class="block font-medium">Resource Type</label>
                            <select name="type" id="type" class="w-full p-2 border border-gray-300 rounded"
                                required>
                                <option value="">Select a type</option>
                                <option value="pdf" {{ old('type') == 'pdf' ? 'selected' : '' }}>PDF</option>
                                <option value="ebook" {{ old('type') == 'ebook' ? 'selected' : '' }}>Ebook</option>
                            </select>
                        </div> --}}

                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded"
                            style="background-color: rgb(59 130 246)">Add Resource</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@section('page_js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const fileInput = document.getElementById('file_path');
            const urlInput = document.getElementById('video_url');

            form.addEventListener('submit', function(e) {
                const hasFile = fileInput.files.length > 0;
                const hasURL = urlInput.value.trim() !== '';

                if (!hasFile && !hasURL) {
                    e.preventDefault();
                    alert("Please upload a file or enter a video URL.");
                }

                if (hasFile && hasURL) {
                    e.preventDefault();
                    alert("Please fill only one: upload a file OR enter a video URL.");
                }
            });
        });
    </script>
    <script>
        const fileInput = document.getElementById('file_path');
        const urlInput = document.getElementById('video_url');

        fileInput.addEventListener('change', function() {
            urlInput.disabled = fileInput.files.length > 0;
        });

        urlInput.addEventListener('input', function() {
            fileInput.disabled = urlInput.value.trim() !== '';
        });
    </script>
@endsection

@endsection
