@php
    session(['schools_previous_url' => url()->full()]);
@endphp
@extends('admin.layouts.layout')
@section('content')
    <div class="wrapper">
        @include('admin.layouts.sidebar')

        <div class="main">
            @include('admin.layouts.navbar')

            <main class="content">
                <div class="container-fluid p-0">


                    <h1>Schools</h1>




                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @can('create school')
                        <a href="{{ route('admins.create') }}" class="btn btn-primary mb-3">Add School</a>
                    @endcan
                    <form action="{{ route('admins.index') }}" method="get">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <input type="text" name="name" class="form-control" placeholder="Search by name"
                                    value="{{ request('name') }}">
                            </div>
                            <div class="col-md-auto">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('admins.index') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>City</th>
                                    <th>Type</th>
                                    <th>Students Count</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($schools as $school)
                                    <tr>
                                        <td>{{ $school->name ?? '-' }}</td>
                                        <td>{{ $school->address ?? '-' }}</td>
                                        <td>{{ $school->city ?? '-' }}</td>
                                        <td>{{ $school->type->name ?? '-' }}</td>
                                        <td>{{ $school->students->count() ?? '-' }}</td>
                                        <td class="d-flex align-items-center gap-2">
                                            @can('update school')
                                                <a href="{{ route('admins.edit', $school->id) }}" class="btn btn-info">Edit</a>
                                            @endcan
                                            @can('delete school')
                                                <form action="{{ route('admins.destroy', $school->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger"
                                                        onclick="return confirm('Are you sure you want to delete this school?');">Delete</button>
                                                </form>
                                            @endcan
                                            <!-- Button to assign curriculum -->
                                            @can('create school')
                                                <a href="{{ route('school.curriculum.assign', $school->id) }}"
                                                    class="btn btn-success">Add Curriculum</a>

                                                <!-- New Button to view curriculum -->
                                                <a href="{{ route('school.curriculum.view', $school->id) }}"
                                                    class="btn btn-primary">View Curriculum</a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- End of scrollable wrapper -->
                    {{ $schools->appends(request()->input())->links('pagination::bootstrap-5') }}
                </div>
            </main>
        </div>
    </div>
@endsection
