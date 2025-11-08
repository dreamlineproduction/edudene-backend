<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    //

    public function index()
    {
        $user = auth('sanctum')->user();

        $chats = Chat::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->with(['sender:id,name', 'receiver:id,name', 'messages' => function ($q) {
                $q->latest()->take(1); // last message preview
            }])
            ->get();

        return jsonResponse(true, 'Chat list', $chats);
    }

    public function startChat(Request $request)
    {
        $user = auth('sanctum')->user();

        $chat = Chat::firstOrCreate([
            'user_id' => $user->role_id === 1 ? $user->id : $request->user_id,
            'tutor_id' => $user->role_id === 2 ? $user->id : $request->tutor_id,
        ]);

        return jsonResponse(true, 'Chat started', $chat);
    }

    public function getMessages($chatId)
    {
        $messages = Message::where('chat_id', $chatId)->with('sender:id,name')->orderBy('id')->get();

        return jsonResponse(true, 'Messages loaded', $messages);
    }


    public function sendMessage(Request $request, $chatId)
    {
        $request->validate(['message' => 'nullable|string']);

        $message = Message::create([
            'chat_id' => $chatId,
            'sender_id' => auth()->id(),
            'message' => $request->message,
        ]);

        broadcast(new \App\Events\NewMessageEvent($message))->toOthers();

        return jsonResponse(true, 'Message sent', $message);
    }

}
