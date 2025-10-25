@extends('admin.layouts.layout')

@section('content')
<div class="wrapper">
    @include('admin.layouts.sidebar')

    <div class="main">
        @include('admin.layouts.navbar')

        <main class="content">
            <div class="container-fluid p-0">

                <h1>Edit Lesson Resource</h1>

                @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('lesson_resource.update', $resource->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="lesson_id" class="form-label">Lesson</label>
                        <select name="lesson_id" id="lesson_id" class="form-control" disabled>
                            <option value="{{ $resource->lesson->id }}" selected>
                                {{ $resource->lesson->chapter->material->title ?? ''}} - 
                                {{ $resource->lesson->chapter->unit->title ?? ''}} - 
                                {{ $resource->lesson->chapter->title ?? ''}} - 
                                {{ $resource->lesson->title ?? ''}}
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Resource Title</label>
                        <input type="text" name="title" id="title" class="form-control" value="{{ $resource->title }}" required>
                    </div>

                    <!-- Select Schools Button -->
                    <button type="button" class="btn btn-secondary" id="selectSchoolsBtn">
                        Edit School Visibility
                    </button>

                    <button type="submit" class="btn btn-primary">Update Resource</button>

                    <!-- Hidden input for selected schools -->
                    <input type="hidden" name="selected_schools" id="selected_schools" value="{{ json_encode($resource->schools->pluck('id')) }}">
                </form>

                <!-- Modal -->
                <div class="modal fade" id="schoolsModal" tabindex="-1" aria-labelledby="schoolsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="schoolsModalLabel">Select Schools</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>Schools List</strong>
                                    <button type="button" id="selectAllSchools" class="btn btn-sm btn-outline-primary">Select All</button>
                                </div>
                                <div id="schoolsList" class="row g-3"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-success" id="saveSchools">Save Selection</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>
@endsection

@section('page_js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectSchoolsBtn = document.getElementById('selectSchoolsBtn');
    const schoolsList = document.getElementById('schoolsList');
    const saveSchoolsBtn = document.getElementById('saveSchools');
    const selectedSchoolsInput = document.getElementById('selected_schools');
    const selectAllBtn = document.getElementById('selectAllSchools');

    let allSchools = @json($schools);
    let selectedSchools = JSON.parse(selectedSchoolsInput.value || '[]');

    selectSchoolsBtn.addEventListener('click', function() {
        schoolsList.innerHTML = '';
        allSchools.forEach(school => {
            const checked = selectedSchools.includes(school.id) ? 'checked' : '';
            const div = document.createElement('div');
            div.classList.add('col-md-4');
            div.innerHTML = `
                <div class="form-check">
                    <input class="form-check-input school-checkbox" type="checkbox" value="${school.id}" ${checked} id="school_${school.id}">
                    <label class="form-check-label" for="school_${school.id}">${school.name}</label>
                </div>
            `;
            schoolsList.appendChild(div);
        });
        new bootstrap.Modal(document.getElementById('schoolsModal')).show();
    });

    selectAllBtn.addEventListener('click', () => {
        document.querySelectorAll('.school-checkbox').forEach(cb => cb.checked = true);
    });

    saveSchoolsBtn.addEventListener('click', function() {
        const selected = Array.from(document.querySelectorAll('.school-checkbox:checked')).map(cb => cb.value);
        selectedSchoolsInput.value = JSON.stringify(selected);
        bootstrap.Modal.getInstance(document.getElementById('schoolsModal')).hide();
    });
});
</script>
@endsection
