<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnthropicClient
{
    protected string $endpoint = 'https://api.anthropic.com/v1/messages';

    protected string $apiKey;

    protected string $model;

    public function __construct()
    {
        $this->apiKey = (string) config('services.anthropic.key');
        $this->model = (string) config('services.anthropic.model');
    }

    /**
     * Send a request to the Anthropic Messages API.
     *
     * @param  array<int, array<string, mixed>>  $messages
     * @param  array<int, array<string, mixed>>|null  $tools
     * @param  array<string, mixed>|null  $toolChoice
     * @return array<string, mixed>
     */
    public function send(array $messages, ?string $system = null, ?array $tools = null, ?array $toolChoice = null, int $maxTokens = 1024): array
    {
        if (blank($this->apiKey)) {
            throw new AiRequestException('ANTHROPIC_API_KEY is not configured.');
        }

        $payload = array_filter([
            'model' => $this->model,
            'max_tokens' => $maxTokens,
            'messages' => $messages,
            'system' => $system,
            'tools' => $tools,
            'tool_choice' => $toolChoice,
        ], fn ($value) => ! is_null($value));

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(60)->post($this->endpoint, $payload);

        if ($response->failed()) {
            $message = data_get($response->json(), 'error.message', $response->body());
            Log::warning('Anthropic API request failed', ['status' => $response->status(), 'message' => $message]);

            throw new AiRequestException($message);
        }

        return $response->json();
    }

    /**
     * Extract the first text block from a Messages API response.
     */
    public function textFrom(array $response): ?string
    {
        foreach (data_get($response, 'content', []) as $block) {
            if (($block['type'] ?? null) === 'text') {
                return $block['text'];
            }
        }

        return null;
    }

    /**
     * Extract the first tool_use block's input from a Messages API response.
     *
     * @return array<string, mixed>|null
     */
    public function toolInputFrom(array $response, ?string $toolName = null): ?array
    {
        foreach (data_get($response, 'content', []) as $block) {
            if (($block['type'] ?? null) !== 'tool_use') {
                continue;
            }

            if ($toolName && ($block['name'] ?? null) !== $toolName) {
                continue;
            }

            return $block['input'] ?? [];
        }

        return null;
    }

    /**
     * Extract all tool_use blocks from a Messages API response.
     *
     * @return array<int, array{id: string, name: string, input: array<string, mixed>}>
     */
    public function toolUsesFrom(array $response): array
    {
        return array_values(array_filter(
            data_get($response, 'content', []),
            fn ($block) => ($block['type'] ?? null) === 'tool_use',
        ));
    }
}
