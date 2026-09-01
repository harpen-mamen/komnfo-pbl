<?php

namespace App\Services;

use App\Services\AI\AIResponse;

/**
 * @deprecated Use App\Services\AI\AIService instead.
 */
class AIService extends \App\Services\AI\AIService
{
    public function chat(string $message, array $history = [], ?string $systemPrompt = null): AIResponse
    {
        return parent::chat($message, $history, $systemPrompt);
    }
}
