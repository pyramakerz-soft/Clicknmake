@extends('layouts.app')

@section('title', 'Chat')

@section('content')
    <div class="container">
        <h1 class="mb-4">Chat</h1>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (Auth::guard('teacher')->check())
            <!-- Teacher View -->
            <form id="chat-form" action="{{ route('chat.sendMessage') }}" method="POST">
                @csrf
                <div class="form-group mb-3">
                    <label for="student-selector">Select Student:</label>
                    <select name="receiver_id" id="student-selector" class="form-control" required>
                        <option value="">--Select Student--</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" data-type="App\Models\Student">{{ $student->username }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <input type="hidden" name="receiver_type" id="receiver-type">
            @else
                <!-- Student View -->
                <input type="hidden" name="receiver_id" value="{{ $teacher->id ?? '' }}">
                <input type="hidden" name="receiver_type" value="App\Models\Teacher">
        @endif

        <!-- Chat Box -->
        <div id="chat-box" class="border p-3 mb-3" style="height: 300px; overflow-y: auto;">
            <!-- Messages will be dynamically populated -->
        </div>

        <!-- Message Input -->
        <div class="form-group">
            <textarea name="message" class="form-control" rows="3" placeholder="Type your message..." required></textarea>
        </div>

        <button type="submit" class="btn btn-success">Send</button>
        </form>
    </div>
@endsection

@section('page_js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            const chatBox = $('#chat-box');

            // Fetch messages
            function fetchMessages() {
                const receiverId = $('#student-selector').val() || $('input[name="receiver_id"]').val();
                const receiverType = $('#receiver-type').val() || $('input[name="receiver_type"]').val();

                if (!receiverId || !receiverType) return;

                $.ajax({
                    url: "{{ route('chat.fetchMessages') }}",
                    method: 'GET',
                    data: {
                        receiver_id: receiverId,
                        receiver_type: receiverType
                    },
                    success: function(response) {
                        chatBox.html(response);
                        chatBox.scrollTop(chatBox[0].scrollHeight);
                    },
                });
            }

            // Auto fetch messages every 5 seconds
            setInterval(fetchMessages, 5000);

            // Send message
            $('#chat-form').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);

                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: form.serialize(),
                    success: function() {
                        fetchMessages();
                        form.find('textarea[name="message"]').val('');
                    },
                });
            });

            // Update receiver type when selecting student
            $('#student-selector').on('change', function() {
                const receiverType = $(this).find(':selected').data('type');
                $('#receiver-type').val(receiverType);
                fetchMessages();
            });

            // Initial fetch
            fetchMessages();
        });
    </script>
@endsection
