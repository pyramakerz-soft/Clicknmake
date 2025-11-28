@extends('layouts.app')

@section('title')
    Create Ticket
@endsection
@section('page_css')
    <style>
        .custom-alert.success {
            background-color: #e9fbe9;
            color: #2d7a2d;
            border: 1px solid #b2e2b2;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .ticket-form-wrapper {
            max-width: 1000px;
            margin: 30px auto;
            padding: 25px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            font-family: Arial, sans-serif;
        }

        .ticket-form-wrapper h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        .ticket-form .form-group {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }

        .ticket-form label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #444;
        }

        .ticket-form input[type="text"],
        .ticket-form input[type="file"],
        .ticket-form textarea,
        .ticket-form select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            background-color: #f9f9f9;
            transition: border 0.3s ease;
        }

        .ticket-form input:focus,
        .ticket-form textarea:focus,
        .ticket-form select:focus {
            border-color: #3f51b5;
            outline: none;
        }

        .form-actions {
            text-align: right;
        }

        .form-actions button {
            padding: 10px 20px;
            background-color: #3f51b5;
            color: white;
            font-size: 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .form-actions button:hover {
            background-color: #2c3e9f;
        }

        .custom-alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .custom-alert.danger {
            background-color: #fdecea;
            color: #b30000;
            border: 1px solid #f5c2c7;
        }

        @media (max-width: 600px) {
            .form-actions {
                text-align: center;
            }
        }
    </style>
@endsection
@php
    $menuItems = [
        ['label' => 'Dashboard', 'icon' => 'fi fi-rr-table-rows', 'route' => route('teacher.dashboard')],
        ['label' => 'Resources', 'icon' => 'fi fi-rr-table-rows', 'route' => route('teacher.resources.index')],
        ['label' => 'Ticket', 'icon' => 'fa-solid fa-ticket', 'route' => route('teacher.tickets.index')],
        ['label' => 'Chat', 'icon' => 'fa-solid fa-message', 'route' => route('chat.all')],
    ];
@endphp

@section('sidebar')
    @include('components.sidebar', ['menuItems' => $menuItems])
@endsection

@section('content')
    @include('components.profile')

    <div class="ticket-form-wrapper">
        <h2>Create Support Ticket</h2>

        @if ($errors->any())
            <div class="custom-alert danger">
                <strong>Errors:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('success'))
            <div class="custom-alert success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" class="ticket-form">
            @csrf

            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" placeholder="Enter ticket title" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="5" placeholder="Describe the issue" required></textarea>
            </div>

            <div class="form-group">
                <select name="status" value="open" hidden>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Priority</label>
                <select name="priority" required>
                    @foreach ($priorities as $priority)
                        <option value="{{ $priority }}">{{ ucfirst($priority) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Attachment (optional)</label>
                <input type="file" name="attachment">
            </div>

            <div class="form-actions">
                <button type="submit">Submit Ticket</button>
            </div>
        </form>
    </div>
@endsection
