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
                <h2 class="text-2xl font-bold mb-4">Add New Resources</h2>

                <form action="{{ route('teacher.resources.store') }}" method="POST" enctype="multipart/form-data" id="resourceForm">
                    @csrf

                    <div id="resourceContainer">

                        <div class="resource-block p-4 border rounded mb-6 bg-gray-50 relative">

                            <h3 class="text-lg font-semibold mb-2">New Resource</h3>

                            <button type="button" class="remove-block absolute top-2 right-2 text-red-600 font-bold hidden">
                                ✖
                            </button>

                            <div class="form-group">
                                <label class="block font-medium">Resource Name</label>
                                <input type="text" name="resources[0][name]" class="w-full p-2 border rounded" required>
                            </div>

                            <div class="form-group mt-2">
                                <label class="block font-medium">Description</label>
                                <textarea name="resources[0][description]" class="w-full p-2 border rounded"></textarea>
                            </div>

                            <div class="form-group mt-2">
                                <label class="block font-medium">Select Grade</label>
                                <select name="resources[0][stage_id]" class="stage-select w-full p-2 border rounded" required>
                                    <option value="">Select a grade</option>
                                    @foreach ($stages as $stage)
                                    <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mt-2">
                                <label class="block font-medium">Select Theme</label>
                                <select name="resources[0][material_id]" class="material-select w-full p-2 border rounded" required disabled>
                                    <option value="">Select grade first</option>
                                </select>
                            </div>
                            <div class="form-group mt-2">
                                <label class="block font-medium">Date of Material</label>
                                <input type="date" name="resources[0][date]" class="w-full p-2 border rounded" required>
                            </div>

                            <div class="form-group mt-2">
                                <label class="block font-medium">Image</label>
                                <input type="file" name="resources[0][image]" class="w-full p-2 border rounded">
                            </div>

                            <div class="form-group mt-2">
                                <label class="block font-medium">Upload File</label>
                                <input type="file" name="resources[0][file_path]" accept=".pdf,.ppt,.pptx,.doc,.docx,.zip,.mp4,.mov,.avi,.jpg,.jpeg,.png" class="file-input w-full p-2 border rounded">
                            </div>

                            <div class="form-group mt-2">
                                <label class="block font-medium">Video URL</label>
                                <input type="url" name="resources[0][video_url]" placeholder="https://example.com/video" class="url-input w-full p-2 border rounded">
                            </div>

                        </div>
                    </div>

                    <button type="button" id="addMore" class="bg-green-600 text-white px-4 py-2 rounded mt-3">
                        Add More
                    </button>

                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded mt-6 block">
                        Save Resources
                    </button>
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
<script>
    let index = 1;

    document.getElementById('addMore').addEventListener('click', function() {
        const container = document.getElementById('resourceContainer');
        const originalBlock = container.querySelector('.resource-block');
        const clone = originalBlock.cloneNode(true);

        clone.querySelector('h3').innerText = `New Resource`;
        clone.querySelector('.remove-block').classList.remove('hidden');

        clone.querySelectorAll('input, textarea, select').forEach(input => {
            let name = input.getAttribute('name');
            name = name.replace(/\[\d+\]/, `[${index}]`);
            input.setAttribute('name', name);
            input.value = "";
        });

        container.appendChild(clone);
        index++;
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-block')) {
            e.target.parentElement.remove();
        }
    });
</script>

<script>
    const stageToMaterials = @json($materials->groupBy('stage_id'));

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('stage-select')) {
            const stageId = e.target.value;
            const materialSelect = e.target.closest('.resource-block').querySelector('.material-select');

            materialSelect.innerHTML = '<option value="">Select Theme/Material</option>';

            if (stageId && stageToMaterials[stageId]) {
                stageToMaterials[stageId].forEach(material => {
                    materialSelect.innerHTML += `<option value="${material.id}">${material.title}</option>`;
                });
                materialSelect.disabled = false;
            } else {
                materialSelect.innerHTML = '<option value="">No materials available</option>';
                materialSelect.disabled = true;
            }
        }
    });
</script>



@endsection

@endsection