# Laravel Deployments

A simple, migration-like deployment script manager for Laravel applications.

## Installation

```bash
composer require sthira-labs/laravel-deployments
```

## Setup

Publish the configuration and migration:

```bash
php artisan vendor:publish --tag=deployments-config
php artisan vendor:publish --tag=deployments-migrations
php artisan vendor:publish --tag=deployments-stubs
```

Run the migration:

```bash
php artisan migrate
```

## Usage

### Create a Deployment

```bash
php artisan make:deployment PatAutomation
```

This creates a timestamped file in `app/Deployments/`.

### Run Deployments

```bash
# Dry run (no DB writes)
php artisan deploy:run --dry

# Execute deployments
php artisan deploy:run

# Production (requires --force)
php artisan deploy:run --force
```

### Check Status

```bash
php artisan deploy:status
```

### Rollback

```bash
# Rollback last batch
php artisan deploy:rollback

# Rollback specific batch
php artisan deploy:rollback --batch=1

# Production rollback
php artisan deploy:rollback --force
```

## Example Deployment

```php
<?php

namespace App\Deployments;

use SthiraLabs\LaravelDeployments\Deployment;
use Illuminate\Support\Facades\DB;

class PatAutomation extends Deployment
{
    public function summary(): array
    {
        return [
            'Run User seeder',
            'Assign permissions to Admin role',
        ];
    }

    protected function handle(): void
    {
        $this->write(
            fn () => DB::table('users')->update(['active' => true]),
            'Activate all users'
        );
    }

    protected function rollback(): void
    {
        DB::table('users')->update(['active' => false]);
    }
}
```

## Configuration

Edit `config/deployments.php` to customize:

- `path`: Deployment scripts directory
- `table`: Database table name
- `require_force_in_production`: Require --force flag in production
- `enable_rollback`: Enable/disable rollback functionality

## Maintainer

Roshan Poojary (GitHub: @sthira-labs)

## License

MIT