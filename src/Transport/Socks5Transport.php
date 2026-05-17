<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Transport;

use Bennito254\RouterOS\Exception\ConnectionException;
use Bennito254\RouterOS\Exception\ProxyException;

class Socks5Transport extends StreamTransport
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
                    'Failed to connect to SOCKS5 proxy %s: %s (Code: %d)',
                    $target,
                    $errorMessage,
                    $errorCode
                )
            );
        }

        stream_set_blocking($this->stream, true);
        $this->setTimeout($this->timeout);

        $this->negotiateSocks5();

        if ($this->config->ssl !== null && $this->config->ssl->enabled) {
            $this->upgradeToTls($this->config->ssl);
        }
    }

    private function negotiateSocks5(): void
    {
        $proxy = $this->config->proxy;
        $hasAuth = $proxy->username !== null;

        // 1. Greeting
        // \x05 (SOCKS5), \x01 (1 method), \x00 (No Auth) or \x02 (User/Pass)
        $method = $hasAuth ? "\x02" : "\x00";
        $this->write("\x05\x01" . $method);

        $response = $this->read(2);
        if (strlen($response) !== 2 || $response[0] !== "\x05") {
            throw new ProxyException("Invalid SOCKS5 greeting response from proxy.");
        }

        if ($response[1] === "\xFF") {
            throw new ProxyException("SOCKS5 proxy rejected authentication methods.");
        }

        // 2. Authentication
        if ($hasAuth) {
            if ($response[1] !== "\x02") {
                throw new ProxyException("SOCKS5 proxy does not support username/password authentication.");
            }

            $userLen = chr(strlen($proxy->username));
            $passLen = chr(strlen($proxy->password ?? ''));
            $this->write("\x01" . $userLen . $proxy->username . $passLen . ($proxy->password ?? ''));

            $authResponse = $this->read(2);
            if (strlen($authResponse) !== 2 || $authResponse[0] !== "\x01" || $authResponse[1] !== "\x00") {
                throw new ProxyException("SOCKS5 proxy authentication failed.");
            }
        }

        // 3. Connect request
        $hostLen = chr(strlen($this->config->host));
        $portPack = pack('n', $this->config->port);

        // \x05 (SOCKS5), \x01 (Connect), \x00 (Reserved), \x03 (Domain name)
        $this->write("\x05\x01\x00\x03" . $hostLen . $this->config->host . $portPack);

        // Read response header: Version, Reply, Reserved, AddressType
        $connResponse = $this->read(4);
        if (strlen($connResponse) !== 4 || $connResponse[0] !== "\x05") {
            throw new ProxyException("Invalid SOCKS5 connect response.");
        }

        if ($connResponse[1] !== "\x00") {
            $code = ord($connResponse[1]);
            throw new ProxyException("SOCKS5 connection failed with reply code: " . $code);
        }

        // Consume address and port bound by proxy
        $addrType = $connResponse[3];
        if ($addrType === "\x01") { // IPv4
            $this->read(4 + 2); // 4 bytes IP + 2 bytes port
        } elseif ($addrType === "\x03") { // Domain
            $domainLenStr = $this->read(1);
            $domainLen = ord($domainLenStr);
            $this->read($domainLen + 2);
        } elseif ($addrType === "\x04") { // IPv6
            $this->read(16 + 2);
        } else {
            throw new ProxyException("Unknown address type in SOCKS5 response.");
        }
    }
}
