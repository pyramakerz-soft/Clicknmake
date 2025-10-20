@extends('admin.layouts.layout')

@section('content')
    <div class="wrapper">
        @include('admin.layouts.sidebar')

        <div class="main">
            @include('admin.layouts.navbar')

            <main class="content py-4">
                <div class="container">
                    @if (Session::has('message'))
                        <div class="alert-banner alert alert-primary d-flex align-items-center justify-content-between rounded px-4 py-3 shadow-sm"
                            role="alert">
                            <div class="d-flex align-items-center">
                                <span class="icon-container me-3">
                                    <i class="bi bi-megaphone-fill"></i>
                                </span>
                                <span class="message-text">{{ Session::get('message') }}</span>
                            </div>
                            <button type="button" class="btn-close text-white" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">User Details</h5>
                            <a href="{{ route('users.index') }}" class="btn btn-light btn-sm">
                                <i class="bi bi-arrow-left"></i> Back to Users
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h6 class="fw-bold">User Name:</h6>
                                <p class="text-muted">{{ $user->name }}</p>
                            </div>
                            <div class="mb-3">
                                <h6 class="fw-bold">User Email:</h6>
                                <p class="text-muted">{{ $user->email }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Roles Section --}}
                    <div class="card shadow-sm border-0 mt-4">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0 text-light">User Roles</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3 d-flex flex-wrap gap-2">
                                @if ($user->roles)
                                    @foreach ($user->roles as $user_role)
                                        <form method="POST"
                                            action="{{ route('users.roles.remove', [$user->id, $user_role->id]) }}"
                                            onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-x-circle"></i> {{ $user_role->name }}
                                            </button>
                                        </form>
                                    @endforeach
                                @endif
                            </div>
                            <form method="POST" action="{{ route('users.roles', $user->id) }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="role" class="form-label">Assign New Role</label>
                                    <select id="role" name="role" class="form-select">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('role')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-plus-circle"></i> Assign Role
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Permissions Section --}}
                    {{-- <div class="card shadow-sm border-0 mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">User Permissions</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 d-flex flex-wrap gap-2">
                            @if ($user->permissions)
                                @foreach ($user->permissions as $user_permission)
                                    <form method="POST" action="{{ route('users.permissions.revoke', [$user->id, $user_permission->id]) }}"
                                          onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-x-circle"></i> {{ $user_permission->name }}
                                        </button>
                                    </form>
                                @endforeach
                            @endif
                        </div>
                        <form method="POST" action="{{ route('users.permissions', $user->id) }}">
                            @csrf
                            <div class="mb-3">
                                <label for="permission" class="form-label">Assign New Permission</label>
                                <select id="permission" name="permission" class="form-select">
                                    @foreach ($permissions as $permission)
                                        <option value="{{ $permission->name }}">{{ $permission->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('permission')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-plus-circle"></i> Assign Permission
                            </button>
                        </form>
                    </div>
                </div> --}}

                </div>
            </main>
        </div>
    </div>
@endsection
