@extends('admin.layouts.layout')

@section('content')
    <div class="wrapper">
        @include('admin.layouts.sidebar')

        <div class="main">
            @include('admin.layouts.navbar')

            <main class="content">
                <div class="container-fluid p-0">

                    <h1>Assign Role</h1>
                    <form action="{{ route('admin.roles.assignUser') }}" method="POST">
                        @csrf
                        <select name="user_id">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <select name="role">
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit">Assign</button>
                    </form>

                </div>
            </main>


        </div>
    </div>
@endsection
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById("typeform");

        form.addEventListener("submit", function() {
            const submitButton = form.querySelector("[type='submit']");
            submitButton.disabled = true; // Disable the button
            submitButton.innerHTML = "Submitting..."; // Optional: Change button text
        });
    });
</script>
