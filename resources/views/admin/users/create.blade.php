@extends('admin.layouts.layout')

@section('content')
    <div class="wrapper">
        @include('admin.layouts.sidebar')

        <div class="main">
            @include('admin.layouts.navbar')

            <main class="content">
                <div class="container-fluid p-0">

                    <h2>Create users</h2>

                    {{-- @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif --}}

                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">name</label>

                            <input type="text" name="username" id="name" class="form-control" required>

                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>

                            <input type="email" name="email" id="email" class="form-control" required>

                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>

                            <input type="password" name="password" id="password" class="form-control" required>

                        </div>

                        <button type="submit" class="btn btn-primary">Create User</button>
                    </form>

                </div>
            </main>


        </div>
    </div>
@endsection
@section('page_js')
@endsection
