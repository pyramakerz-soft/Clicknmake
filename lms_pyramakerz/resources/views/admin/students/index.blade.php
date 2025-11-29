@extends('admin.layouts.layout')

@section('content')
    <div class="wrapper">
        @include('admin.layouts.sidebar')

        <div class="main">
            @include('admin.layouts.navbar')

            <main class="content">
                <div class="container-fluid p-0">
                    <h1>Students</h1>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @can('create student')
                        <a href="{{ route('students.create') }}" class="btn btn-primary mb-3">Add Student</a>
                    @endcan
                    <form id="filterForm" action="{{ route('students.index') }}" method="GET"
                        class="d-flex justify-content-evenly mb-3">

                        <input type="text" name="search" class="form-control w-25" placeholder="Search by username"
                            value="{{ request('search') }}">
                        <select name="school" id="school" class="form-select w-25">
                            <option disabled selected hidden>Filter By School</option>
                            @foreach ($schools as $school)
                                <option value="{{ $school->id }}"
                                    {{ request('school') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                            @endforeach
                        </select>
                        <select name="class" id="class" class="form-select w-25">
                            <option disabled selected hidden>Filter By Class</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class') == $class->id ? 'selected' : '' }}>
                                    {{ $class->stage->name }}-{{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary">Search</button>

                        <a class="btn btn-secondary" href="{{ route('students.index') }}">Clear</a>
                    </form>

                    <form id="deleteMultipleForm" action="{{ route('students.deleteMultiple') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="school" value="{{ request('school') }}">
                        <input type="hidden" name="class" value="{{ request('class') }}">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="select-all"></th> <!-- Master Checkbox -->
                                        <th>Image</th>
                                        <th>Username</th>
                                        <th>Password</th>
                                        <th>Gender</th>
                                        <th>School</th>
                                        <th>Stage</th>
                                        <th>Class</th>
                                        <th>#Logins</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($students as $student)
                                        <tr>
                                            <td><input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                                    class="select-student"></td>
                                            <td>
                                                @if ($student->image)
                                                    <img src="{{ asset($student->image) }}" alt="Student Image"
                                                        width="50" height="50" class="rounded-circle">
                                                @else
                                                    <img src="https://w7.pngwing.com/pngs/184/113/png-transparent-user-profile-computer-icons-profile-heroes-black-silhouette-thumbnail.png"
                                                        alt="Teacher Image" width="50" height="50"
                                                        class="rounded-circle">
                                                @endif
                                            </td>
                                            <td>{{ $student->username ?? '' }}</td>
                                            <td>{{ $student->plain_password }}</td>
                                            <td>{{ ucfirst($student->gender) }}</td>
                                            <td>{{ $student->school->name ?? '' }}</td>
                                            <td>{{ $student->stage->name ?? '' }}</td>
                                            <td>{{ $student->classes->name ?? '' }}</td>
                                            <td>{{ $student->num_logins }}</td>
                                            <td class="d-flex align-items-center gap-2">
                                                @can('update student')
                                                    <a href="{{ route('students.edit', $student->id) }}"
                                                        class="btn btn-info">Edit</a>
                                                @endcan
                                                @can('delete student')
                                                    <form action="{{ route('students.destroy', $student->id) }}" method="POST"
                                                        style="display:inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger"
                                                            onclick="return confirm('Are you sure you want to delete this student?');">
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Delete Selected Button -->
                        @can('delete student')
                            <button type="submit" id="delete-selected" class="btn btn-danger mt-3" disabled>
                                Delete Selected
                            </button>
                        @endcan
                        @can('update student')
                            <button type="button" id="bulk-update-btn" class="btn btn-warning mt-3" disabled>
                                Change Stage/Class
                            </button>
                        @endcan
                    </form>


                </div>
                {{-- {{ $students->links('pagination::bootstrap-5') }} --}}
                {{ $students->appends(request()->input())->links('pagination::bootstrap-5') }}

            </main>

        </div>
    </div>
@endsection

@section('page_js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
        $(document).ready(function() {
            $('#school, #class').change(function() {
                $('#filterForm').submit();
            });
        });

        $('#clearFilters').click(function(e) {
            e.preventDefault();
            $('#school').val('').prop('selected', true);
            $('#class').val('').prop('selected', true);
            $('#filterForm').submit();
        });
</script>
<script>
let allSelected = false; // global flag

$(document).ready(function () {
    // 🔹 Handle "Select All" checkbox
    $('#select-all').on('change', function () {
        if (this.checked) {
            // Select all on this page
            $('.select-student').prop('checked', true);
            toggleBulkButtons();

            // Ask if user wants ALL students across pages
            Swal.fire({
                title: "Select All Students?",
                text: "You have selected all students on this page. Do you want to select ALL students across ALL pages (with current filters)?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Yes, Select All",
                cancelButtonText: "Only This Page"
            }).then((result) => {
                if (result.isConfirmed) {
                    allSelected = true;
                } else {
                    allSelected = false;
                }
            });
        } else {
            // Unselect everything
            $('.select-student').prop('checked', false);
            allSelected = false;
            toggleBulkButtons();
        }
    });

    // 🔹 Handle individual checkboxes
    $('.select-student').on('change', function () {
        if ($('.select-student:checked').length === $('.select-student').length) {
            $('#select-all').prop('checked', true);
        } else {
            $('#select-all').prop('checked', false);
        }

        // 👉 If user manually checks/unchecks, cancel "ALL" mode
        allSelected = false;
        toggleBulkButtons();
    });

    // 🔹 Enable/disable bulk buttons
    function toggleBulkButtons() {
        if ($('.select-student:checked').length > 0 || allSelected) {
            $('#delete-selected, #bulk-update-btn').prop('disabled', false);
        } else {
            $('#delete-selected, #bulk-update-btn').prop('disabled', true);
        }
    }

    // 🔹 Handle bulk update button
   $('#bulk-update-btn').click(function () {
    let studentIds = [];
    if (allSelected) {
        studentIds = "ALL"; // special flag
    } else {
        studentIds = $('.select-student:checked').map(function () {
            return $(this).val();
        }).get();
    }

    Swal.fire({
        title: 'Update School, Stage & Class',
        html: `
            <form id="bulkUpdateForm" class="text-start">
                <label for="school_id">School:</label>
                <select id="swal_school_id" class="form-control" required>
                    <option value="" selected disabled hidden>Select School</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                    @endforeach
                </select>
                <br><br>
                <label for="stage_id">Stage:</label>
                <select id="swal_stage_id" class="form-control" required>
                    <option value="" selected disabled hidden>Select Stage</option>
                </select>
                <br><br>
                <label for="class_id">Class:</label>
                <select id="swal_class_id" class="form-control" required>
                    <option value="" selected disabled hidden>Select Class</option>
                </select>
            </form>
        `,
        focusConfirm: false,
        didOpen: () => {
            const schoolSelect = document.getElementById('swal_school_id');
            const stageSelect = document.getElementById('swal_stage_id');
            const classSelect = document.getElementById('swal_class_id');

            // when school changes → fetch stages
            schoolSelect.addEventListener('change', function() {
                clearSelect(stageSelect);
                clearSelect(classSelect);
                if (this.value) {
                    fetchStages(this.value);
                }
            });

            // when stage changes → fetch classes
            stageSelect.addEventListener('change', function() {
                clearSelect(classSelect);
                if (schoolSelect.value && this.value) {
                    fetchClasses(schoolSelect.value, this.value);
                }
            });

            function fetchStages(schoolId) {
                fetch(`{{ route('admin.schools.stages', ':school') }}`.replace(':school', schoolId))
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(stage => {
                            stageSelect.add(new Option(stage.name, stage.id));
                        });
                    })
                    .catch(err => console.error(err));
            }

            function fetchClasses(schoolId, stageId) {
                fetch(`{{ route('admin.schools.stages.classes', [':school', ':stage']) }}`
                    .replace(':school', schoolId).replace(':stage', stageId))
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(cls => {
                            classSelect.add(new Option(cls.name, cls.id));
                        });
                    })
                    .catch(err => console.error(err));
            }

            function clearSelect(selectElement) {
                selectElement.innerHTML = '<option value="" selected disabled hidden>Select</option>';
            }
        },
        preConfirm: () => {
            return {
                school_id: document.getElementById('swal_school_id').value,
                stage_id: document.getElementById('swal_stage_id').value,
                class_id: document.getElementById('swal_class_id').value
            }
        },
        showCancelButton: true,
        confirmButtonText: 'Update'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('students.bulkUpdate') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    student_ids: studentIds,
                    school_id: result.value.school_id,
                    stage_id: result.value.stage_id,
                    class_id: result.value.class_id,
                    school: "{{ request('school') }}",
                    class_filter: "{{ request('class') }}",
                    search: "{{ request('search') }}"
                },
                success: function (response) {
                    Swal.fire('Success', 'Students updated successfully!', 'success')
                        .then(() => location.reload());
                },
                error: function (xhr) {
                    let message = 'Something went wrong!';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', message, 'error');
                }
            });
        }
    });
});


    // 🔹 Handle delete selected button
    $('#deleteMultipleForm').on('submit', function (e) {
        if (allSelected) {
            e.preventDefault();
            Swal.fire({
                title: "Delete ALL students?",
                text: "This will delete ALL students across ALL pages (with current filters). Are you sure?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, Delete All"
            }).then((result) => {
                if (result.isConfirmed) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'student_ids',
                        value: 'ALL'
                    }).appendTo('#deleteMultipleForm');
                    e.currentTarget.submit();
                }
            });
        }
    });
});
</script>

@endsection

