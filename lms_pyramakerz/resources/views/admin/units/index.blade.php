@extends('admin.layouts.layout')

@section('content')
    <div class="wrapper">
        @include('admin.layouts.sidebar')

        <div class="main">
            @include('admin.layouts.navbar')

            <main class="content">
                <div class="container-fluid p-0">

                    <h1>Units</h1>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @can('create unit')
                        <a href="{{ route('units.create') }}" class="btn btn-primary mb-3">Add Unit</a>
                    @endcan
                    <!-- Add scrollable wrapper for horizontal scroll -->
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Theme</th>
                                    <th>Image</th>
                                    <th>Active</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($units as $unit)
                                    <tr>
                                        <td>{{ $unit->title }}</td>
                                        <td>{{ $unit->material->title ?? ' ' }}</td>
                                        <td>
                                            @if ($unit->image)
                                                <img src="{{ asset($unit->image) }}" alt="{{ $unit->title }}"
                                                    width="100">
                                            @else
                                                No Image
                                            @endif
                                        </td>
                                        <td>{{ $unit->is_active ? 'Active' : 'Inactive' }}</td>
                                        <td class="d-flex align-items-center gap-2">
                                            @can('update unit')
                                                <a href="{{ route('units.edit', $unit->id) }}" class="btn btn-info">Edit</a>
                                            @endcan
                                            @can('delete unit')
                                                <form action="{{ route('units.destroy', $unit->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger"
                                                        onclick="return confirm('Are you sure you want to delete this unit?');">Delete</button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $units->appends(request()->input())->links('pagination::bootstrap-5') }}

                </div>
            </main>

        </div>
    </div>
@endsection
