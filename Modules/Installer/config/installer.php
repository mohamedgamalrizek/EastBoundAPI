<?php

return [
    'env_key' => 'APP_INSTALLED',
    // Read this once through Laravel configuration. With config caching enabled,
    // changing .env requires the standard `php artisan optimize:clear` refresh.
    'installed' => env('APP_INSTALLED', null),
    'required_tables' => ['users', 'roles', 'settings'],
    'writable_paths' => ['.env', 'bootstrap/cache', 'storage', 'storage/app/public'],
    'required_extensions' => ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'curl', 'fileinfo', 'bcmath', 'json'],
];
