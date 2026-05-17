<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Transport;

use Bennito254\RouterOS\Exception\ConnectionException;

class DirectTransport extends StreamTransport
{
    public function connect(): void
    {
        if ($this->isConnected()) {
            return;
        }

        $target = sprintf('tcp://%s:%d', $this->config->host, $this->config->port);
        $context = stream_context_create();

        $this->stream = @stream_socket_client(
            $target,
            $errorCode,
            $errorMessage,
            $this->timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if ($this->stream === false) {
            throw new ConnectionException(
                sprintf(
                    'Failed to connect to %s: %s (Code: %d)',
                    $target,
                    $errorMessage,
                    $errorCode
                )
            );
        }

        stream_set_blocking($this->stream, true);
        $this->setTimeout($this->timeout);

        if ($this->config->ssl !== null && $this->config->ssl->enabled) {
            $this->upgradeToTls($this->config->ssl);
        }
    }
}
