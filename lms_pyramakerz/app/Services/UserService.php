<?php


namespace App\Services;

use App\Events\ChatSent;
use App\Models\Message;
use App\Models\Student;
use App\Models\Teacher;
// use Auth;
use Illuminate\Support\Facades\Auth;


class UserService
{

    public function getUserById($id, $type)
    {
        return $type === 'student' ? Student::find($id) : Teacher::find($id);
    }

    public function sendMessage($receiverId, $receiverType, $messageContent)
    {
        $sender = auth()->guard('teacher')->check() ? auth()->guard('teacher')->user() : auth()->guard('student')->user();
        $senderType = auth()->guard('teacher')->check() ? 'teacher' : 'student';

        if (!$sender) {
            throw new \Exception('Unauthorized user');
        }

        $data = [
            'sender_id' => $sender->id,
            'sender_type' => $senderType,
            'receiver_id' => $receiverId,
            'receiver_type' => $receiverType,
            'message' => $messageContent,
        ];

        $createdMessage = Message::create($data);
        return $createdMessage;
    }
}