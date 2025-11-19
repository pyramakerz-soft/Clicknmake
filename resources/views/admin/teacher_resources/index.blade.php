@extends('admin.layouts.layout')

@section('content')
<div class="wrapper">
    @include('admin.layouts.sidebar')

    <div class="main">
        @include('admin.layouts.navbar')
        <main class="content">

            <div class="container-fluid p-0">
                <h1>Teacher Resources</h1>

                <!-- <a href="{{ route('teacher_resources.create') }}" class="btn btn-primary mb-3">Add Resource</a> -->

                @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Teacher</th>
                            <th>School</th>
                            <th>Name</th>
                            <th>Grade</th>
                            <th>Theme</th>
                            <th>Image</th>
                            <th>File / Video</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($resources as $resource)
                        <tr>
                            <td>{{ $resource->teacher->name ?? '—' }}</td>
                            <td>{{ $resource->school->name ?? '—' }}</td>
                            <td>{{ $resource->name }}</td>

                            {{-- Grade = Stage --}}
                            <td>{{ $resource->stage->name ?? '—' }}</td>

                            {{-- Theme = Material --}}
                            <td>{{ $resource->material->title ?? '—' }}</td>

                            {{-- Image --}}
                            <td>
                                @if ($resource->image)
                                <img src="{{ asset($resource->image) }}" width="70" class="rounded">
                                @else
                                <span class="text-muted">No Image</span>
                                @endif
                            </td>

                            {{-- File/Video --}}
                            <td>
                                @if ($resource->video_url)
                                <a href="{{ $resource->video_url }}" target="_blank" title="Watch Video">
                                    <i class="fas fa-play text-primary"></i>
                                </a>
                                @elseif ($resource->file_path)
                                <a href="{{ asset($resource->file_path) }}" target="_blank" title="View File">
                                    <i class="fas fa-eye text-primary"></i>
                                </a>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td>{{ ucfirst($resource->type ?? '—') }}</td>

                            {{-- Date --}}
                            <td>{{ $resource->date ? date('d M Y', strtotime($resource->date)) : '—' }}</td>

                            {{-- Actions --}}
                            <td class="d-flex gap-2">
                                <a href="{{ route('teacher_resources.edit', $resource->id) }}"
                                    class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('admin.resources.view', [$resource->id, "teacher"]) }}" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>

                                <form action="{{ route('teacher_resources.destroy', $resource->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this resource?');"
                                        title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>

                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>
@endsection