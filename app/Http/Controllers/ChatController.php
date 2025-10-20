<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherClass;
use App\Services\UserService;
use Auth;
use Illuminate\Http\Request;
use Pusher\Pusher;

class ChatController extends Controller
{
    public function viewAllChats(Request $request)
    {
        $students = collect();
        $teachers = collect();
        $classes = collect();
        $perPage = 15;

        if (auth()->guard('teacher')->check()) {
            $userId = auth()->guard('teacher')->user()->id;

            $classes = TeacherClass::where('teacher_id', $userId)->with('class')->get();

            $classId = $request->query('class_id');
            $search = $request->query('search');
            $sortBy = $request->query('sort_by', 'username');
            $sortOrder = $request->query('sort_order', 'asc');

            $studentsQuery = Student::query();

            if ($classId) {
                $studentsQuery->where('class_id', $classId);
            }

            if ($search) {
                $studentsQuery->where('username', 'like', "%$search%");
            }

            $students = $studentsQuery->whereIn('class_id', $classes->pluck('class_id'))
                ->orderBy($sortBy, $sortOrder)
                ->paginate($perPage);
        } elseif (auth()->guard('student')->check()) {
            $userId = auth()->guard('student')->user()->id;

            $student = Student::find($userId);

            if (!$student) {
                abort(404, 'Student not found');
            }

            $teachersQuery = Teacher::query();
            $teachers = $teachersQuery->whereHas('classes', function ($query) use ($student) {
                $query->where('class_id', $student->class_id);
            })
                ->orderBy('username', 'asc')
                ->paginate($perPage);
        } else {
            abort(403, 'Unauthorized access');
        }

        return view('pages.student.all', compact('students', 'teachers', 'classes'));
    }




    public function chatForm($receiverId, $receiverType, UserService $userService)
    {
        $receiver = $userService->getUserById($receiverId, $receiverType);

        if (!$receiver) {
            abort(404, 'Receiver not found');
        }

        $students = collect();
        $teachers = collect();

        if (auth()->guard('student')->check()) {
            $user = auth()->guard('student')->user();
            $userType = 'student';

            $teachers = Teacher::whereHas('classes', function ($query) use ($user) {
                $query->where('class_id', $user->class_id);
            })->get();

            if ($receiverType === 'teacher') {
                $isAllowed = $teachers->contains('id', $receiverId);
                if (!$isAllowed) {
                    abort(403, 'Unauthorized to chat with this teacher.');
                }
            } else {
                abort(403, 'Students can only chat with teachers.');
            }

        } elseif (auth()->guard('teacher')->check()) {
            $user = auth()->guard('teacher')->user();
            $userType = 'teacher';

            $teacherClasses = TeacherClass::where('teacher_id', $user->id)->pluck('class_id');
            $students = Student::whereIn('class_id', $teacherClasses)->get();

            if ($receiverType === 'student') {
                $isAllowed = $students->contains('id', $receiverId);
                if (!$isAllowed) {
                    abort(403, 'Unauthorized to chat with this student.');
                }
            } else {
                abort(403, 'Teachers can only chat with students.');
            }

        } else {
            abort(403, 'Unauthorized access');
        }

        $messages = Message::where(function ($query) use ($user, $receiverId, $receiverType, $userType) {
            $query->where('sender_id', $user->id)
                ->where('sender_type', $userType)
                ->where('receiver_id', $receiverId)
                ->where('receiver_type', $receiverType);
        })->orWhere(function ($query) use ($user, $receiverId, $receiverType, $userType) {
            $query->where('sender_id', $receiverId)
                ->where('sender_type', $receiverType)
                ->where('receiver_id', $user->id)
                ->where('receiver_type', $userType);
        })->orderBy('created_at', 'asc')->get();

        $lastMessageId = $messages->last()?->id ?? 0;

        return view('pages.student.chat', compact('receiver', 'receiverType', 'messages', 'students', 'teachers', 'lastMessageId'));
    }



    public function sendMessage($receiverId, $receiverType, Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $senderId = auth()->guard('student')->check() ? auth()->guard('student')->id() : auth()->guard('teacher')->id();
        $senderType = auth()->guard('student')->check() ? 'student' : 'teacher';

        $message = Message::create([
            'sender_id' => $senderId,
            'sender_type' => $senderType,
            'receiver_id' => $receiverId,
            'receiver_type' => $receiverType,
            'message' => $request->message,
        ]);

        return response()->json(['message' => $message], 201);
    }
    public function fetchMessages($receiverId, $receiverType, Request $request)
    {
        $userId = auth()->guard('student')->check() ? auth()->guard('student')->id() : auth()->guard('teacher')->id();
        $userType = auth()->guard('student')->check() ? 'student' : 'teacher';
        $lastMessageId = $request->query('last_message_id', 0);

        $messages = Message::where(function ($query) use ($userId, $userType, $receiverId, $receiverType) {
            $query->where('sender_id', $userId)
                ->where('sender_type', $userType)
                ->where('receiver_id', $receiverId)
                ->where('receiver_type', $receiverType);
        })
            ->orWhere(function ($query) use ($userId, $userType, $receiverId, $receiverType) {
                $query->where('sender_id', $receiverId)
                    ->where('sender_type', $receiverType)
                    ->where('receiver_id', $userId)
                    ->where('receiver_type', $userType);
            })
            ->where('id', '>', $lastMessageId) // Fetch only messages after last_message_id
            ->orderBy('created_at', 'asc')
            ->get();

        Message::where('receiver_id', $userId)
            ->where('receiver_type', $userType)
            ->where('sender_id', $receiverId)
            ->where('sender_type', $receiverType)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['messages' => $messages]);
    }


}