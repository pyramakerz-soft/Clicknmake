@extends('admin.layouts.layout')

@section('title', 'Edit Role')

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
                                <div
                                    class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                    <h5 class="text-light">Edit Role</h5>
                                    <a href="{{ route('admin.roles.index') }}" class="btn btn-light btn-sm">
                                        <i class="bi bi-arrow-left"></i> Back to Roles
                                    </a>
                                </div>

                                <div class="card-body">
                                    {{-- Role Edit Form --}}
                                    <form method="POST" action="{{ route('admin.roles.update', $role->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Role Name</label>
                                            <input type="text" id="name" name="name" value="{{ $role->name }}"
                                                class="form-control @error('name') is-invalid @enderror">
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            {{-- <label for="permission"
                                                class="form-label d-flex justify-content-between align-items-center">
                                                <span>Select Permissions</span>
                                                <button type="button" id="select-all-permissions"
                                                    class="btn btn-sm btn-outline-primary">
                                                    Select All
                                                </button>
                                            </label> --}}
                                            <label class="form-label d-flex justify-content-between align-items-center">
                                                <span>Select Permissions</span>
                                                <input type="checkbox" id="select-all-global"> Select All
                                            </label>

                                            @foreach ($groupedPermissions as $model => $permissions)
                                                <div class="mb-4 border rounded p-3">
                                                    <div class="form-check mb-2">
                                                        <input type="checkbox" class="form-check-input model-master"
                                                            data-model="{{ $model }}">
                                                        <label class="form-check-label fw-bold">{{ ucfirst($model) }}
                                                            Permissions</label>
                                                    </div>

                                                    <div class="row">
                                                        @foreach ($permissions as $permission)
                                                            <div class="col-md-3">
                                                                <div class="form-check">
                                                                    <input
                                                                        class="form-check-input model-checkbox model-{{ $model }}"
                                                                        type="checkbox" name="permissions[]"
                                                                        value="{{ $permission->id }}"
                                                                        id="perm-{{ $permission->id }}"
                                                                        {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}>
                                                                    <label class="form-check-label"
                                                                        for="perm-{{ $permission->id }}">
                                                                        {{ ucfirst(Str::afterLast($permission->name, '-')) }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach





                                        </div>

                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-success">
                                                <i class="bi bi-save"></i> Update Role
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

    {{-- Include Select2 JS --}}
@section('page_js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.group-checkbox').forEach(groupCheck => {
                groupCheck.addEventListener('change', function() {
                    const groupClass = 'group-' + this.id.replace('group-', '');
                    document.querySelectorAll('.' + groupClass).forEach(cb => {
                        cb.checked = this.checked;
                    });
                });
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Global Select All
            document.getElementById('select-all-global').addEventListener('change', function() {
                const all = document.querySelectorAll('.model-checkbox, .model-master');
                all.forEach(cb => cb.checked = this.checked);
            });

            // Model Group Select All
            document.querySelectorAll('.model-master').forEach(masterCheckbox => {
                masterCheckbox.addEventListener('change', function() {
                    const model = this.dataset.model;
                    document.querySelectorAll('.model-' + model).forEach(cb => {
                        cb.checked = this.checked;
                    });
                });
            });
        });
    </script>
@endsection
@endsection
