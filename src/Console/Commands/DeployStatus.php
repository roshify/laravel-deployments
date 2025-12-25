<?php

namespace SthiraLabs\LaravelDeployments\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DeployStatus extends Command
{
    protected $signature = 'deploy:status';
    protected $description = 'Show the status of all deployment scripts';

    public function handle(): int
    {
        $table = config('deployments.table', 'deployments');
        $path = config('deployments.path', app_path('Deployments'));

        if (!File::exists($path)) {
            $this->info('No deployments directory found.');
            return self::SUCCESS;
        }

        $files = collect(File::files($path))
            ->filter(fn ($file) => $file->getExtension() === 'php' 
                && $file->getBasename() !== 'Deployment.php')
            ->sortBy(fn ($file) => $file->getFilename());

        $executed = DB::table($table)->pluck('executed_at', 'name');

        $rows = [];

        foreach ($files as $file) {
            $baseName = $file->getBasename('.php');
            $className = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $baseName);
            $class = "App\\Deployments\\{$className}";

            if (!class_exists($class)) {
                require_once $file->getPathname();
            }

            $status = $executed->has($class) ? '✓ Executed' : '⏳ Pending';
            $executedAt = $executed->get($class, 'N/A');

            $rows[] = [
                $className,
                $status,
                $executedAt instanceof \Carbon\Carbon ? $executedAt->format('Y-m-d H:i:s') : $executedAt,
            ];
        }

        $this->table(['Deployment', 'Status', 'Executed At'], $rows);

        return self::SUCCESS;
    }
}