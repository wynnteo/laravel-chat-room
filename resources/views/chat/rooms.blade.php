@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <!-- Left Column: Active Rooms -->
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title mb-4">Active Chat Rooms</h2>
                    <ul class="list-group">
                        @foreach($chatRooms as $room)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="font-weight-bold">{{ $room->name }}</span>
                                <span class="text-muted">({{ $room->users_count }} users)</span>
                            </div>
                            <form method="GET" action="{{ route('chat.joinExistingRoom', ['id' => $room->id]) }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fa-solid fa-right-to-bracket"></i>
                                </button>
                            </form>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Right Column: Join Form -->
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4">Create a Chat Room</h2>
                    <form method="POST" action="{{ route('chat.joinRoom') }}">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="room_name">Room Name:</label>
                            <input id="room_name" type="text" class="form-control @error('room_name') is-invalid @enderror" name="room_name" required autofocus>
                            @error('room_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-circle-plus"></i> Create Room
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
