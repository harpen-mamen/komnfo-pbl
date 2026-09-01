<?php

namespace Tests\Unit;

use App\Services\AI\AIException;
use App\Services\AI\AIService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AIServiceTest extends TestCase
{
    public function test_it_returns_a_consistent_ai_response(): void
    {
        config([
            'ai.api_key' => 'test-key',
            'ai.base_url' => 'https://gateway.example.test',
        ]);

        Http::fake([
            '*' => Http::response([
                'id' => 'message_123',
                'model' => 'claude-sonnet-4-5',
                'content' => [['type' => 'text', 'text' => 'Halo!']],
                'usage' => ['input_tokens' => 4, 'output_tokens' => 2],
            ]),
        ]);

        $response = app(AIService::class)->chat('Halo');

        $this->assertSame('Halo!', $response->content);
        $this->assertSame(4, $response->inputTokens);
        $this->assertSame(2, $response->outputTokens);
        $this->assertSame('Halo!', $response->toArray()['content']);
    }

    public function test_it_throws_a_domain_exception_for_gateway_errors(): void
    {
        Http::fake(['*' => Http::response(['error' => ['message' => 'failed']], 500)]);

        $this->expectException(AIException::class);
        $this->expectExceptionMessage('AI Gateway mengembalikan error.');

        app(AIService::class)->chat('Halo');
    }
}
