<?php

namespace App\Services;

use App\Contracts\AIService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class OpenAIService implements AIService
{
    protected $client;
    protected $apiKey;
    protected $model;
    protected $embeddingModel;
    protected $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'http_errors' => false,
        ]);
        $this->apiKey = env('OPENAI_API_KEY');
        $this->model = env('OPENAI_MODEL', 'gpt-4o-mini');
        $this->embeddingModel = env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small');
        
        if (empty($this->apiKey)) {
            throw new \RuntimeException('OPENAI_API_KEY is not configured in .env file');
        }
    }

    public function chat(string $prompt, array $history = []): string
    {
        $url = "{$this->baseUrl}/chat/completions";
        
        $messages = [];
        
        foreach ($history as $message) {
            $messages[] = [
                'role' => $message['role'] === 'model' ? 'assistant' : 'user',
                'content' => $message['text']
            ];
        }
        
        $messages[] = [
            'role' => 'user',
            'content' => $prompt
        ];

        $messages = [
            [
                'role' => 'system',
                'content' => $this->getSystemPrompt()
            ]
        ] + $messages;

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'messages' => $messages,
                    'temperature' => 0.7,
                    'max_tokens' => 1024,
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if ($statusCode !== 200) {
                Log::error('OpenAI API Error', [
                    'status' => $statusCode,
                    'response' => $body
                ]);
                throw new \RuntimeException('OpenAI API error: ' . ($data['error']['message'] ?? 'Unknown error'));
            }

            if (!isset($data['choices'][0]['message']['content'])) {
                Log::error('OpenAI Response Missing Content', ['response' => $data]);
                throw new \RuntimeException('OpenAI returned invalid response');
            }

            return $data['choices'][0]['message']['content'];

        } catch (RequestException $e) {
            Log::error('OpenAI Request Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \RuntimeException('OpenAI service is currently unavailable. Please try again later.');
        } catch (\Exception $e) {
            Log::error('OpenAI Unexpected Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function embed(string $text): array
    {
        $url = "{$this->baseUrl}/embeddings";
        
        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->embeddingModel,
                    'input' => $text
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if ($statusCode !== 200) {
                Log::error('OpenAI Embedding Error', [
                    'status' => $statusCode,
                    'response' => $body
                ]);
                throw new \RuntimeException('Embedding service error');
            }

            return $data['data'][0]['embedding'] ?? [];

        } catch (RequestException $e) {
            Log::error('OpenAI Embed Request Failed', [
                'error' => $e->getMessage()
            ]);
            throw new \RuntimeException('Embedding service is currently unavailable');
        }
    }

    public function chatWithImage(string $prompt, string $imageBase64, array $history = []): string
    {
        return $this->chatWithImages($prompt, [$imageBase64], $history);
    }

    public function chatWithImages(string $prompt, array $images, array $history = []): string
    {
        $url = "{$this->baseUrl}/chat/completions";

        $messages = [];

        foreach ($history as $message) {
            $messages[] = [
                'role' => $message['role'] === 'model' ? 'assistant' : 'user',
                'content' => $message['text']
            ];
        }

        $content = [['type' => 'text', 'text' => $prompt]];

        foreach ($images as $imageBase64) {
            $content[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => "data:image/jpeg;base64,{$imageBase64}"
                ]
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $content
        ];

        try {
            $response = $this->client->post($url, [
                'headers' => ['Authorization' => 'Bearer ' . $this->apiKey],
                'json' => [
                    'model' => str_replace('gpt-', 'gpt-4-vision-', $this->model),
                    'messages' => $messages,
                    'max_tokens' => 1024,
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['choices'][0]['message']['content'] ?? '';

        } catch (\Exception $e) {
            Log::error('OpenAI Vision Error', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Rasmni tahlil qilishda xatolik');
        }
    }

    public function getSystemPrompt(): string
    {
        return \App\AiSetting::getSystemPrompt();
    }
}
