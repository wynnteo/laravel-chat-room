<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatRoomController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/chat', [ChatRoomController::class, 'index'])->name('chat.index');
Route::post('/chat/send-message', [ChatRoomController::class, 'sendMessage'])->name('chat.send.message');
Route::get('/chat/rooms', [ChatRoomController::class, 'showJoinRoomForm'])->name('chat.rooms');
Route::post('/chat/join', [ChatRoomController::class, 'joinRoom'])->name('chat.joinRoom');
Route::get('/chat/join/{id}', [ChatRoomController::class, 'joinExistingRoom'])->name('chat.joinExistingRoom');
Route::post('/chat/leave', [ChatRoomController::class, 'leaveRoom'])->name('chat.leaveRoom');
Route::post('/chat/send', [ChatRoomController::class, 'sendMessage'])->name('chat.sendMessage');
