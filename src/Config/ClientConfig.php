<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Config;

final class ClientConfig
{
    public function __construct(
        public readonly string $host,
        public readonly string $username,
        public readonly string $password,
        public readonly int $port = 8728,
        public readonly int $timeout = 10,
        public readonly ?ProxyConfig $proxy = null,
        public readonly ?SslConfig $ssl = null,
        public readonly int $reconnectAttempts = 1,
    ) {
    }

    public static function fromArray(array $config): self
    {
        $proxy = null;
        if (isset($config['proxy']) && is_array($config['proxy'])) {
            $proxy = new ProxyConfig(
                type: $config['proxy']['type'] ?? ProxyConfig::TYPE_SOCKS5,
                host: $config['proxy']['host'],
                port: (int)$config['proxy']['port'],
                username: $config['proxy']['username'] ?? null,
                password: $config['proxy']['password'] ?? null,
            );
        }

        $ssl = null;
        if (isset($config['ssl']) && is_array($config['ssl'])) {
            $ssl = new SslConfig(
                enabled: $config['ssl']['enabled'] ?? false,
                verifyPeer: $config['ssl']['verify_peer'] ?? true,
                verifyPeerName: $config['ssl']['verify_peer_name'] ?? true,
                allowSelfSigned: $config['ssl']['allow_self_signed'] ?? false,
                caFile: $config['ssl']['ca_file'] ?? null,
            );
        }

        return new self(
            host: $config['host'],
            username: $config['username'],
            password: $config['password'],
            port: (int)($config['port'] ?? 8728),
            timeout: (int)($config['timeout'] ?? 10),
            proxy: $proxy,
            ssl: $ssl,
            reconnectAttempts: (int)($config['reconnect_attempts'] ?? 1)
        );
    }
}
