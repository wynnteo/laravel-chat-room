<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\ChatRoom;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


Broadcast::channel('chat-room.{roomId}', function ($user, $roomId) {
    if (Auth::check()) {
        $chatRoom = ChatRoom::find($roomId);

        if ($chatRoom && $chatRoom->users()->where('user_id', $user->id)->exists()) {
            return ['user' => $user, 'roomId' => $roomId];
        }
    }

    return false;
});