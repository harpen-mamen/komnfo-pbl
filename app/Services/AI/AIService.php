<?php

namespace App\Services\AI;

use Throwable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    public function chat(string $message, array $history = [], ?string $systemPrompt = null): AIResponse
    {
        $message = trim($message);

        if ($message === '') {
            throw new AIException('Pesan AI tidak boleh kosong.');
        }

        $url = rtrim((string) config('ai.base_url'), '/') . '/anthropic/v1/messages';

        try {
            $response = Http::timeout((int) config('ai.timeout', 120))
                ->retry(
                    (int) config('ai.retry.times', 3),
                    (int) config('ai.retry.sleep', 200),
                    throw: false,
                )
                ->withHeaders([
                    'x-api-key' => config('ai.api_key'),
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->post($url, [
                    'model' => config('ai.model'),
                    'max_tokens' => config('ai.max_tokens'),
                    'temperature' => config('ai.temperature'),
                    'system' => $systemPrompt ?? config('ai.system_prompt'),
                    'messages' => array_merge(
                        array_map(fn (array $item): array => [
                            'role' => $item['role'],
                            'content' => $item['content'],
                        ], $history),
                        [[
                            'role' => 'user',
                            'content' => [[
                                'type' => 'text',
                                'text' => $message,
                            ]],
                        ]]
                    ),
                ]);
        } catch (Throwable $exception) {
            Log::error('AI gateway request failed.', [
                'exception' => $exception,
                'url' => $url,
            ]);

            throw new AIException('AI Gateway tidak dapat dihubungi.', previous: $exception);
        }

        if ($response->failed()) {
            $this->logFailure($response);

            throw new AIException(
                'AI Gateway mengembalikan error.',
                $response->status(),
                $response->json(),
            );
        }

        $payload = $response->json();
        $content = collect($payload['content'] ?? [])
            ->where('type', 'text')
            ->pluck('text')
            ->implode('');

        if ($content === '') {
            Log::warning('AI gateway returned an empty response.', ['response' => $payload]);
        }

        return new AIResponse(
            content: $content,
            raw: $payload,
            id: $payload['id'] ?? null,
            model: $payload['model'] ?? null,
            inputTokens: $payload['usage']['input_tokens'] ?? null,
            outputTokens: $payload['usage']['output_tokens'] ?? null,
        );
    }

    private function logFailure(Response $response): void
    {
        Log::error('AI gateway returned an error response.', [
            'status' => $response->status(),
            'response' => $response->json(),
        ]);
    }
}
