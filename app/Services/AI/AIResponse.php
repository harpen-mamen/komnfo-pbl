<?php

namespace App\Services\AI;

use Illuminate\Contracts\Support\Arrayable;

class AIResponse implements Arrayable
{
    public function __construct(
        public readonly string $content,
        public readonly array $raw,
        public readonly ?string $id = null,
        public readonly ?string $model = null,
        public readonly ?int $inputTokens = null,
        public readonly ?int $outputTokens = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'id' => $this->id,
            'model' => $this->model,
            'usage' => [
                'input_tokens' => $this->inputTokens,
                'output_tokens' => $this->outputTokens,
            ],
            'raw' => $this->raw,
        ];
    }
}
