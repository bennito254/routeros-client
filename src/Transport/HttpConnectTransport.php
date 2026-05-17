<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Transport;

use Bennito254\RouterOS\Exception\ConnectionException;
use Bennito254\RouterOS\Exception\ProxyException;

class HttpConnectTransport extends StreamTransport
{
    public function connect(): void
    {
        if ($this->isConnected()) {
            return;
        }

        $proxy = $this->config->proxy;
        if ($proxy === null) {
            throw new ConnectionException("Proxy configuration is missing.");
        }

        $target = sprintf('tcp://%s:%d', $proxy->host, $proxy->port);
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
            throw new ProxyException(
                sprintf(
                    'Failed to connect to HTTP proxy %s: %s (Code: %d)',
                    $target,
                    $errorMessage,
                    $errorCode
                )
            );
        }

        stream_set_blocking($this->stream, true);
        $this->setTimeout($this->timeout);

        $this->negotiateHttpConnect();

        if ($this->config->ssl !== null && $this->config->ssl->enabled) {
            $this->upgradeToTls($this->config->ssl);
        }
    }

    private function negotiateHttpConnect(): void
    {
        $proxy = $this->config->proxy;
        $targetHost = $this->config->host . ':' . $this->config->port;

        $request = sprintf("CONNECT %s HTTP/1.1\r\n", $targetHost);
        $request .= sprintf("Host: %s\r\n", $targetHost);

        if ($proxy->username !== null) {
            $auth = base64_encode($proxy->username . ':' . ($proxy->password ?? ''));
            $request .= sprintf("Proxy-Authorization: Basic %s\r\n", $auth);
        }

        $request .= "\r\n";

        $this->write($request);

        $response = '';
        while (true) {
            if (!$this->isConnected()) {
                throw new ProxyException("Connection to HTTP proxy closed unexpectedly.");
            }

            $char = $this->read(1);
            $response .= $char;

            if (str_ends_with($response, "\r\n\r\n")) {
                break;
            }
        }

        // Parse status line, e.g. "HTTP/1.1 200 Connection established"
        $lines = explode("\r\n", $response);
        $statusLine = $lines[0] ?? '';

        if (preg_match('#^HTTP/1\.[01]\s+(\d{3})#', $statusLine, $matches)) {
            $statusCode = (int) $matches[1];
            if ($statusCode < 200 || $statusCode >= 300) {
                throw new ProxyException("HTTP CONNECT proxy rejected connection: " . $statusLine);
            }
        } else {
            throw new ProxyException("Invalid response from HTTP CONNECT proxy.");
        }
    }
}
