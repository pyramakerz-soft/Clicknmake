@extends('admin.layouts.layout')

@section('content')
    <div class="wrapper">
        @include('admin.layouts.sidebar')

        <div class="main">
            @include('admin.layouts.navbar')

            <main class="content">
                <div class="container py-4">
                    <div class="row justify-content-center">
                        <div class="col-md-10">
                            <div class="card shadow-sm">
                                <div class="card-header bg-dark d-flex justify-content-between align-items-center">
                                    <h5 class="text-light">Create New Role</h5>
                                    <a href="{{ route('admin.roles.index') }}" class="btn btn-light btn-sm">
                                        <i class="bi bi-arrow-left"></i> Back to Roles
                                    </a>
                                </div>

                                <div class="card-body">
                                    <form method="POST" action="{{ route('admin.roles.store') }}">
                                        @csrf

                                        <div class="mb-3">
                                            <label for="name" class="form-label">Role Name</label>
                                            <input type="text" id="name" name="name"
                                                class="form-control @error('name') is-invalid @enderror" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-success">
                                                <i class="bi bi-plus-circle"></i> Create Role
                                            </button>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection
