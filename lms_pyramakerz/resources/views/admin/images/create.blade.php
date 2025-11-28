@extends('admin.layouts.layout')

@section('content')
    <div class="wrapper">
        @include('admin.layouts.sidebar')

        <div class="main">
            @include('admin.layouts.navbar')

            <main class="content">
                <div class="container-fluid p-0">

                    <h2>Upload Images</h2>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('images.store') }}" id="imagesform" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="images" class="form-label">Select Images</label>
                            <input type="file" name="images[]" id="images" class="form-control" multiple>
                        </div>

                        <button type="submit" class="btn btn-primary">Upload Images</button>
                    </form>

                </div>
            </main>

             
        </div>
    </div>
@endsection
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById("imagesform");

        form.addEventListener("submit", function() {
            const submitButton = form.querySelector("[type='submit']");
            submitButton.disabled = true; // Disable the button
            submitButton.innerHTML = "Submitting..."; // Optional: Change button text
        });
    });
</script>
