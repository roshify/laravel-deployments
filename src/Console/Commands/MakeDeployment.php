<?php

namespace Roshify\LaravelDeployments\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeDeployment extends Command
{
    protected $signature = 'make:deployment {name}';
    protected $description = 'Create a new deployment script';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $date = now()->format('Y_m_d_His');
        $fileName = "{$date}_{$name}.php";
        $directory = config('deployments.path', app_path('Deployments'));

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $stubPath = base_path('stubs/deployment-script.stub');

        if (!file_exists($stubPath)) {
            $this->error('Deployment stub not found. Run: php artisan vendor:publish --tag=deployments-stubs');
            return self::FAILURE;
        }

        $stub = file_get_contents($stubPath);

        $content = str_replace(
            ['{{ class }}', '{{ date }}'],
            [$name, now()->toDateString()],
            $stub
        );

        file_put_contents("{$directory}/{$fileName}", $content);

        $this->info("Deployment created: {$fileName}");

        return self::SUCCESS;
    }
}