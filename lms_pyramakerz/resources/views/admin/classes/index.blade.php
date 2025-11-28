@extends('admin.layouts.layout')

@section('content')
<div class="wrapper">
    @include('admin.layouts.sidebar')

    <div class="main">
        @include('admin.layouts.navbar')

        <main class="content">
            <div class="container-fluid p-0">
                <h1>Classes</h1>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <!-- School Filter Form -->
                <form action="{{ route('classes.index') }}" method="GET" class="d-flex mb-3">
                    <select name="school_id" class="form-select" style="width: 200px; margin-right: 10px;">
                        <option value="">All Schools</option>
                        @foreach ($schools as $school)
                            <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>

                @can('create group')
                    <a href="{{ route('classes.create') }}" class="btn btn-primary mb-3">Add Class</a>
                @endcan
                
                @can('update group')
                    <button type="button" id="promote-selected" class="btn btn-warning mb-3" disabled>
                        Promote Selected Classes to Next Grade
                    </button>
                @endcan

                <!-- MULTISELECT FORM -->
                <form id="bulkActionForm" action="#" method="POST">
                    @csrf

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select-all"></th>
                                    <th>Name</th>
                                    <th>School</th>
                                    <th>Grade</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($classes as $class)
                                    <tr>
                                        <td><input type="checkbox" name="class_ids[]" value="{{ $class->id }}" class="select-class"></td>
                                        <td>{{ $class->name }}</td>
                                        <td>{{ $class->school->name }}</td>
                                        <td>{{ $class->stage->name }}</td>
                                        <td class="d-flex align-items-center gap-2">
                                            @can('update group')
                                                <a href="{{ route('classes.edit', $class->id) }}" class="btn btn-info">Edit</a>
                                            @endcan
                                            @can('delete group')
                                                <form action="{{ route('classes.destroy', $class->id) }}" method="POST" style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger"
                                                        onclick="return confirm('Are you sure you want to delete this class?');">
                                                        Delete
                                                    </button>
                                                </form>
                                            @endcan
                                            <a href="{{ route('classes.import', $class->id) }}" class="btn btn-secondary">Import Students</a>
                                            <a href="{{ route('classes.export', $class->id) }}" class="btn btn-success">Export Students</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>

                {{ $classes->appends(request()->input())->links('pagination::bootstrap-5') }}
            </div>
        </main>
    </div>
</div>
@endsection


@section('page_js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Handle select all checkbox
    $('#select-all').on('change', function() {
        $('.select-class').prop('checked', this.checked);
        togglePromoteButton();
    });

    // Handle individual checkbox change
    $('.select-class').on('change', function() {
        if ($('.select-class:checked').length === $('.select-class').length) {
            $('#select-all').prop('checked', true);
        } else {
            $('#select-all').prop('checked', false);
        }
        togglePromoteButton();
    });

    function togglePromoteButton() {
        if ($('.select-class:checked').length > 0) {
            $('#promote-selected').prop('disabled', false);
        } else {
            $('#promote-selected').prop('disabled', true);
        }
    }

    // Handle promote selected button click
    $('#promote-selected').on('click', function() {
        const selectedClassIds = $('.select-class:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedClassIds.length === 0) return;

        Swal.fire({
            title: 'Promote Selected Classes?',
            text: "This will move selected classes and their students to the next grade level.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Promote!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('classes.promoteMultiple') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        class_ids: selectedClassIds
                    },
                    success: function(response) {
                        Swal.fire('Success', response.message, 'success').then(() => location.reload());
                    },
                    error: function(xhr) {
                        let msg = xhr.responseJSON?.message || 'Something went wrong!';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            }
        });
    });
});
</script>
@endsection
