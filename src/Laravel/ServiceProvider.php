<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Laravel;

use Bennito254\RouterOS\Client\Client;
use Bennito254\RouterOS\Config\ClientConfig;
use Bennito254\RouterOS\Config\ProxyConfig;
use Bennito254\RouterOS\Config\SslConfig;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Psr\Log\LoggerInterface;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                // @phpstan-ignore-next-line
                __DIR__ . '/../../config/routeros.php' => config_path('routeros.php'),
            ], 'routeros-config');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/routeros.php', 'routeros');

        $this->app->singleton(Client::class, function ($app) {
            $config = $app['config']['routeros'];

            $proxy = null;
            if ($config['proxy']['enabled'] ?? false) {
                $proxy = new ProxyConfig(
                    type: $config['proxy']['type'] === 'http' ? ProxyConfig::TYPE_HTTP : ProxyConfig::TYPE_SOCKS5,
                    host: $config['proxy']['host'],
                    port: (int) $config['proxy']['port'],
                    username: $config['proxy']['username'],
                    password: $config['proxy']['password'],
                );
            }

            $ssl = null;
            if ($config['ssl']['enabled'] ?? false) {
                $ssl = new SslConfig(
                    enabled: true,
                    verifyPeer: $config['ssl']['verify_peer'] ?? false,
                    verifyPeerName: $config['ssl']['verify_peer_name'] ?? false,
                    allowSelfSigned: $config['ssl']['allow_self_signed'] ?? true,
                );
            }

            $clientConfig = new ClientConfig(
                host: $config['host'],
                username: $config['username'],
                password: $config['password'],
                port: (int) $config['port'],
                timeout: (int) $config['timeout'],
                proxy: $proxy,
                ssl: $ssl,
                reconnectAttempts: (int) ($config['reconnect_attempts'] ?? 1),
            );

            $client = new Client($clientConfig);

            if ($app->bound(LoggerInterface::class)) {
                $client->setLogger($app->make(LoggerInterface::class));
            }

            return $client;
        });

        // Register the alias
        $this->app->alias(Client::class, 'routeros');
    }
}
