@extends('layouts.app')

@section('title', 'Chat')

@section('sidebar')
    @include('components.sidebar', [
        'menuItems' => [
            ['label' => 'Dashboard', 'icon' => 'fi fi-rr-table-rows', 'route' => route('teacher.dashboard')],
            // ['label' => 'Chat', 'icon' => 'fas fa-comments', 'route' => route('teacher.chat')],
        ],
    ])
@endsection

@section('content')
    <div class="p-4">
        <h1 class="text-2xl font-bold">Chat</h1>


    </div>


@endsection
@section('page_js')

@endsection
