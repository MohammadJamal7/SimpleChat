<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Chat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Chat Messages -->
                    <div id="chat-messages" class="mb-4 overflow-y-auto h-96 p-4 border rounded-lg dark:border-gray-700 space-y-4">
                        @foreach ($messages as $message)
                            <div class="flex {{ $message->user_id == auth()->id() ? 'justify-end' : 'justify-start' }}">
                                @if($message->user_id != auth()->id())
                                    <div class="flex-shrink-0 mr-2">
                                        <div class="h-8 w-8 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center text-sm font-bold">
                                            {{ substr($message->user->name, 0, 1) }}
                                        </div>
                                    </div>
                                @endif

                                <div class="max-w-xs {{ $message->user_id == auth()->id() ? 'bg-indigo-500 text-white' : 'bg-gray-100 dark:bg-gray-700' }} rounded-lg p-3 shadow">
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="font-bold text-sm">{{ $message->user->name }}</p>
                                        <span class="text-xs {{ $message->user_id == auth()->id() ? 'text-indigo-100' : 'text-gray-500 dark:text-gray-400' }}">
                                            {{ $message->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    <p class="{{ $message->user_id == auth()->id() ? 'text-white' : 'text-gray-800 dark:text-gray-200' }}">{{ $message->message }}</p>
                                </div>

                                @if($message->user_id == auth()->id())
                                    <div class="flex-shrink-0 ml-2">
                                        <div class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center text-sm font-bold text-white">
                                            {{ substr($message->user->name, 0, 1) }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Chat Input Form -->
                    <form id="chat-form" method="POST" action="{{ route('chat.store') }}" class="mt-4">
                        @csrf
                        <div class="flex">
                            <input
                                type="text"
                                id="message-input"
                                name="message"
                                placeholder="Type your message..."
                                class="flex-1 rounded-l-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm"
                                required
                                autofocus
                            >
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-r-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Send
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@7.0.3/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.10.0/dist/echo.iife.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Get elements
            const form = document.getElementById('chat-form');
            const messageInput = document.getElementById('message-input');
            const chatMessages = document.getElementById('chat-messages');

            // Scroll chat to bottom on page load
            chatMessages.scrollTop = chatMessages.scrollHeight;

            // Use the globally initialized Echo instance
            const echo = window.Echo;

            // Listen for new chat messages from the event broadcast
            echo.channel('chat')
                .listen('NewChatMessage', (e) => {
                    console.log('Received message event:', e);

                    const message = e;
                    const isCurrentUser = message.user_id === {{ auth()->id() }};

                    // Ensure user_name is a string and handle empty string cases
                    const userNameInitial = message.user_name && typeof message.user_name === 'string'
                        ? message.user_name.charAt(0).toUpperCase()
                        : 'U'; // Fallback to 'U' if user_name is not available

                    // Create message HTML dynamically based on the event data
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'flex ' + (isCurrentUser ? 'justify-end' : 'justify-start');

                    messageDiv.innerHTML = `
                        ${!isCurrentUser ? `
                        <div class="flex-shrink-0 mr-2">
                            <div class="h-8 w-8 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center text-sm font-bold">
                                ${userNameInitial}
                            </div>
                        </div>
                        ` : ''}

                        <div class="max-w-xs ${isCurrentUser ? 'bg-indigo-500 text-white' : 'bg-gray-100 dark:bg-gray-700'} rounded-lg p-3 shadow">
                            <div class="flex items-center justify-between mb-1">
                                <p class="font-bold text-sm">${message.user_name}</p>
                                <span class="text-xs ${isCurrentUser ? 'text-indigo-100' : 'text-gray-500 dark:text-gray-400'}">
                                    just now
                                </span>
                            </div>
                            <p class="${isCurrentUser ? 'text-white' : 'text-gray-800 dark:text-gray-200'}">${message.message}</p>
                        </div>

                        ${isCurrentUser ? `
                        <div class="flex-shrink-0 ml-2">
                            <div class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center text-sm font-bold text-white">
                                ${userNameInitial}
                            </div>
                        </div>
                        ` : ''}
                    `;

                    // Add the new message to the chat and scroll to the bottom
                    chatMessages.appendChild(messageDiv);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                });

            // Handle form submission with AJAX to prevent page reload
            form.addEventListener('submit', function (e) {
                e.preventDefault(); // Prevent the form from reloading the page

                const message = messageInput.value;

                // Clear the input field immediately when button is clicked
                messageInput.value = '';

                // Only proceed with the fetch if there's a message to send
                if (message.trim() !== '') {
                    // Use Fetch API to send the message via AJAX
                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({
                            message: message
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            console.log('Message sent successfully:', data);
                        })
                        .catch(error => {
                            console.error('Error sending message:', error);
                        });
                }
            });
        });
    </script>
</x-app-layout>
