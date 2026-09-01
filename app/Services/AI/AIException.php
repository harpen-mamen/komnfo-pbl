<?php

namespace App\Services\AI;

use RuntimeException;
use Throwable;

class AIException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $status = null,
        public readonly ?array $response = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $status ?? 0, $previous);
    }
}
