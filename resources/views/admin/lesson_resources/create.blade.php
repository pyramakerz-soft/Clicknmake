@extends('admin.layouts.layout')

@section('content')
<div class="wrapper">
    @include('admin.layouts.sidebar')

    <div class="main">
        @include('admin.layouts.navbar')

        <main class="content">
            <div class="container-fluid p-0">

                <h1>Add Lesson Resource</h1>
                @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
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

                <form action="{{ route('lesson_resource.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="lesson_id" class="form-label">Lesson</label>
                    <select name="lesson_id" id="lesson_id" class="form-control" required>
                        <option value="" disabled selected>Select Lesson</option>
                        @foreach ($lessons as $lesson)
                            <option value="{{ $lesson->id }}">
                                {{ $lesson->chapter->material->title ?? ''}} - 
                                {{ $lesson->chapter->unit->title ?? ''}} - 
                                {{ $lesson->chapter->title ?? ''}} - 
                                {{ $lesson->title ?? ''}}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="file_path" class="form-label">Upload Resource</label>
                    <input type="file" name="file_path" class="form-control" id="file_path" required>
                </div>
                
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" id="title" required>
                </div>

                <!-- Select Schools Button -->
                <button type="button" class="btn btn-secondary" id="selectSchoolsBtn" disabled>
                    Select Schools
                </button>

                <button type="submit" class="btn btn-primary">Add Resource</button>

                <!-- Hidden input to store selected school IDs -->
                <input type="hidden" name="selected_schools" id="selected_schools">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const lessonSelect = document.getElementById('lesson_id');
    const selectSchoolsBtn = document.getElementById('selectSchoolsBtn');
    const schoolsList = document.getElementById('schoolsList');
    const saveSchoolsBtn = document.getElementById('saveSchools');
    const selectedSchoolsInput = document.getElementById('selected_schools');
    const selectAllBtn = document.getElementById('selectAllSchools');

    let currentSchools = [];

    lessonSelect.addEventListener('change', function() {
        const lessonId = this.value;
        if (!lessonId) return;

        fetch("{{ route('lesson_resource.schools', ':lessonId') }}".replace(':lessonId', lessonId))
            .then(res => res.json())
            .then(data => {
                currentSchools = data;
                selectSchoolsBtn.disabled = false;
            });
    });

    selectSchoolsBtn.addEventListener('click', function() {
        schoolsList.innerHTML = '';
        currentSchools.forEach(school => {
            const div = document.createElement('div');
            div.classList.add('col-md-4');
            div.innerHTML = `
                <div class="form-check">
                    <input class="form-check-input school-checkbox" type="checkbox" value="${school.id}" checked id="school_${school.id}">
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
