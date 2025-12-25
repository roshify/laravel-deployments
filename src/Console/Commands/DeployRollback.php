<?php

namespace SthiraLabs\LaravelDeployments\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeployRollback extends Command
{
    protected $signature = 'deploy:rollback
        {--batch= : The batch number to rollback}
        {--force : Required when running in production}';

    protected $description = 'Rollback the last deployment batch';

    public function handle(): int
    {
        if (!config('deployments.enable_rollback', true)) {
            $this->error('Rollback is disabled in configuration.');
            return self::FAILURE;
        }

        if (app()->environment('production') && !$this->option('force')) {
            $this->error('Rolling back deployments in production requires --force.');
            return self::FAILURE;
        }

        $table = config('deployments.table', 'deployments');
        $batch = $this->option('batch') ?? DB::table($table)->max('batch');

        if (!$batch) {
            $this->info('Nothing to rollback.');
            return self::SUCCESS;
        }

        $deployments = DB::table($table)
            ->where('batch', $batch)
            ->orderByDesc('id')
            ->get();

        if ($deployments->isEmpty()) {
            $this->info("No deployments found for batch {$batch}.");
            return self::SUCCESS;
        }

        $this->info("Rolling back batch {$batch}...");

        foreach ($deployments as $record) {
            $this->newLine();
            $this->info("▶ Rolling back: {$record->name}");

            // Try to load the class file
            $path = config('deployments.path', app_path('Deployments'));
            $files = \Illuminate\Support\Facades\File::files($path);
            
            foreach ($files as $file) {
                $baseName = $file->getBasename('.php');
                $className = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $baseName);
                $class = "App\\Deployments\\{$className}";
                
                if ($record->class === $class && !class_exists($record->class)) {
                    require_once $file->getPathname();
                    break;
                }
            }

            if (!class_exists($record->class)) {
                $this->warn("Class {$record->class} not found. Skipping...");
                continue;
            }

            try {
                $deployment = new $record->name;
                $deployment->runRollback();

                DB::table($table)->where('id', $record->id)->delete();

                $this->info("✓ Rolled back: {$record->name}");
            } catch (\Throwable $e) {
                Log::error('Rollback failed', [
                    'deployment' => $record->name,
                    'batch' => $batch,
                    'exception' => $e,
                ]);

                $this->error("✗ Failed: {$record->name}");
                $this->error($e->getMessage());

                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->info("Batch {$batch} rolled back successfully.");

        return self::SUCCESS;
    }
}