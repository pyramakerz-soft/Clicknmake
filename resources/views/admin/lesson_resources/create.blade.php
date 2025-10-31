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

                    <!-- Grade -->
                    <div class="mb-3">
                        <label for="stage_id" class="form-label">Grade</label>
                        <select name="stage_id" id="stage_id" class="form-control" required>
                            <option value="" disabled selected>Select Grade</option>
                            @foreach ($stages as $stage)
                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Theme -->
                    <div class="mb-3">
                        <label for="material_id" class="form-label">Theme</label>
                        <select name="material_id" id="material_id" class="form-control" disabled required>
                            <option value="" disabled selected>Select Theme</option>
                        </select>
                    </div>

                    <!-- Unit -->
                    <div class="mb-3">
                        <label for="unit_id" class="form-label">Unit</label>
                        <select name="unit_id" id="unit_id" class="form-control" disabled required>
                            <option value="" disabled selected>Select Unit</option>
                        </select>
                    </div>

                    <!-- Chapter -->
                    <div class="mb-3">
                        <label for="chapter_id" class="form-label">Chapter</label>
                        <select name="chapter_id" id="chapter_id" class="form-control" disabled required>
                            <option value="" disabled selected>Select Chapter</option>
                        </select>
                    </div>

                    <!-- Lesson -->
                    <div class="mb-3">
                        <label for="lesson_id" class="form-label">Lesson</label>
                        <select name="lesson_id" id="lesson_id" class="form-control" disabled required>
                            <option value="" disabled selected>Select Lesson</option>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const stages = @json($stages);

    const stageSelect = document.getElementById('stage_id');
    const materialSelect = document.getElementById('material_id');
    const unitSelect = document.getElementById('unit_id');
    const chapterSelect = document.getElementById('chapter_id');
    const lessonSelect = document.getElementById('lesson_id');

    const selectSchoolsBtn = document.getElementById('selectSchoolsBtn');
    const schoolsList = document.getElementById('schoolsList');
    const saveSchoolsBtn = document.getElementById('saveSchools');
    const selectedSchoolsInput = document.getElementById('selected_schools');
    const selectAllBtn = document.getElementById('selectAllSchools');

    let currentSchools = [];

    // Helper: reset dropdowns
    function resetDropdown(selectEl, placeholder) {
        selectEl.innerHTML = `<option value="" disabled selected>${placeholder}</option>`;
        selectEl.disabled = true;
    }

    // When Grade changes -> load Themes
    stageSelect.addEventListener('change', function() {
        const stageId = this.value;
        const selectedStage = stages.find(s => s.id == stageId);

        resetDropdown(materialSelect, 'Select Theme');
        resetDropdown(unitSelect, 'Select Unit');
        resetDropdown(chapterSelect, 'Select Chapter');
        resetDropdown(lessonSelect, 'Select Lesson');
        selectSchoolsBtn.disabled = true;

        if (!selectedStage) return;

        selectedStage.materials.forEach(material => {
            const opt = document.createElement('option');
            opt.value = material.id;
            opt.textContent = material.title;
            materialSelect.appendChild(opt);
        });
        materialSelect.disabled = false;
    });

    // When Theme changes -> load Units
    materialSelect.addEventListener('change', function() {
        const stageId = stageSelect.value;
        const selectedStage = stages.find(s => s.id == stageId);
        const materialId = this.value;

        const selectedMaterial = selectedStage?.materials.find(m => m.id == materialId);

        resetDropdown(unitSelect, 'Select Unit');
        resetDropdown(chapterSelect, 'Select Chapter');
        resetDropdown(lessonSelect, 'Select Lesson');
        selectSchoolsBtn.disabled = true;

        if (!selectedMaterial) return;

        selectedMaterial.units.forEach(unit => {
            const opt = document.createElement('option');
            opt.value = unit.id;
            opt.textContent = unit.title;
            unitSelect.appendChild(opt);
        });
        unitSelect.disabled = false;
    });

    // When Unit changes -> load Chapters
    unitSelect.addEventListener('change', function() {
        const stageId = stageSelect.value;
        const materialId = materialSelect.value;
        const selectedStage = stages.find(s => s.id == stageId);
        const selectedMaterial = selectedStage?.materials.find(m => m.id == materialId);
        const unitId = this.value;

        const selectedUnit = selectedMaterial?.units.find(u => u.id == unitId);

        resetDropdown(chapterSelect, 'Select Chapter');
        resetDropdown(lessonSelect, 'Select Lesson');
        selectSchoolsBtn.disabled = true;

        if (!selectedUnit) return;

        selectedUnit.chapters.forEach(ch => {
            const opt = document.createElement('option');
            opt.value = ch.id;
            opt.textContent = ch.title;
            chapterSelect.appendChild(opt);
        });
        chapterSelect.disabled = false;
    });

    // When Chapter changes -> load Lessons
    chapterSelect.addEventListener('change', function() {
        const stageId = stageSelect.value;
        const materialId = materialSelect.value;
        const unitId = unitSelect.value;
        const selectedStage = stages.find(s => s.id == stageId);
        const selectedMaterial = selectedStage?.materials.find(m => m.id == materialId);
        const selectedUnit = selectedMaterial?.units.find(u => u.id == unitId);
        const chapterId = this.value;

        const selectedChapter = selectedUnit?.chapters.find(c => c.id == chapterId);

        resetDropdown(lessonSelect, 'Select Lesson');
        selectSchoolsBtn.disabled = true;

        if (!selectedChapter) return;

        selectedChapter.lessons.forEach(lesson => {
            const opt = document.createElement('option');
            opt.value = lesson.id;
            opt.textContent = lesson.title;
            lessonSelect.appendChild(opt);
        });
        lessonSelect.disabled = false;
    });

    // When Lesson changes -> load its Schools
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

    // --- Same school modal logic as before ---
    selectSchoolsBtn.addEventListener('click', function() {
        schoolsList.innerHTML = '';
        currentSchools.forEach(school => {
            const div = document.createElement('div');
            div.classList.add('col-md-4');
            div.innerHTML = `
                <div class="form-check">
                    <input class="form-check-input school-checkbox" type="checkbox" value="${school.id}" checked id="school_${school.id}">
                    <label class="form-check-label" for="school_${school.id}">${school.name}</label>
                </div>`;
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

