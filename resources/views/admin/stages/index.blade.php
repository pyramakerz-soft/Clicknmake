@extends('admin.layouts.layout')

@section('content')
    <div class="wrapper">
        @include('admin.layouts.sidebar')

        <div class="main">
            @include('admin.layouts.navbar')

            <main class="content">
                <div class="container-fluid p-0">

                    <h1>Grades</h1>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @can('create stage')
                        <a href="{{ route('stages.create') }}" class="btn btn-primary mb-3">Add Grade</a>
                    @endcan
                    <!-- Add scrollable wrapper for horizontal scroll -->
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Grade Name</th>
                                    <th>Image</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stages as $stage)
                                    <tr>
                                        <td>{{ $stage->name }}</td>
                                        <td>
                                            @if ($stage->image)
                                                <img src="{{ asset($stage->image) }}" alt="{{ $stage->name }}"
                                                    width="100">
                                            @else
                                                No Image
                                            @endif
                                        </td>
                                        <td class="d-flex align-items-center gap-2">
                                            @can('create material')
                                                <a href="{{ route('material.unit.chapter.create', $stage->id) }}"
                                                    class="btn btn-primary">Add Material</a>
                                            @endcan
                                            @can('update stage')
                                                <a href="{{ route('stages.edit', $stage->id) }}" class="btn btn-info">Edit</a>
                                            @endcan
                                            @can('delete stage')
                                                <form action="{{ route('stages.destroy', $stage->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger"
                                                        onclick="return confirm('Are you sure you want to delete this stage?');">Delete</button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $stages->appends(request()->input())->links('pagination::bootstrap-5') }}
                </div>
            </main>

        </div>
    </div>
@endsection
