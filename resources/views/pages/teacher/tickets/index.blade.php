@extends('layouts.app')

@section('title', 'My Tickets')
@section('page_css')
    <style>
        .ticket-container {
            padding: 24px;
            font-family: 'Segoe UI', sans-serif;
            color: #333;
        }

        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .btn-primary {
            padding: 8px 16px;
            background-color: #1d4ed8;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: 0.2s;
        }

        .btn-primary:hover {
            background-color: #2563eb;
        }

        .alert-success {
            padding: 10px;
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
            border-radius: 4px;
            margin-bottom: 16px;
        }

        .no-tickets {
            font-style: italic;
            color: #888;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .custom-table th,
        .custom-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .custom-table thead {
            background-color: #f9fafb;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-open {
            color: #f59e0b;
        }

        .status-in_progress {
            color: #3b82f6;
        }

        .status-resolved {
            color: #10b981;
        }

        .status-closed {
            color: rgb(228, 17, 17);
        }

        @media screen and (max-width: 600px) {
            .ticket-header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }

            .btn-primary {
                width: 100%;
                text-align: center;
            }
        }
    </style>
@endsection
@php
    $menuItems = [
        ['label' => 'Dashboard', 'icon' => 'fi fi-rr-table-rows', 'route' => route('teacher.dashboard')],
        ['label' => 'Resources', 'icon' => 'fi fi-rr-table-rows', 'route' => route('teacher.resources.index')],
        ['label' => 'Tickets', 'icon' => 'fa-solid fa-ticket', 'route' => route('teacher.tickets.index')],
        ['label' => 'Chat', 'icon' => 'fa-solid fa-message', 'route' => route('chat.all')],
    ];
@endphp

@section('sidebar')
    @include('components.sidebar', ['menuItems' => $menuItems])
@endsection

@section('content')
    @include('components.profile')

    <div class="ticket-container">
        <div class="ticket-header">
            <h2>My Tickets</h2>
            <a href="{{ route('tickets.create') }}" class="btn-primary">+ New Ticket</a>
        </div>

        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        @if ($tickets->isEmpty())
            <p class="no-tickets">No tickets submitted yet.</p>
        @else
            <div class="table-wrapper">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Attachment</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tickets as $ticket)
                            <tr>
                                <td>{{ $ticket->title }}</td>
                                <td>
                                    <span class="status-badge status-{{ $ticket->status }}">
                                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                </td>
                                <td>{{ ucfirst($ticket->priority) }}</td>
                                <td>
                                    @if ($ticket->attachment)
                                        <a href="{{ asset('/' . $ticket->attachment) }}" target="_blank">View</a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $ticket->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
