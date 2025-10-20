@extends('admin.layouts.layout')
@section('page_css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
@endsection
@section('content')
    <div class="wrapper">
        @include('admin.layouts.sidebar')

        <div class="main">
            @include('admin.layouts.navbar')

            <main class="content">
                <div class="container-fluid p-0">






                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-hover align-middle table-bordered animate__animated animate__fadeIn">

                            <thead>
                                <tr>
                                    <th>Teacher</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Attachment</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tickets as $ticket)
                                    <tr>
                                        <td>{{ $ticket->teacher->name }}</td>
                                        <td>{{ $ticket->title }}</td>
                                        <td>
                                            <span
                                                class="badge 
                                                {{ $ticket->status == 'open' ? 'bg-secondary' : '' }}
                                                {{ $ticket->status == 'in_progress' ? 'bg-warning text-dark' : '' }}
                                                {{ $ticket->status == 'resolved' ? 'bg-success' : '' }}
                                                {{ $ticket->status == 'closed' ? 'bg-danger' : '' }}">
                                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="badge 
                                                {{ $ticket->priority == 'low' ? 'bg-success' : '' }}
                                                {{ $ticket->priority == 'medium' ? 'bg-warning text-dark' : '' }}
                                                {{ $ticket->priority == 'high' ? 'bg-danger' : '' }}{{ $ticket->priority == 'urgent' ? 'bg-dark' : '' }}">
                                                {{ ucfirst($ticket->priority) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($ticket->attachment)
                                                <a href="{{ asset('/' . $ticket->attachment) }}" target="_blank">View</a>
                                            @else
                                                No file
                                            @endif
                                        </td>
                                        <td>{{ $ticket->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary view-details"
                                                data-ticket='@json($ticket)'
                                                data-teacher="{{ $ticket->teacher->name }}">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            @can('ticket-edit')
                                                <form action="{{ route('admin.tickets.updateStatus', $ticket->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <select name="status" class="form-select form-select-sm d-inline w-auto"
                                                        onchange="this.form.submit()">
                                                        @foreach (['open', 'in_progress', 'resolved', 'closed'] as $status)
                                                            <option value="{{ $status }}"
                                                                {{ $ticket->status === $status ? 'selected' : '' }}>
                                                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                    <div class="modal fade" id="ticketModal" tabindex="-1"
                                        aria-labelledby="ticketModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Ticket Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <h5 class="card-title"><strong>Title:</strong> <span
                                                                    id="modal-title"></span>
                                                            </h5>
                                                            <p><strong>Teacher:</strong> <span id="modal-teacher"></span>
                                                            </p>
                                                            <p><strong>Status:</strong> <span class="badge bg-info"
                                                                    id="modal-status"></span></p>
                                                            <p><strong>Priority:</strong> <span class="badge bg-danger"
                                                                    id="modal-priority"></span></p>
                                                            <p><strong>Description:</strong></p>
                                                            <p id="modal-description"></p>
                                                            <div id="modal-attachment-container" class="mt-3">
                                                                <strong>Attachment:</strong><br>
                                                                <img id="modal-attachment"
                                                                    src="{{ asset($ticket->attachment) }}" alt="Attachment"
                                                                    class="img-fluid rounded d-none" />
                                                                <a id="modal-attachment-link" href="" target="_blank"
                                                                    class="btn btn-outline-primary mt-2 d-none">View
                                                                    File</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- End of scrollable wrapper -->

                </div>
            </main>
        </div>
    </div>
@endsection
@section('page_js')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const modal = new bootstrap.Modal(document.getElementById('ticketModal'));

            document.querySelectorAll(".view-details").forEach(button => {
                button.addEventListener("click", () => {
                    const ticket = JSON.parse(button.getAttribute('data-ticket'));
                    document.getElementById('modal-teacher').textContent = button.getAttribute(
                        'data-teacher');
                    document.getElementById('modal-title').textContent = ticket.title;
                    document.getElementById('modal-status').textContent = ticket.status;
                    document.getElementById('modal-priority').textContent = ticket.priority;
                    document.getElementById('modal-description').textContent = ticket.description;

                    const attachment = ticket.attachment ?
                        `{{ route('landing') }}/${ticket.attachment}` :
                        null;

                    const img = document.getElementById('modal-attachment');
                    const link = document.getElementById('modal-attachment-link');

                    if (attachment) {
                        if (attachment.match(/\.(jpeg|jpg|gif|png|webp)$/i)) {
                            // img.src = attachment;
                            img.classList.remove('d-none');
                            link.classList.add('d-none');
                        } else {
                            img.classList.add('d-none');
                            link.href = attachment;
                            link.classList.remove('d-none');
                        }
                    } else {
                        img.classList.add('d-none');
                        link.classList.add('d-none');
                    }

                    modal.show();
                });
            });
        });
    </script>
@endsection
