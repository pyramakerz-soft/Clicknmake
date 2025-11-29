@extends('admin.layouts.layout')

@section('content')
    <div class="wrapper">
        @include('admin.layouts.sidebar')

        <div class="main">
            @include('admin.layouts.navbar')

            <main class="content">
                <div class="container-fluid p-0">

                    <h1>Add Ebook</h1>
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
                    @can('create ebook')
                        <form action="{{ route('ebooks.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="lesson_id" class="form-label">Grade</label>
                                <select name="grade" id="grade" class="form-control" required>
                                    <option value="" disabled selected>Select Grade</option>
                                    @foreach ($grades as $grade)
                                        <option value="{{ $grade }}">{{ $grade }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="file_path" class="form-label">Upload Ebook </label>
                                <input type="file" name="file_path" class="form-control" id="file_path" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Add Ebook</button>
                        </form>
                    @endcan
                </div>
            </main>


        </div>
    </div>
@endsection
