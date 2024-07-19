<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\ChatRoom;
use App\Models\User;
use App\Events\NewMessage;
use Illuminate\Support\Facades\Log;
use Auth;


class ChatRoomController extends Controller
{
    public function index(Request $request)
    {
        $roomId = $request->input('room_id', 1);
        $chatRoom = ChatRoom::findOrFail($roomId);

        $messageHistory = Message::where('chat_room_id', $roomId)->get();

        return view('chat.index', [
            'messageHistory' => $messageHistory,
            'chatRoom' => $chatRoom,
        ]);
    }

    public function sendMessage(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'content' => 'required|string',
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Create a new message instance
        $message = new Message;
        $message->user_id = $user->id;
        $message->chat_room_id = $request->chat_room_id; // Assuming chat room ID is passed in the request
        $message->content = $validatedData['content'];
        $message->save();

        // Broadcast the message to others in the chat room
        broadcast(new NewMessage($message))->toOthers();

        // Return a JSON response indicating success
        return response()->json(['status' => 'Message sent!', 'message' => $message]);
    }

    public function joinRoom(Request $request)
    {
        $request->validate([
            'room_name' => 'required|string|max:255',
        ]);
        $chatRoom = ChatRoom::firstOrCreate(['name' => $request->room_name]);
        if (!$chatRoom->users->contains(Auth::id())) {
            $chatRoom->users()->attach(Auth::id());
        }
        return redirect()->route('chat.index', ['room_id' => $chatRoom->id]);
    }
    
    public function joinExistingRoom($id)
    {
        $chatRoom = ChatRoom::findOrFail($id);
        if (!$chatRoom->users->contains(Auth::id())) {
            $chatRoom->users()->attach(Auth::id());
        }
        return redirect()->route('chat.index', ['room_id' => $chatRoom->id]);
    }

    public function leaveRoom(Request $request)
    {
        $request->validate([
            'roomId' => 'required|integer|exists:chat_rooms,id',
            'userId' => 'required|integer|exists:users,id',
        ]);
        $chatRoom = ChatRoom::findOrFail($request->input('roomId'));
        $user = User::findOrFail($request->input('userId'));

        if ($user->chatRooms->contains($chatRoom)) {
            $user->chatRooms()->detach($chatRoom);
        }

        return response()->json(['status' => 'Left room successfully']);
    }

    public function listRooms()
    {
        $chatRooms = ChatRoom::withCount('users')->get();

        return response()->json([
            'chatRooms' => $chatRooms,
        ]);
    }

    public function showJoinRoomForm() 
    {
        $chatRooms = ChatRoom::withCount('users')->get();

        return view('chat.rooms', ['chatRooms' => $chatRooms]);
    }
}
