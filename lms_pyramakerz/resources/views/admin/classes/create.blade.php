@extends('admin.layouts.layout')

@section('content')
<div class="wrapper">
    @include('admin.layouts.sidebar')

    <div class="main">
        @include('admin.layouts.navbar')

        <main class="content">
            <div class="container-fluid p-0">

                <h2>Create Class</h2>

                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('classes.store') }}" id="classform" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Image (Optional)</label>
                        <input type="file" name="image" id="image" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="school_id" class="form-label">School</label>
                        <select name="school_id" id="school_id" class="form-control" required>
                            <option selected disabled hidden></option>
                            @foreach ($schools as $school)
                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="stage_id" class="form-label">Grade</label>
                        <select name="stage_id" id="stage_id" class="form-control" required>
                            <option selected disabled> Choose a School</option>
                        </select>
                    </div>



                    <button type="submit" class="btn btn-primary">Create Class</button>
                </form>

            </div>
        </main>


    </div>
</div>
@endsection
@section('page_js')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById("classform");

        form.addEventListener("submit", function() {
            const submitButton = form.querySelector("[type='submit']");
            submitButton.disabled = true; // Disable the button
            submitButton.innerHTML = "Submitting..."; // Optional: Change button text
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('.js-select2').select2();

        $('#school_id').change(function() {
            var schoolId = $('#school_id').val();
            var selectedStageId = "{{$request['stage_id'] ?? '' }}";
            getSchoolGrades(schoolId, selectedStageId);

        });
        $('#school_id').trigger('change');
    });

    function getSchoolGrades(schoolId, selectedStageId) {
        $.ajax({
            url: '/LMS/lms_pyramakerz/public/admin/get-grades-school/' + schoolId,
            type: "GET",
            dataType: "json",
            success: function(data) {
                // Clear the existing options
                $('select[name="stage_id"]').empty();
                if (!data || data.length === 0) {
                    $('select[name="stage_id"]').append(
                        '<option value="" selected disabled>No Available Grade</option>'
                    );
                } else {
                    $('select[name="stage_id"]').append(
                        '<option value="" selected disabled>Choose a Grade</option>'
                    );
                    $.each(data, function(key, value) {
                        $('select[name="stage_id"]').append(
                            '<option value="' + value.id + '">' + value.name + '</option>'
                        );
                    });
                    if (selectedStageId) {
                        $('select[name="stage_id"]').val(selectedStageId).trigger('change');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }
</script>
@endsection