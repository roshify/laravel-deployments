<?php
// IDE Helper for Laravel functions

if (! function_exists('config_path')) {
    function config_path(string $path = ''): string
    {
        return '';
    }
}

if (! function_exists('database_path')) {
    function database_path(string $path = ''): string
    {
        return '';
    }
}

if (! function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return '';
    }
}

if (! function_exists('app_path')) {
    function app_path(string $path = ''): string
    {
        return '';
    }
}

if (! function_exists('config')) {
    function config(?string $key = null, mixed $default = null): mixed
    {
        return $default;
    }
}

if (! function_exists('app')) {
    function app(?string $abstract = null, array $parameters = []): mixed
    {
        return null;
    }
}

if (! function_exists('now')) {
    function now($tz = null): \Illuminate\Support\Carbon
    {
        return new \Illuminate\Support\Carbon();
    }
}
