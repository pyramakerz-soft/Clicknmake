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
                            <option value="{{ $lesson->id }}">{{ $lesson->chapter->material->title ?? ''}}-{{ $lesson->chapter->unit->title ?? ''}}-{{$lesson->chapter->title ?? ''}}-{{ $lesson->title ?? ''}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="file_path" class="form-label">Upload Resource</label>
                        <input type="file" name="file_path" class="form-control" id="file_path" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Add Resource</button>
                </form>

            </div>
        </main>
    </div>
</div>
@endsection