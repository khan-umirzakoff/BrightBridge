<?php

namespace App\Services;

use App\Contracts\AIService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class GeminiAIService implements AIService
{
    protected $client;
    protected $apiKey;
    protected $model;
    protected $embeddingModel;
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'http_errors' => false,
        ]);

        // Try to get settings from database, fallback to .env
        try {
            $this->apiKey = \App\AiSetting::get('gemini_api_key') ?: env('GEMINI_API_KEY');
            $this->model = \App\AiSetting::get('gemini_model') ?: env('GEMINI_MODEL', 'gemini-2.0-flash-exp');
            $this->embeddingModel = \App\AiSetting::get('gemini_embedding_model') ?: env('GEMINI_EMBEDDING_MODEL', 'gemini-embedding-001');
        } catch (\Exception $e) {
            // Fallback to .env if database not available
            $this->apiKey = env('GEMINI_API_KEY');
            $this->model = env('GEMINI_MODEL', 'gemini-2.0-flash-exp');
            $this->embeddingModel = env('GEMINI_EMBEDDING_MODEL', 'gemini-embedding-001');
        }

        if (empty($this->apiKey)) {
            throw new \RuntimeException('Gemini API Key is not configured. Please configure it in Admin > AI Settings.');
        }
    }

    public function chat(string $prompt, array $history = []): string
    {
        $url = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";
        
        $contents = [];
        
        foreach ($history as $message) {
            $contents[] = [
                'role' => $message['role'] ?? 'user',
                'parts' => [['text' => $message['text']]]
            ];
        }
        
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $prompt]]
        ];

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 2048,
            ],
            'tools' => [
                [
                    'functionDeclarations' => [
                        [
                            'name' => 'search_jobs',
                            'description' => 'Search for job listings in the database when user asks about jobs, vacancies, or work opportunities',
                            'parameters' => [
                                'type' => 'object',
                                'properties' => [
                                    'query' => [
                                        'type' => 'string',
                                        'description' => 'Search query (location, job type, skills)'
                                    ]
                                ],
                                'required' => ['query']
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // System instruction
        $systemPrompt = $this->getSystemPrompt();
        $payload['systemInstruction'] = [
            'parts' => [['text' => $systemPrompt]]
        ];

        try {
            $response = $this->client->post($url, [
                'json' => $payload
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if ($statusCode !== 200) {
                Log::error('Gemini API Error', [
                    'status' => $statusCode,
                    'response' => $body
                ]);
                throw new \RuntimeException('AI service returned error: ' . ($data['error']['message'] ?? 'Unknown error'));
            }

            // Thinking detection (real from API)
            $hasThinking = isset($data['usageMetadata']['thoughtsTokenCount']) 
                && $data['usageMetadata']['thoughtsTokenCount'] > 0;
            
            if ($hasThinking) {
                Log::info('AI used thinking', [
                    'thoughtsTokens' => $data['usageMetadata']['thoughtsTokenCount']
                ]);
            }

            $candidate = $data['candidates'][0] ?? null;
            if (!$candidate) {
                throw new \RuntimeException('No candidate in response');
            }

            // Function calling check
            $parts = $candidate['content']['parts'] ?? [];
            if (!empty($parts) && isset($parts[0]['functionCall'])) {
                $functionCall = $candidate['content']['parts'][0]['functionCall'];
                
                Log::info('Function calling detected', ['function' => $functionCall['name']]);
                
                // Execute function va qayta so'rov
                $functionResult = $this->executeFunction($functionCall);
                
                // Function result bilan qayta chaqirish
                $contents[] = $candidate['content'];
                $contents[] = [
                    'role' => 'function',
                    'parts' => [
                        [
                            'functionResponse' => [
                                'name' => $functionCall['name'],
                                'response' => [
                                    'result' => $functionResult
                                ]
                            ]
                        ]
                    ]
                ];
                
                $secondPayload = [
                    'contents' => $contents,
                    'generationConfig' => $payload['generationConfig']
                ];
                
                Log::info('Sending function response back to AI');
                
                $response2 = $this->client->post($url, ['json' => $secondPayload]);
                $statusCode2 = $response2->getStatusCode();
                $body2 = $response2->getBody()->getContents();
                $data2 = json_decode($body2, true);
                
                if ($statusCode2 !== 200) {
                    Log::error('Function response API error', ['response' => $body2]);
                    return 'Function executed but response error';
                }
                
                Log::info('Function response received', ['data' => $data2]);
                
                // Ikkinchi javobdan text olish
                if (isset($data2['candidates'][0]['content']['parts'])) {
                    foreach ($data2['candidates'][0]['content']['parts'] as $part) {
                        if (isset($part['text']) && !empty($part['text'])) {
                            return $part['text'];
                        }
                    }
                }
                
                Log::warning('Function response empty', ['data' => $data2]);
                return 'Ma\'lumot topildi, lekin javob yaratishda xatolik.';
            }

            // Oddiy javob
            $parts = $candidate['content']['parts'] ?? [];
            
            // Text qidirish (parts array ichida)
            foreach ($parts as $part) {
                if (isset($part['text']) && !empty($part['text'])) {
                    return $part['text'];
                }
            }
            
            // Agar text topilmasa
            Log::error('Gemini Response Missing Text', [
                'response' => $data,
                'finishReason' => $candidate['finishReason'] ?? 'unknown'
            ]);
            
            // Thinking model bo'lsa, placeholder javob
            if (isset($data['usageMetadata']['thoughtsTokenCount'])) {
                return 'Kechirasiz, javob yaratishda xatolik yuz berdi. Iltimos, qayta urinib ko\'ring.';
            }
            
            throw new \RuntimeException('AI service returned invalid response');

        } catch (RequestException $e) {
            Log::error('Gemini Chat Request Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \RuntimeException('AI service is currently unavailable. Please try again later.');
        } catch (\Exception $e) {
            Log::error('Gemini Chat Unexpected Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function embed(string $text): array
    {
        $url = "{$this->baseUrl}/models/{$this->embeddingModel}:embedContent?key={$this->apiKey}";
        
        try {
            $response = $this->client->post($url, [
                'json' => [
                    'model' => "models/{$this->embeddingModel}",
                    'content' => [
                        'parts' => [['text' => $text]]
                    ]
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if ($statusCode !== 200) {
                Log::error('Gemini Embedding Error', [
                    'status' => $statusCode,
                    'response' => $body
                ]);
                throw new \RuntimeException('Embedding service returned error');
            }

            return $data['embedding']['values'] ?? [];

        } catch (RequestException $e) {
            Log::error('Gemini Embed Request Failed', [
                'error' => $e->getMessage()
            ]);
            throw new \RuntimeException('Embedding service is currently unavailable');
        }
    }

    protected function executeFunction($functionCall): string
    {
        $functionName = $functionCall['name'];
        $args = $functionCall['args'] ?? [];

        if ($functionName === 'search_jobs') {
            $ragService = app(\App\Services\RAGService::class);
            $query = $args['query'] ?? '';
            
            Log::info('Executing search_jobs', ['query' => $query]);
            
            return $ragService->buildContext($query);
        }

        return 'Function not found';
    }

    public function chatWithImage(string $prompt, string $imageBase64, array $history = []): string
    {
        return $this->chatWithImages($prompt, [$imageBase64], $history);
    }

    public function chatWithImages(string $prompt, array $images, array $history = []): string
    {
        $url = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

        $contents = [];

        foreach ($history as $message) {
            $contents[] = [
                'role' => $message['role'] ?? 'user',
                'parts' => [['text' => $message['text']]]
            ];
        }

        // Rasm va text (text ixtiyoriy)
        $parts = [];

        if (!empty($prompt)) {
            $parts[] = ['text' => $prompt];
        }

        foreach ($images as $imageBase64) {
            $parts[] = [
                'inlineData' => [
                    'mimeType' => 'image/jpeg',
                    'data' => $imageBase64
                ]
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => $parts
        ];

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 2048,
            ]
        ];

        try {
            $response = $this->client->post($url, ['json' => $payload]);
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if ($statusCode !== 200) {
                Log::error('Gemini Vision API Error', ['status' => $statusCode, 'response' => $body]);
                throw new \RuntimeException('Rasm tahlil xato');
            }

            $candidate = $data['candidates'][0] ?? null;
            if (!$candidate) {
                throw new \RuntimeException('No candidate');
            }

            $parts = $candidate['content']['parts'] ?? [];
            foreach ($parts as $part) {
                if (isset($part['text']) && !empty($part['text'])) {
                    return $part['text'];
                }
            }

            throw new \RuntimeException('Vision response empty');

        } catch (\Exception $e) {
            Log::error('Vision API Error', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Rasmni tahlil qilishda xatolik');
        }
    }

    public function chatWithThinking(string $prompt, array $history = []): array
    {
        $response = $this->chat($prompt, $history);
        
        // Since chat() method doesn't return thinking info, 
        // we'll simulate it by checking if the response suggests thinking
        $hasThinking = strlen($response) > 100 || 
                      str_contains(strtolower($response), 'analizlash') ||
                      str_contains(strtolower($response), 'o\'ylab') ||
                      str_contains(strtolower($response), 'hisoblab');
        
        return [
            'response' => $response,
            'thinking' => $hasThinking
        ];
    }

    public function chatWithImagesAndThinking(string $prompt, array $base64Images, array $history = []): array
    {
        $response = $this->chatWithImages($prompt, $base64Images, $history);

        // Image analysis usually involves thinking
        $hasThinking = true;

        return [
            'response' => $response,
            'thinking' => $hasThinking
        ];
    }

    public function getSystemPrompt(): string
    {
        return \App\AiSetting::getSystemPrompt();
    }
}
