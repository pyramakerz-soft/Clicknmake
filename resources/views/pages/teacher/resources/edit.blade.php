@php
    $userAuth = auth()->guard('teacher')->user();
@endphp

@extends('layouts.app')

@section('title')
    Edit Resource
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
                <a href="{{ route('teacher.resources.index') }}" class="mx-2 cursor-pointer">Resources</a>
                <span class="mx-2 text-[#D0D5DD]">/</span>
                <span>Edit Resource</span>
            </div>
        </div>

        <div class="p-3">
            <div class="overflow-x-auto rounded-2xl border border-[#EAECF0]">
                <div class="container mx-auto px-4 py-8">
                    <h2 class="text-2xl font-bold mb-4">Edit Resource</h2>

                    <form action="{{ route('teacher.resources.update', $resource->id) }}" method="POST"
                        enctype="multipart/form-data" id="editResourceForm">
                        @csrf
                        @method('PUT')

                        <div class="p-4 border rounded bg-gray-50">

                            <div class="form-group">
                                <label class="block font-medium">Resource Name</label>
                                <input type="text" name="name"
                                    class="w-full p-2 border rounded"
                                    value="{{ old('name', $resource->name) }}" required>
                            </div>

                            <div class="form-group mt-2">
                                <label class="block font-medium">Description</label>
                                <textarea name="description" class="w-full p-2 border rounded">{{ old('description', $resource->description) }}</textarea>
                            </div>

                            <div class="form-group mt-2">
                                <label class="block font-medium">Select Grade</label>
                                <select name="stage_id" class="stage-select w-full p-2 border rounded" required>
                                    <option value="">Select a grade</option>
                                    @foreach ($stages as $stage)
                                        <option value="{{ $stage->id }}"
                                            {{ $resource->stage_id == $stage->id ? 'selected' : '' }}>
                                            {{ $stage->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mt-2">
                                <label class="block font-medium">Select Theme</label>
                                <select name="material_id" class="material-select w-full p-2 border rounded" required>
                                    @foreach ($materials->where('stage_id', $resource->stage_id) as $material)
                                        <option value="{{ $material->id }}"
                                            {{ $resource->material_id == $material->id ? 'selected' : '' }}>
                                            {{ $material->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mt-2">
                                <label class="block font-medium">Date of Material</label>
                                <input type="date" name="date" class="w-full p-2 border rounded"
                                       value="{{ old('date', $resource->date) }}" required>
                            </div>

                            <div class="form-group mt-2">
                                <label class="block font-medium">Replace Image</label>
                                <input type="file" name="image" class="w-full p-2 border rounded">
                            </div>

                            <div class="form-group mt-2">
                                <label class="block font-medium">Replace File</label>
                                <input type="file" name="file_path"
                                    accept=".pdf,.ppt,.pptx,.doc,.docx,.zip,.mp4,.mov,.avi,.jpg,.jpeg,.png"
                                    class="file-input w-full p-2 border rounded">
                            </div>

                            <div class="form-group mt-2">
                                <label class="block font-medium">Video URL</label>
                                <input type="url" name="video_url"
                                    class="url-input w-full p-2 border rounded"
                                    value="{{ old('video_url', $resource->video_url) }}"
                                    placeholder="https://example.com/video">
                                @if ($resource->video_url)
                                    <label class="block mt-2 text-sm text-red-600">
                                        <input type="checkbox" name="remove_video"> Remove Video URL
                                    </label>
                                @endif
                            </div>

                        </div>

                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded mt-6 block">
                            Update Resource
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection

@section('page_js')
<script>
    const stageToMaterials = @json($materials->groupBy('stage_id'));

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('stage-select')) {
            const stageId = e.target.value;
            const materialSelect = e.target.closest('form').querySelector('.material-select');
            materialSelect.innerHTML = '<option value="">Select Theme/Material</option>';

            if (stageId && stageToMaterials[stageId]) {
                stageToMaterials[stageId].forEach(material => {
                    materialSelect.innerHTML += `<option value="${material.id}">${material.title}</option>`;
                });
                materialSelect.disabled = false;
            } else {
                materialSelect.disabled = true;
            }
        }
    });
</script>

<script>
    // File/URL mutual exclusivity
    const fileInput = document.querySelector('.file-input');
    const urlInput = document.querySelector('.url-input');

    fileInput?.addEventListener('change', () => urlInput.disabled = fileInput.files.length > 0);
    urlInput?.addEventListener('input', () => fileInput.disabled = urlInput.value.trim() !== '');
</script>
@endsection
