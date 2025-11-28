@extends('admin.layouts.layout')

@section('content')
<div class="wrapper">
    @include('admin.layouts.sidebar')

    <div class="main">
        @include('admin.layouts.navbar')

        <main class="content">
            <div class="container-fluid p-0">

                <h1>Edit Theme Resource</h1>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
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

                <form action="{{ route('theme_resource.update', $resource->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Display Grade (Stage) -->
                    <div class="mb-3">
                        <label for="stage_id" class="form-label">Grade</label>
                        <select id="stage_id" class="form-control" disabled>
                            <option value="{{ $resource->material->stage->id }}">
                                {{ $resource->material->stage->name }}
                            </option>
                        </select>
                        <input type="hidden" name="stage_id" value="{{ $resource->material->stage->id }}">
                    </div>

                    <!-- Display Theme (Material) -->
                    <div class="mb-3">
                        <label for="theme_id" class="form-label">Theme</label>
                        <select id="theme_id" class="form-control" disabled>
                            <option value="{{ $resource->material->id }}">
                                {{ $resource->material->title }}
                            </option>
                        </select>
                        <input type="hidden" name="theme_id" value="{{ $resource->material->id }}">
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" id="title" 
                               value="{{ $resource->title }}" required>
                    </div>

                    <!-- Select Schools Button -->
                    <button type="button" class="btn btn-secondary" id="selectSchoolsBtn">Edit School Visibility</button>

                    <button type="submit" class="btn btn-primary">Save Changes</button>

                    <input type="hidden" name="selected_schools" id="selected_schools" 
                           value='@json($resource->schools->pluck("id"))'>
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
    const selectSchoolsBtn = document.getElementById('selectSchoolsBtn');
    const schoolsList = document.getElementById('schoolsList');
    const saveSchoolsBtn = document.getElementById('saveSchools');
    const selectedSchoolsInput = document.getElementById('selected_schools');
    const selectAllBtn = document.getElementById('selectAllSchools');

    let allSchools = @json($schools);
    let selectedSchools = @json($resource->schools->pluck('id'));

    selectSchoolsBtn.addEventListener('click', function() {
        schoolsList.innerHTML = '';
        allSchools.forEach(school => {
            const div = document.createElement('div');
            div.classList.add('col-md-4');
            const isChecked = selectedSchools.includes(school.id);
            div.innerHTML = `
                <div class="form-check">
                    <input class="form-check-input school-checkbox" type="checkbox" value="${school.id}" ${isChecked ? 'checked' : ''} id="school_${school.id}">
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
        selectedSchools = Array.from(document.querySelectorAll('.school-checkbox:checked')).map(cb => parseInt(cb.value));
        selectedSchoolsInput.value = JSON.stringify(selectedSchools);
        bootstrap.Modal.getInstance(document.getElementById('schoolsModal')).hide();
    });
});
</script>
@endsection
