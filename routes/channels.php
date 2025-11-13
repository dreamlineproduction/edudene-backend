<?php

use App\Models\Chat;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/




Broadcast::channel('chat.{chatId}', function ($user, $chatId) {

    return 2; // 👈 test only

    // \Log::info('Broadcasting Auth Attempt', [
    //     'user' => $user ? $user->id : 'Guest/Failed Auth', // Check if $user is null
    //     'id' => $id
    // ]);

    // return true;



   // \Log::info('Broadcast auth user:', [$user]);

    return Chat::where('id', $chatId)
               ->where(function ($query) use ($user) {
                   $query->where('sender_id', 2)
                         ->orWhere('receiver_id', 2);
               })->exists();
});
