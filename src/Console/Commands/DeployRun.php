<?php

namespace Roshify\LaravelDeployments\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Roshify\LaravelDeployments\Deployment;

class DeployRun extends Command
{
    protected $signature = 'deploy:run
        {--dry : Dry run without executing DB writes}
        {--force : Required when running in production}';

    protected $description = 'Run pending deployment scripts';

    public function handle(): int
    {
        if (config('deployments.require_force_in_production', true) 
            && app()->environment('production') 
            && !$this->option('force')) {
            $this->error('Running deployments in production requires --force.');
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry');
        $table = config('deployments.table', 'deployments');
        $lastBatch = DB::table($table)->max('batch') ?? 0;
        $batch = $lastBatch + 1;
        $path = config('deployments.path', app_path('Deployments'));

        if (!File::exists($path)) {
            $this->info('No deployments directory found.');
            return self::SUCCESS;
        }

        $pending = $this->getPendingDeployments($path, $table);

        if (empty($pending)) {
            $this->info('Nothing to deploy.');
            return self::SUCCESS;
        }

        $this->info("Starting deployment batch {$batch}".($dryRun ? ' [DRY-RUN]' : ''));

        foreach ($pending as $item) {
            $deployment = new $item['class'];

            $this->newLine();
            $this->info("▶ Running: {$item['basename']}");

            foreach ($deployment->summary() as $line) {
                $this->line("  - {$line}");
            }

            try {
                $deployment->run($dryRun, $batch);

                if (!$dryRun) {
                    DB::table($table)->insert([
                        'name' => $item['basename'],
                        'class' => $item['class'],
                        'checksum' => sha1_file($item['file']->getPathname()),
                        'batch' => $batch,
                        'executed_at' => now(),
                    ]);
                }

                $this->info("✓ Completed: {$item['class']}");
            } catch (\Throwable $e) {
                Log::error('Deployment failed', [
                    'deployment' => $item['class'],
                    'batch' => $batch,
                    'exception' => $e,
                ]);

                $this->error("✗ Failed: {$item['class']}");
                $this->error($e->getMessage());

                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->info("Deployment batch {$batch} completed successfully.");

        return self::SUCCESS;
    }

    protected function getPendingDeployments(string $path, string $table): array
    {
        $files = collect(File::files($path))
            ->filter(fn ($file) => $file->getExtension() === 'php' 
                && $file->getBasename() !== 'Deployment.php')
            ->sortBy(fn ($file) => $file->getFilename());

        $pending = [];

        foreach ($files as $file) {
            require_once $file->getPathname();

            $baseName = $file->getBasename('.php');
            $className = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $baseName);
            $class = "App\\Deployments\\{$className}";

            if (!class_exists($class) || !is_subclass_of($class, Deployment::class)) {
                continue;
            }

            if (DB::table($table)->where('name', $baseName)->exists()) {
                continue;
            }

            $pending[] = ['file' => $file, 'class' => $class, 'basename' => $baseName];
        }

        return $pending;
    }
}