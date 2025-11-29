<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index()
    {
        $teacher = Auth::guard('teacher')->user();
        $tickets = Ticket::where('teacher_id', $teacher->id)->latest()->get();

        return view('pages.teacher.tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('pages.teacher.tickets.create', [
            'statuses' => ['open', 'in_progress', 'resolved', 'closed'],
            'priorities' => ['low', 'medium', 'high', 'urgent'],
        ]);

    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'status' => 'required|in:open,in_progress,resolved,closed',
            'priority' => 'required|in:low,medium,high,urgent',
            'attachment' => 'nullable|file|max:2048',
        ]);

        $data = $request->all();
        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('attachments', 'public');
        }

        $data['teacher_id'] = auth('teacher')->id();
        Ticket::create($data);

        return redirect()->back()->with('success', 'Ticket submitted successfully.');
    }
}
