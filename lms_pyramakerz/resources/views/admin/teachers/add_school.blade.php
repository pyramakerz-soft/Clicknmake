@extends('admin.layouts.layout')

@section('content')
<div class="wrapper">
    @include('admin.layouts.sidebar')

    <div class="main">
        @include('admin.layouts.navbar')

        <main class="content">
            <div class="container-fluid p-0 mb-5">

                <h1>Teacher: {{$mainteacher->name}}'s Schools</h1>

                @if (session('error'))
                <div class="alert alert-danger d-flex justify-content-between align-items-center" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="table-responsive" style="overflow-x: auto;">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>School</th>
                                <th>Plain Password</th>
                                <th>Number of Logins</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $mainteacher->username }}</td>
                                <td>{{ $mainteacher->school->name}}</td>
                                <td>{{ $mainteacher->plain_password }}</td>
                                <td>{{ $mainteacher->num_logins }}</td>
                                <td class="d-flex align-items-center gap-2">
                                    <a href="{{ route('teachers.edit', $mainteacher->id) }}"
                                        class="btn btn-info">Edit</a>
                                    <!-- Delete button -->
                                    <form action="{{ route('teachers.destroy', $mainteacher->id) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" value="1" name="school_list_flag">
                                        <input type="hidden" value="{{$teacherAlias}}" name="teacher_aliases">
                                        <input type="hidden" value="{{$mainteacher->id}}" name="mainteacher">
                                        <button type="submit" class="btn btn-danger"
                                            onclick="return confirm('Are you sure you want to remove this teacher from this school?');">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @foreach ($teacherAlias as $teacher)
                            <tr>
                                <td>{{ $teacher->username }}</td>
                                <td>{{ $teacher->school->name}}</td>
                                <td>{{ $teacher->plain_password }}</td>
                                <td>{{ $teacher->num_logins }}</td>
                                <td class="d-flex align-items-center gap-2">
                                    <a href="{{ route('teachers.edit', $teacher->id) }}"
                                        class="btn btn-info">Edit</a>
                                    <!-- Delete button -->
                                    <form action="{{ route('teachers.destroy', $teacher->id) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" value="1" name="school_list_flag">
                                        <input type="hidden" value="{{$teacherAlias}}" name="teacher_aliases">
                                        <input type="hidden" value="{{$mainteacher->id}}" name="mainteacher">
                                        <button type="submit" class="btn btn-danger"
                                            onclick="return confirm('Are you sure you want to remove this teacher from this school?');">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>


            </div>
            <div class="container-fluid p-0 mb-5">
                <h1>Edit Teacher Info</h1>
                <form action="{{ route('teachers.update', $mainteacher->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="Name" class="form-label">Teacher Name</label>

                        <input type="text" name="name" id="name" class="form-control" value="{{$mainteacher->name}}" required>
                        <div class="invalid-feedback" style="display: none;">Name cannot contain numbers.</div>
                    </div>
                    <div class="mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select name="gender" id="gender" class="form-control">
                            <option value="boy" {{ (old('gender', $mainteacher->gender ?? '') == 'boy') ? 'selected' : '' }}>Boy</option>
                            <option value="girl" {{ (old('gender', $mainteacher->gender ?? '') == 'girl') ? 'selected' : '' }}>Girl</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Profile Image</label>
                        <input type="file" name="image" class="form-control" id="image" accept="image/*">
                    </div>
                    <input type="hidden" name="mainteacher" value="{{$mainteacher->id}}">
                    <button type="submit" class="btn btn-primary">Edit Teacher</button>
                </form>
            </div>
            <div class="container-fluid p-0">
                <h1>Add Teacher School</h1>
                <form action="{{ route('teachers.storeSchool') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="school_id" class="form-label">School</label>
                        <select name="school_id" id="school_id" class="form-control" required>
                            <option value="">-- Select School --</option>
                            @foreach ($schools as $school)
                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="stage_ids" class="form-label">Grades</label>
                        <select name="stage_ids[]" id="stage_ids" class="form-control" multiple required disabled>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="class_ids" class="form-label">Classes</label>
                        <select name="class_id[]" id="class_id" class="form-control" multiple required disabled>
                        </select>
                    </div>

                    <input type="hidden" name="mainteacher" value="{{$mainteacher->id}}">
                    <button type="submit" class="btn btn-primary">Add School</button>
                </form>
            </div>
        </main>


    </div>
</div>
@endsection

@section('page_js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('#stage_ids').select2({
            placeholder: "Select Stages",
            allowClear: true
        });
        $('#class_id').select2({
            placeholder: "Select Classes",
            allowClear: true
        });
    });
</script>
<script>
    document.getElementById('school_id').addEventListener('change', function() {
        let schoolId = this.value;
        if (schoolId) {
            const stagesUrl = `{{ route('admin.schools.stages', ':school') }}`.replace(':school', schoolId);

            fetch(stagesUrl)
                .then(response => response.json())
                .then(data => {
                    let stageSelect = document.getElementById('stage_ids');
                    stageSelect.innerHTML = '';
                    data.forEach(stage => {
                        stageSelect.innerHTML +=
                            `<option value="${stage.id}">${stage.name}</option>`;
                    });
                    stageSelect.disabled = false;
                });
        } else {
            document.getElementById('stage_ids').disabled = true;
        }
    });
    document.getElementById('school_id').addEventListener('change', function() {
        let schoolId = this.value;
        if (schoolId) {
            const classesUrl = `{{ route('admin.schools.classes', ':school') }}`.replace(':school', schoolId);

            fetch(classesUrl)
                .then(response => response.json())
                .then(data => {
                    let classSelect = document.getElementById('class_id');
                    classSelect.innerHTML = '';
                    data.forEach(classData => {
                        classSelect.innerHTML +=
                            `<option value="${classData.id}">${classData.name}</option>`;
                    });
                    classSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error fetching classes:', error);
                });
        } else {
            document.getElementById('class_id').innerHTML = '';
            document.getElementById('class_id').disabled = true;
        }
    });
</script>
@endsection