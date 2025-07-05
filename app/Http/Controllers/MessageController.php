<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index($userId)
    {
        $messages = Message::where(function ($query) use ($userId) {
            $query->where('sender_id', auth()->id())
                  ->where('receiver_id', $userId);
        })->orWhere(function ($query) use ($userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', auth()->id());
        })->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($messages);
    }

    public function usersWithMessages()
    {
        $adminId = auth()->id();

        $userIds = Message::where('sender_id', $adminId)
            ->orWhere('receiver_id', $adminId)
            ->get()
            ->flatMap(function ($msg) use ($adminId) {
                return [$msg->sender_id, $msg->receiver_id];
            })
            ->unique()
            ->reject(fn ($id) => $id == $adminId)
            ->values();

        $users = User::whereIn('id', $userIds)->get();

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'body' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'body' => $request->body,
        ]);

        return response()->json($message, 201);
    }
}
