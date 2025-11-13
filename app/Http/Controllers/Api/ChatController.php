<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Events\NewChatMessage;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    //
    public function contacts()
    {
        $loggedInUser = auth('sanctum')->user();

        $contacts = User::where('id', '!=', $loggedInUser->id)
            ->select('id', 'full_name', 'email')
            ->get();


        return jsonResponse(true,'Contacts data fetch',['contacts'=>$contacts])   ;
        //return response()->json($contacts);
    }

    /**
     * Start a chat or retrieve the existing one with a specific user.
     */
    public function findOrCreateChat(int $otherUserId, Request $request)
    {


       $loggedInUser = auth('sanctum')->user();
        if (!$loggedInUser) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }


        // Find chat where current user is sender AND other user is receiver
        $chat = Chat::where(function ($query) use ($loggedInUser, $otherUserId) {
                $query->where('sender_id', $loggedInUser->id)
                      ->where('receiver_id', $otherUserId);
            })

            // OR where current user is receiver AND other user is sender
            ->orWhere(function ($query) use ($loggedInUser, $otherUserId) {
                $query->where('sender_id', $otherUserId)
                      ->where('receiver_id', $loggedInUser->id);
            })
            ->first();

        if (!$chat) {
            // Create a new chat if it doesn't exist
            $chat = Chat::create([
                'sender_id' => $loggedInUser->id,
                'receiver_id' => $otherUserId,
            ]);
        }
        
        // Eager load the other user for the frontend
        $chat->load(['sender', 'receiver']);

        $data = [
            'chat_id' => $chat->id,
            'chat' => $chat,
        ];

        return jsonResponse(true,'Chat start',$data);
        // return response()->json([
        //     'chat_id' => $chat->id,
        //     'chat' => $chat,
        // ]);
    }


    /**
     * Get the message history for a specific chat.
     */
    public function getMessages(Chat $chat, Request $request)
    {
        $loggedInUser = auth('sanctum')->user();

        // Authorization: Ensure the authenticated user is part of the chat
        if ($loggedInUser->id !== $chat->sender_id && $loggedInUser->id !== $chat->receiver_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = $chat->messages()->latest()->paginate(20);

        // Mark messages received by the current user as read (optional, for efficiency)
        $chat->messages()
             ->where('receiver_id', $loggedInUser->id)
             ->where('is_read', false)
             ->update(['is_read' => true]);


         return jsonResponse(true,'Message Loading',$messages)  ; 
        //return response()->json($messages);
    }

    /**
     * Send a new message.
     */
    public function sendMessage(Chat $chat, Request $request)
    {
        $loggedInUser = auth('sanctum')->user();

        // 1. Authorization check
        if ($loggedInUser->id !== $chat->sender_id && $loggedInUser->id !== $chat->receiver_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // 2. Validation
        $request->validate([
            'message' => 'required_without:file_url|string|max:2000',
            'file_url' => 'nullable|url|max:255', // Simplified file handling
        ]);
        
        // Determine the receiver ID
        $receiverId = $loggedInUser->id === $chat->sender_id 
            ? $chat->receiver_id 
            : $chat->sender_id;

        // 3. Create the message
        $message = $chat->messages()->create([
            'sender_id' => $loggedInUser->id,
            'message' => $request->message,
            'file_url' => $request->file_url,
            // 'is_read' will be false by default
            'receiver_id' => $receiverId, // Add receiver_id to the message schema (good practice, but not strictly required by your original schema)
        ]);

        // 4. Broadcast the message to the other user
        NewChatMessage::dispatch($message);

        return jsonResponse(true,'Message Load',$message->load('sender'));      
    }

}
