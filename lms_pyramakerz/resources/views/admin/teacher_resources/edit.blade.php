@extends('admin.layouts.layout')

@section('content')
    <div class="wrapper">
        @include('admin.layouts.sidebar')

        <div class="main">
            @include('admin.layouts.navbar')

            <main class="content">
                <div class="container-fluid p-0">
                    <h1>Edit Teacher Resource</h1>

                    <form action="{{ route('teacher_resources.update', $resource->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Resource Name</label>
                            <input type="text" name="name" class="form-control"
                                value="{{ old('name', $resource->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" class="form-control">{{ old('description', $resource->description) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <div class="mb-3">
                                    <label for="stage_id" class="form-label">Stage</label>
                                    <select name="stage_id" class="form-control stage-select" required>
                                        <option value="">Select Stage</option>
                                        @foreach ($stages as $stage)
                                            <option value="{{ $stage->id }}" {{ $resource->stage_id == $stage->id ? 'selected' : '' }}>
                                                {{ $stage->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-6">
                                <div class="mb-3">
                                    <label for="material_id" class="form-label">Theme / Material</label>
                                    <select name="material_id" class="form-control material-select" required>
                                        @foreach ($materials->where('stage_id', $resource->stage_id) as $material)
                                            <option value="{{ $material->id }}" {{ $resource->material_id == $material->id ? 'selected' : '' }}>
                                                {{ $material->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-6">
                                <div class="mb-3">
                                    <label for="date" class="form-label">Date of Material</label>
                                    <input type="date" name="date" class="form-control"
                                           value="{{ old('date', $resource->date) }}" required>
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-6"></div> <!-- for spacing -->

                            <div class="col-sm-12 col-md-6">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Replace Image</label>
                                    <input type="file" name="image" class="form-control">
                                    @if ($resource->image)
                                        <p class="mt-2">
                                            <img src="{{ asset($resource->image) }}" width="100">
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-6">
                                <div class="mb-3">
                                    <label for="file_path" class="form-label">Replace File</label>
                                    <input type="file" name="file_path"
                                           accept=".pdf,.ppt,.pptx,.doc,.docx,.zip,.mp4,.mov,.avi,.jpg,.jpeg,.png"
                                           class="form-control file-input">
                                    @if ($resource->file_path)
                                        <p class="mt-2">
                                            <a href="{{ asset($resource->file_path) }}" target="_blank">Current File</a>
                                        </p>
                                    @endif
                                </div>
                            </div>

                        </div>

                        <div class="mb-3">
                            <label for="video_url" class="form-label">Video URL</label>
                            <input type="url" name="video_url"
                                   class="form-control url-input"
                                   value="{{ old('video_url', $resource->video_url) }}"
                                   placeholder="https://example.com/video">

                            @if ($resource->video_url)
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" name="remove_video" value="1">
                                    <label class="form-check-label text-danger">Remove Video URL</label>
                                </div>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-success mt-3">Update Resource</button>
                    </form>
                </div>
            </main>
        </div>
    </div>
@endsection

@section('page_js')
<script>
    const stageToMaterials = @json($materials->groupBy('stage_id'));

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('stage-select')) {
            const stageId = e.target.value;
            const materialSelect = document.querySelector('.material-select');
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

    // File / URL mutual exclusivity
    const fileInput = document.querySelector('.file-input');
    const urlInput = document.querySelector('.url-input');

    fileInput?.addEventListener('change', () => urlInput.disabled = fileInput.files.length > 0);
    urlInput?.addEventListener('input', () => fileInput.disabled = urlInput.value.trim() !== '');
</script>
@endsection
