@extends('admin.layouts.layout')

@section('content')
<div class="wrapper">
    @include('admin.layouts.sidebar')

    <div class="main">
        @include('admin.layouts.navbar')

        <main class="content">
            <div class="container-fluid p-0">

                <h1>Add Theme Resource</h1>

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

                <form action="{{ route('theme_resource.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Select Stage -->
                    <div class="mb-3">
                        <label for="stage_id" class="form-label">Grade</label>
                        <select name="stage_id" id="stage_id" class="form-control" required>
                            <option value="" disabled selected>Select Grade</option>
                            @foreach ($stages as $stage)
                            <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Select Theme -->
                    <div class="mb-3">
                        <label for="theme_id" class="form-label">Theme</label>
                        <select name="theme_id" id="theme_id" class="form-control" required disabled>
                            <option value="" disabled selected>Select Theme</option>
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

                <!-- Schools Modal -->
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
    const stageSelect = document.getElementById('stage_id');
    const themeSelect = document.getElementById('theme_id');
    const selectSchoolsBtn = document.getElementById('selectSchoolsBtn');
    const schoolsList = document.getElementById('schoolsList');
    const saveSchoolsBtn = document.getElementById('saveSchools');
    const selectedSchoolsInput = document.getElementById('selected_schools');
    const selectAllBtn = document.getElementById('selectAllSchools');

    let currentSchools = [];

    // Step 1: When Grade changes → load themes
    stageSelect.addEventListener('change', function() {
        const stageId = this.value;
        themeSelect.innerHTML = '<option value="" disabled selected>Loading...</option>';
        themeSelect.disabled = true;
        selectSchoolsBtn.disabled = true;

        fetch(`{{ route('theme_resource.themes', ':stageId') }}`.replace(':stageId', stageId))
            .then(res => res.json())
            .then(themes => {
                themeSelect.innerHTML = '<option value="" disabled selected>Select Theme</option>';
                themes.forEach(theme => {
                    const option = document.createElement('option');
                    option.value = theme.id;
                    option.textContent = theme.title;
                    themeSelect.appendChild(option);
                });
                themeSelect.disabled = false;
            });
    });

    // Step 2: When Theme changes → load its schools
    themeSelect.addEventListener('change', function() {
        const themeId = this.value;
        if (!themeId) return;

        fetch("{{ route('theme_resource.schools', ':themeId') }}".replace(':themeId', themeId))
            .then(res => res.json())
            .then(data => {
                currentSchools = data;
                selectSchoolsBtn.disabled = false;
            });
    });

    // Step 3: Open Schools Modal
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

    // Step 4: Select All Schools
    selectAllBtn.addEventListener('click', () => {
        document.querySelectorAll('.school-checkbox').forEach(cb => cb.checked = true);
    });

    // Step 5: Save Selected Schools
    saveSchoolsBtn.addEventListener('click', function() {
        const selected = Array.from(document.querySelectorAll('.school-checkbox:checked')).map(cb => cb.value);
        selectedSchoolsInput.value = JSON.stringify(selected);
        bootstrap.Modal.getInstance(document.getElementById('schoolsModal')).hide();
    });
});
</script>
@endsection
