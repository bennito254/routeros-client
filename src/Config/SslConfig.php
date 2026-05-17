<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Config;

final class SslConfig
{
    public function __construct(
        public readonly bool $enabled = false,
        public readonly bool $verifyPeer = true,
        public readonly bool $verifyPeerName = true,
        public readonly bool $allowSelfSigned = false,
        public readonly ?string $caFile = null,
    ) {
    }
}
