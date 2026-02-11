<?php

namespace App\Providers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Transport;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Mail::extend('smtp', function (array $config) {
            $scheme   = in_array(strtolower($config['encryption'] ?? ''), ['ssl', 'smtps']) ? 'smtps' : 'smtp';
            $username = rawurlencode($config['username'] ?? '');
            $password = rawurlencode($config['password'] ?? '');
            $host     = $config['host'] ?? '127.0.0.1';
            $port     = (int) ($config['port'] ?? 465);

            $dsn = "{$scheme}://{$username}:{$password}@{$host}:{$port}?verify_peer=0";

            return Transport::fromDsn($dsn);
        });
    }
}