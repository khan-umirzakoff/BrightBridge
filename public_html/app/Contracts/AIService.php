<?php

namespace App\Contracts;

interface AIService
{
    public function chat(string $prompt, array $history = []): string;
    
    public function chatWithImage(string $prompt, string $imageBase64, array $history = []): string;
    
    public function embed(string $text): array;
}
