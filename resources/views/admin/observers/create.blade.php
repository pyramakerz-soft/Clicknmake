@extends('admin.layouts.layout')


@section('page_css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
@endsection
@section('content')
    <div class="wrapper">
        @include('admin.layouts.sidebar')

        <div class="main">
            @include('admin.layouts.navbar')

            <main class="content">
                <div class="container-fluid p-0">

                    <h2>Create Observer</h2>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('observers.store') }}" id="observerform" method="POST"
                        enctype="multipart/form-data" autocomplete="off">
                        @csrf
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" name="fake_name" id="fake_name" class="form-control"
                                        style="display:none;">
                                    <input type="text" name="name" id="name" class="form-control"
                                        autocomplete="new-name" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" name="fake_username" id="fake_username" class="form-control"
                                        style="display:none;">
                                    <input type="text" name="username" id="username" class="form-control"
                                        autocomplete="new-username" required>
                                    <div class="invalid-feedback" style="display: none;">Username cannot contain numbers.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" name="fake_password" id="fake_password" class="form-control"
                                        style="display:none;">
                                    <input type="password" name="password" id="password" class="form-control"
                                        autocomplete="new-password" required>
                                    <div class="invalid-feedback" style="display: none;">Invalid Password</div>
                                </div>

                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <input type="password" name="fake_confirm_password" id="fake_confirm_password"
                                        class="form-control" style="display:none;">
                                    <input type="password" name="password_confirmation" id="confirm_password"
                                        class="form-control" autocomplete="new-password" required>
                                    <div class="invalid-feedback" style="display: none;">Invalid Password</div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select name="gender" id="gender" class="form-control" autocomplete="new-gender"
                                        required>
                                        <option selected disabled hidden></option>
                                        <option value="boy">Boy</option>
                                        <option value="girl">Girl</option>
                                    </select>
                                </div>

                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="gender" class="form-label">School</label>
                                    <select name="school_id[]" id="school_id" class="form-control select2" multiple
                                        required>
                                        @foreach ($schools as $school)
                                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>
                        </div>





                        <button type="submit" class="btn btn-primary">Create Observer</button>
                    </form>



                </div>
            </main>


        </div>
    </div>
@endsection
@section('page_js')
    <script>
        $(document).ready(function() {
            $('#school_id').select2({
                placeholder: "Select Schools",
                allowClear: true,
                width: "100%" // Ensures proper width inside Bootstrap
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById("observerform");

            form.addEventListener("submit", function() {
                const submitButton = form.querySelector("[type='submit']");
                submitButton.disabled = true; // Disable the button
                submitButton.innerHTML = "Submitting..."; // Optional: Change button text
            });
        });
    </script>
    <script>
        document.getElementById('school_id').addEventListener('change', function() {
            let schoolId = this.value;
            let stageSelect = document.getElementById('stage_id');
            let classSelect = document.getElementById('class_id');

            // Reset both the stage and class dropdowns
            stageSelect.innerHTML = '<option value="" selected disabled hidden>Select Stage</option>';
            classSelect.innerHTML = '<option value="" selected disabled hidden>Select Class</option>';

            if (schoolId) {
                fetch(`{{ route('admin.schools.stages', ':school') }}`.replace(':school', schoolId))
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(stage => {
                            stageSelect.innerHTML +=
                                `<option value="${stage.id}">${stage.name}</option>`;
                        });
                    });
            }
        });

        document.getElementById('stage_id').addEventListener('change', function() {
            let schoolId = document.getElementById('school_id').value;
            let stageId = this.value;
            let classSelect = document.getElementById('class_id');

            classSelect.innerHTML = '<option value="" selected disabled hidden>Select Class</option>';

            if (schoolId && stageId) {
                fetch(`{{ route('admin.schools.stages.classes', [':school', ':stage']) }}`
                        .replace(':school', schoolId)
                        .replace(':stage', stageId))
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(cls => {
                            classSelect.innerHTML += `<option value="${cls.id}">${cls.name}</option>`;
                        });
                    });
            }
        });


        document.getElementById('username').addEventListener('input', function(event) {
            const inputField = event.target;
            const invalidFeedback = inputField.nextElementSibling;

            const regex = /^[a-zA-Z][a-zA-Z0-9\s]*$/;

            if (!regex.test(inputField.value)) {
                inputField.classList.add('is-invalid');
                invalidFeedback.style.display = 'block';
            } else {
                inputField.classList.remove('is-invalid');
                invalidFeedback.style.display = 'none';
            }
        });
    </script>
@endsection
