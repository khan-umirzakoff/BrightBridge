<?php

namespace App\Http\Controllers;

use App\Contracts\AIService;
use App\Services\RAGService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AIChatController extends Controller
{
    protected $aiService;
    protected $ragService;

    public function __construct(AIService $aiService, RAGService $ragService)
    {
        $this->aiService = $aiService;
        $this->ragService = $ragService;
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:5000',
            'history' => 'nullable|array',
            'history.*.role' => 'required_with:history|in:user,model',
            'history.*.text' => 'required_with:history|string',
            'stream' => 'nullable|boolean',
            'images' => 'nullable|array',
            'images.*' => 'string',
        ]);

        // Matn yoki rasm bo'lishi kerak
        if (empty($request->message) && empty($request->images)) {
            return response()->json([
                'success' => false,
                'error' => 'Matn yoki rasm yuborish kerak',
            ], 400);
        }

        $stream = $request->input('stream', false);

        if ($stream) {
            return $this->chatStream($request);
        }

        try {
            $message = $request->input('message');
            $history = $request->input('history', []);

            Log::info('AI Chat request', ['message' => $message, 'stream' => $stream]);

            $response = $this->aiService->chat($message, $history);

            return response()->json([
                'success' => true,
                'response' => $response,
                'timestamp' => now()->toIso8601String(),
            ]);

        } catch (\RuntimeException $e) {
            Log::error('AI Chat Error', [
                'message' => $e->getMessage(),
                'user_message' => $request->input('message'),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 503);

        } catch (\Exception $e) {
            Log::error('Unexpected AI Chat Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred. Please try again later.',
            ], 500);
        }
    }

    protected function chatStream(Request $request)
    {
        $message = $request->input('message');
        $history = $request->input('history', []);
        $images = $request->input('images', []);

        return response()->stream(function () use ($message, $history, $images) {
            try {
                // Send thinking indicator first
                echo "data: " . json_encode(['thinking' => true]) . "\n\n";
                ob_flush();
                flush();
                
                // AI ga so'rov (rasmli yoki rasmsiz)
                if (!empty($images)) {
                    Log::info('Chat with images', ['count' => count($images)]);
                    $result = $this->aiService->chatWithImagesAndThinking($message, $images, $history);
                } else {
                    $result = $this->aiService->chatWithThinking($message, $history);
                }
                
                // Check if AI actually used thinking
                if (isset($result['thinking']) && $result['thinking']) {
                    // Keep thinking indicator for a moment
                    usleep(500000); // 0.5 seconds
                }
                
                // Remove thinking indicator
                echo "data: " . json_encode(['thinking' => false]) . "\n\n";
                ob_flush();
                flush();
                
                $fullResponse = $result['response'] ?? $result;
                $words = mb_str_split($fullResponse, 3);
                
                foreach ($words as $chunk) {
                    echo "data: " . json_encode(['chunk' => $chunk]) . "\n\n";
                    ob_flush();
                    flush();
                    usleep(30000);
                }
                
                echo "data: " . json_encode(['done' => true]) . "\n\n";
                ob_flush();
                flush();
                
            } catch (\Exception $e) {
                echo "data: " . json_encode(['error' => $e->getMessage()]) . "\n\n";
                ob_flush();
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function embed(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:5000',
        ]);

        try {
            $text = $request->input('text');
            $embedding = $this->aiService->embed($text);

            return response()->json([
                'success' => true,
                'embedding' => $embedding,
                'dimensions' => count($embedding),
            ]);

        } catch (\Exception $e) {
            Log::error('Embedding Error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate embedding',
            ], 500);
        }
    }
}
