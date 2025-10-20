@foreach ($messages as $message)
    <div class="{{ $message->sender_id === Auth::id() ? 'text-end' : 'text-start' }} mb-2">
        <span class="badge bg-{{ $message->sender_id === Auth::id() ? 'primary' : 'secondary' }}">
            {{ $message->message }}
        </span>
    </div>
@endforeach
