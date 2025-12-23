<?php

namespace Roshify\LaravelDeployments;

use Illuminate\Support\Facades\Log;

abstract class Deployment
{
    protected bool $dryRun = false;
    protected int $batch;
    protected array $executed = [];

    final public function run(bool $dryRun = false, int $batch = 1): void
    {
        $this->dryRun = $dryRun;
        $this->batch = $batch;

        Log::info('Deployment started', [
            'deployment' => static::class,
            'batch' => $batch,
            'dry_run' => $dryRun,
        ]);

        $this->handle();

        Log::info('Deployment finished', [
            'deployment' => static::class,
            'batch' => $batch,
            'dry_run' => $dryRun,
            'executed_operations' => count($this->executed),
        ]);
    }

    final public function runRollback(): void
    {
        if (!config('deployments.enable_rollback', true)) {
            throw new \RuntimeException('Rollback is disabled in configuration');
        }

        Log::info('Rollback started', ['deployment' => static::class]);

        $this->rollback();

        Log::info('Rollback finished', ['deployment' => static::class]);
    }

    protected function write(callable $callback, string $description = ''): void
    {
        if ($this->dryRun) {
            Log::info('[DRY-RUN] '.$description, ['deployment' => static::class]);
            return;
        }

        $this->executed[] = $description;
        $callback();
    }

    /**
     * Human-readable summary (shown before execution).
     */
    abstract public function summary(): array;

    /**
     * Deployment logic.
     */
    abstract protected function handle(): void;

    /**
     * Rollback logic (optional).
     */
    protected function rollback(): void
    {
        throw new \RuntimeException('Rollback not implemented for '.static::class);
    }
}