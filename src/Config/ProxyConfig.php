<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Config;

final class ProxyConfig
{
    public const TYPE_SOCKS5 = 'socks5';
    public const TYPE_HTTP = 'http';

    public function __construct(
        public readonly string $type,
        public readonly string $host,
        public readonly int $port,
        public readonly ?string $username = null,
        public readonly ?string $password = null,
    ) {
        if (!in_array($this->type, [self::TYPE_SOCKS5, self::TYPE_HTTP], true)) {
            throw new \InvalidArgumentException("Invalid proxy type. Supported: socks5, http");
        }
    }
}
