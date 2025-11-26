<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Neuron\MyChatBot;
use NeuronAI\Chat\Messages\UserMessage;

class ChatController extends Controller
{
    /**
     * Handle chat messages and stream AI responses
     */
    public function chat(Request $request): StreamedResponse
    {
        try {
            // Validate the request
            $request->validate([
                'message' => 'required|string|max:2000',
                'thread_id' => 'nullable|string|max:255'
            ]);

            $message = $request->input('message');
            $threadId = $request->input('thread_id');
            $isNewThread = false;

            if (!$threadId) {
                $threadId = \Illuminate\Support\Str::uuid()->toString();
                $isNewThread = true;
            }

            // Create response with Server-Sent Events headers
            return response()->stream(function () use ($message, $threadId, $isNewThread) {
                header('Content-Type: text/event-stream');
                header('Cache-Control: no-cache');
                header('Connection: keep-alive');
                header('X-Accel-Buffering: no'); 

                // Send thread_id to client immediately
                echo "data: " . json_encode(['thread_id' => $threadId]) . "\n\n";
                if (ob_get_level() > 0) ob_flush();
                flush();

                try {
                    // Create the RAG agent and stream the response
                    $agent = MyChatBot::make();
                    $agent->setThreadId($threadId);
                    
                    $stream = $agent->stream(
                        new UserMessage($message)
                    );

                    // Stream each chunk to the client
                    foreach ($stream as $text) {
                        echo "data: " . json_encode(['chunk' => $text]) . "\n\n";
                        if (ob_get_level() > 0) {
                            ob_flush();
                        }
                        flush();
                    }
                    // If it's a new thread, generate and save a title
                    try {
                        $chatHistory = \App\Models\ChatHistory::where('thread_id', $threadId)->first();
                        if ($chatHistory && !$chatHistory->title) {
                            $title = mb_substr($message, 0, 40) . (mb_strlen($message) > 40 ? '...' : '');
                            $chatHistory->title = $title;
                            $chatHistory->save();
                            
                            echo "data: " . json_encode(['title' => $title]) . "\n\n";
                            if (ob_get_level() > 0) ob_flush();
                            flush();
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::warning('Failed to generate chat title', [
                            'thread_id' => $threadId,
                            'error' => $e->getMessage()
                        ]);
                    }

                    // Touch the chat history to update updated_at
                    \App\Models\ChatHistory::where('thread_id', $threadId)->touch();

                    // Send completion signal
                    echo "data: " . json_encode(['done' => true]) . "\n\n";
                    
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();

                } catch (\Exception $e) {
                    // Send error to client
                    echo "data: " . json_encode([
                        'error' => true,
                        'message' => $e->getMessage()
                    ]) . "\n\n";
                    
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
                'X-Accel-Buffering' => 'no'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function listHistory()
    {
        $history = \App\Models\ChatHistory::select('id', 'thread_id', 'title', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'history' => $history
        ]);
    }

    public function loadConversation($threadId)
    {
        $chat = \App\Models\ChatHistory::where('thread_id', $threadId)->first();
        
        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'messages' => $chat->messages,
            'title' => $chat->title
        ]);
    }
}
