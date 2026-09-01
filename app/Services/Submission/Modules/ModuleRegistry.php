<?php

namespace App\Services\Submission\Modules;

use App\Models\SubmissionBatch;
use InvalidArgumentException;

/**
 * Sprint 5: In-memory registry of all module handlers.
 *
 * Replaces the match() blocks in SubmissionBatchService so adding a new module
 * is just creating a new handler + registering it here.
 */
class ModuleRegistry
{
    /** @var array<string, BaseModuleHandler> */
    private array $handlers = [];

    public function register(BaseModuleHandler $handler): void
    {
        $this->handlers[$handler->key()] = $handler;
    }

    public function get(string $key): BaseModuleHandler
    {
        if (! isset($this->handlers[$key])) {
            throw new InvalidArgumentException("Modul [{$key}] tidak dikenal.");
        }
        return $this->handlers[$key];
    }

    public function has(string $key): bool
    {
        return isset($this->handlers[$key]);
    }

    /** @return array<string, BaseModuleHandler> */
    public function all(): array
    {
        return $this->handlers;
    }

    public function availableKeys(): array
    {
        return array_keys($this->handlers);
    }

    /**
     * Build the SubmissionBatch::moduleDefinitions()-compatible array.
     */
    public function definitions(): array
    {
        return collect($this->handlers)
            ->mapWithKeys(fn (BaseModuleHandler $h, string $key) => [
                $key => [
                    'key' => $key,
                    'label' => $h->label(),
                    'description' => sprintf('Modul %s.', $h->label()),
                    'period_type' => $h->periodType(),
                    'badge' => $h->periodType() === BaseModuleHandler::MASTER ? 'Master data' : 'Data periodik',
                    'handler' => $h,
                ],
            ])
            ->all();
    }

    public static function default(): self
    {
        $registry = new self();
        $registry->register(new RumahHandler());
        $registry->register(new KeluargaHandler());
        $registry->register(new PendudukHandler());
        $registry->register(new KesehatanHandler());
        $registry->register(new PendidikanHandler());
        $registry->register(new PuskesHandler());
        $registry->register(new SekolahHandler());

        return $registry;
    }
}
