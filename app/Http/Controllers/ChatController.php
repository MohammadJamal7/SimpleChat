<?php

namespace App\Http\Controllers;

use App\Events\NewChatMessage;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // For logging the error

class ChatController extends Controller
{
    public function index()
    {
        $messages = Message::with('user')->latest()->take(10)->get()->reverse();
        return view('chat.index', compact('messages'));
    }

    public function store(Request $request)
    {
        // Ensure the user is authenticated
        if (!Auth::check()) {
            return response()->json(['error' => 'You must be logged in to send a message.'], 401); // Send a 401 Unauthorized response
        }

        // Validate the incoming request data
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        // Check if the authenticated user has the relationship and create the message
        $user = Auth::user();

        if ($user) {
            try {
                // Create message and store it in the database
                $message = $user->messages()->create([
                    'message' => $validated['message'],
                ]);

                // Dispatch the broadcast event
                broadcast(new NewChatMessage($message));

                // Log success
                Log::info('Message broadcasted to Pusher', ['message' => $message]);

                // Return a success response
                return response()->json(['status' => 'Message sent successfully', 'message' => $message], 200);

            } catch (\Exception $e) {
                // Log the error if broadcasting fails
                Log::error('Error broadcasting message to Pusher', [
                    'error' => $e->getMessage(),
                    'message' => $message ?? 'No message'
                ]);

                // Return an error response with status code 500
                return response()->json(['error' => 'Failed to send message.'], 500);
            }
        }

        // If the user is not authenticated, return an error
        return response()->json(['error' => 'User not authenticated.'], 401);
    }


}
