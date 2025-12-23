<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Deployments Path
    |--------------------------------------------------------------------------
    |
    | The directory where deployment scripts are stored.
    |
    */
    'path' => app_path('Deployments'),

    /*
    |--------------------------------------------------------------------------
    | Deployments Table
    |--------------------------------------------------------------------------
    |
    | The database table used to track executed deployments.
    |
    */
    'table' => 'deployments',

    /*
    |--------------------------------------------------------------------------
    | Require Force in Production
    |--------------------------------------------------------------------------
    |
    | When true, deployments in production require the --force flag.
    |
    */
    'require_force_in_production' => true,

    /*
    |--------------------------------------------------------------------------
    | Enable Rollback
    |--------------------------------------------------------------------------
    |
    | When true, deployments can be rolled back using the rollback() method.
    |
    */
    'enable_rollback' => true,
];