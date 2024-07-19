@extends('layouts.app')

@section('content')

<style>
 .message {
    display: flex;
    flex-direction: column;
}

.message-content-wrapper {
    padding: 0.5rem;
    border-radius: 0.5rem;
    word-wrap: break-word;
    display: inline-block;
    max-width: 100%;
}

.message-content {
    margin-top: 0.25rem; 
}

.message-content-wrapper.bg-primary {
    background-color: #007bff;
    color: #ffffff;
}

.message-content-wrapper.bg-light {
    background-color: #e8e8e8 !important;
    color: #000000;
}

.message strong {
    display: block;
    margin-bottom: 0.25rem; 
}

.list-group {
    padding: 10px 15px !important;
    margin-bottom: 5px !important;
}
</style>
<script>
    $(document).ready(function() {
        $('#messageForm').submit(function(event) {
            event.preventDefault(); 
            var formData = $(this).serialize();

            $.ajax({
                type: 'POST',
                url: '{{ route('chat.send.message') }}',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    var message = response.message;
                    document.querySelector('.message-history').innerHTML += `<div class="message mb-2 d-flex flex-column align-items-end">
                    <div class="message-content-wrapper bg-primary text-white">
                        <div class="message-content">
                            <span>${message.content}</span>
                        </div>
                    </div>
                </div>`;
                    $('textarea[name="content"]').val('');
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText); 
                }
            });
        });
    });

    function joinRoom(roomId) {
        const channel = window.Echo.join('chat-room.' + roomId);
        channel
            .here((users) => {
                console.log('Current users:', users);
                updateActiveUsers(users);
            })
            .joining((user) => {
                console.log('User joined:', user);
                addActiveUser(user);
            })
            .leaving((user) => {
                console.log('User left:', user);
                axios.post(`/chat/leave`,  {
                    roomId: user.roomId,
                    userId: user.user.id
                },{
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    console.log('Status:', response.data.status);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
                removeActiveUser(user);
            })
            .listen('NewMessage', (e) => {
                console.log('New message received:', e.content);
                document.querySelector('.message-history').innerHTML += `<div class="message mb-2 d-flex flex-column align-items-start">
                    <div class="message-content-wrapper bg-light">
                        <strong class="text-primary">${e.user_name}:</strong>
                        <div class="message-content">
                            <span>${e.content}</span>
                        </div>
                    </div>
                </div>`;
            });
    }

    function updateActiveUsers(users) {
        const userList = document.querySelector('.active-users');
        userList.innerHTML = '';
        
        users.forEach(user => {
            userList.innerHTML += `
                <li class="d-flex align-items-center mb-2">
                    <i class="fa fa-circle text-success me-2"></i>
                    <span>${user.user.name}</span>
                </li>`;
        });
    }

    function addActiveUser(user) {
        console.log(user)
        const userList = document.querySelector('.active-users');
        userList.innerHTML += `
                <li class="d-flex align-items-center mb-2">
                    <i class="fa fa-circle text-success me-2"></i>
                    <span>${user.user.name}</span>
                </li>`;
    }

    function removeActiveUser(user) {
        const userList = document.querySelector('.active-users');
        userList.querySelectorAll('li').forEach(li => {
            const userName = li.querySelector('span').textContent.trim();
            if (userName === user.user.name) {
                li.remove(); 
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const roomId = document.getElementById('chatRoomId').value;
        joinRoom(roomId);
    });
</script>
<div class="py-4 bg-light">
    <div class="container">
        <input type="hidden" id="chatRoomId" value="{{ $chatRoom->id }}">

        <div class="row g-4">
            <!-- Active Users Section -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body border-bottom">
                        <h3 class="card-title">{{ $chatRoom->name }}</h3>
                    </div>
                    <ul class="list-group list-group-flush active-users">

                    </ul>
                </div>
            </div>

            <!-- Message History Section -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body overflow-auto message-history" style="height: 400px;">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <strong>Whoops!</strong>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @foreach($messageHistory as $message)
                            <div class="message mb-2 d-flex flex-column align-items-{{ $message->user_id === Auth::id() ? 'end' : 'start' }}">
                                <div class="message-content-wrapper @if($message->user_id === Auth::id()) bg-primary text-white @else bg-light @endif">
                                    @if($message->user_id !== Auth::id())
                                        <strong class="text-primary">
                                            {{ $message->user->name }}:
                                        </strong>
                                    @endif
                                    <div class="message-content">
                                        <span>{{ $message->content }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <!-- Message Sending Section -->
                    <form id="messageForm" method="POST" class="card-body border-top">
                        @csrf
                        <input type="hidden" name="chat_room_id" value="{{ $chatRoom->id }}">
                        <div class="mb-3">
                            <textarea name="content" class="form-control" rows="3" placeholder="Type your message..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
