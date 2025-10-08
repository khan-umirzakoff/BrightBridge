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

        if (empty($request->message) && empty($request->images)) {
            return response()->json(['success' => false, 'error' => 'Matn yoki rasm yuborish kerak'], 400);
        }

        if ($request->input('stream', false)) {
            return $this->chatStream($request);
        }

        try {
            ['prompt' => $enhancedMessage] = $this->buildEnhancedPrompt($request->input('message'));
            $response = $this->aiService->chat($enhancedMessage, $request->input('history', []));
            return response()->json(['success' => true, 'response' => $response]);
        } catch (\Exception $e) {
            Log::error('AI Chat Error', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 503);
        }
    }

    protected function chatStream(Request $request)
    {
        $message = $request->input('message');
        $history = $request->input('history', []);
        $images = $request->input('images', []);

        return response()->stream(function () use ($message, $history, $images) {
            try {
                echo "data: " . json_encode(['thinking' => true]) . "\n\n";
                ob_flush();
                flush();

                ['prompt' => $enhancedMessage, 'sources' => $sources] = $this->buildEnhancedPrompt($message);

                if (!empty($images)) {
                    $result = $this->aiService->chatWithImagesAndThinking($enhancedMessage, $images, $history);
                } else {
                    $result = $this->aiService->chatWithThinking($enhancedMessage, $history);
                }

                if (isset($result['thinking']) && $result['thinking']) {
                    usleep(500000);
                }
                
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
                
                $finalData = ['done' => true];
                if (!empty($sources)) {
                    $finalData['sources'] = $sources;
                }

                echo "data: " . json_encode($finalData) . "\n\n";
                ob_flush();
                flush();
                
            } catch (\Exception $e) {
                Log::error('AI Chat Stream Error', ['error' => $e->getMessage()]);
                echo "data: " . json_encode(['error' => 'Xatolik yuz berdi: ' . $e->getMessage()]) . "\n\n";
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
        $request->validate(['text' => 'required|string|max:5000']);
        try {
            $embedding = $this->aiService->embed($request->input('text'));
            return response()->json(['success' => true, 'embedding' => $embedding]);
        } catch (\Exception $e) {
            Log::error('Embedding Error', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Failed to generate embedding'], 500);
        }
    }

    private function buildEnhancedPrompt(string $userMessage): array
    {
        if (empty($userMessage)) {
            return ['prompt' => '', 'sources' => []];
        }

        try {
            $relevantKnowledge = $this->ragService->retrieve($userMessage);

            if (empty($relevantKnowledge)) {
                return ['prompt' => $userMessage, 'sources' => []];
            }

            $directFacts = [];
            $otherKnowledge = [];
            $sources = [];

            foreach ($relevantKnowledge as $item) {
                if (empty($item['content']) || !is_string($item['content'])) continue;

                if (isset($item['source']) && $item['source'] === 'direct_fact') {
                    $directFacts[] = $item['content'];
                } else {
                    $otherKnowledge[] = $item['content'];
                }

                if (!empty($item['url'])) {
                    $sources[] = ['url' => $item['url'], 'title' => $item['title']];
                }
            }

            // Remove duplicate sources
            $sources = array_unique($sources, SORT_REGULAR);

            $knowledgeText = "";
            if (!empty($directFacts)) {
                $knowledgeText .= "# Aniq Faktlar (Eng Muhim):\n" . implode("\n", $directFacts) . "\n\n";
            }
            if (!empty($otherKnowledge)) {
                $knowledgeText .= "# Boshqa Foydali Ma'lumotlar:\n" . implode("\n\n", $otherKnowledge);
            }

            $knowledgeText = substr(trim($knowledgeText), 0, 150000);

            if (empty($knowledgeText)) {
                return ['prompt' => $userMessage, 'sources' => []];
            }

            $promptTemplate = "Sen JobCare platformasi yordamchisisan. Quyidagi ma'lumotlarga asoslanib foydalanuvchining savoliga javob ber. '# Aniq Faktlar' bo'limidagi ma'lumotlarga eng yuqori ustuvorlik ber.\n\n%s\n\nFoydalanuvchi savoli: %s";
            $prompt = sprintf($promptTemplate, $knowledgeText, $userMessage);

            return ['prompt' => $prompt, 'sources' => array_values($sources)];

        } catch (\Exception $e) {
            Log::error('RAG retrieve or prompt build error', ['error' => $e->getMessage()]);
            return ['prompt' => $userMessage, 'sources' => []];
        }
    }
}