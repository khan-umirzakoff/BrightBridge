<?php

namespace App\Contracts;

interface AIService
{
    public function chat(string $prompt, array $history = []): string;

    public function chatWithImage(string $prompt, string $imageBase64, array $history = []): string;

    public function chatWithImages(string $prompt, array $images, array $history = []): string;

    public function chatWithThinking(string $prompt, array $history = []): array;

    public function chatWithImagesAndThinking(string $prompt, array $images, array $history = []): array;

    public function embed(string $text): array;

    public function getSystemPrompt(): string;
}
