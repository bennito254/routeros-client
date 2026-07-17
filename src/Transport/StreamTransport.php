<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Transport;

use Bennito254\RouterOS\Config\ClientConfig;
use Bennito254\RouterOS\Config\SslConfig;
use Bennito254\RouterOS\Contracts\TransportInterface;
use Bennito254\RouterOS\Exception\ConnectionException;
use Bennito254\RouterOS\Exception\TimeoutException;

abstract class StreamTransport implements TransportInterface
{
    /**
     * @var resource|null
     */
    protected $stream = null;
    protected int $timeout;

    public function __construct(protected readonly ClientConfig $config)
    {
        $this->timeout = $this->config->timeout;
    }

    public function write(string $data): void
    {
        if (!$this->isConnected()) {
            throw new ConnectionException("Cannot write to closed stream.");
        }

        $length = strlen($data);
        $written = 0;

        while ($written < $length) {
            $result = @fwrite($this->stream, substr($data, $written));

            if ($result === false) {
                $meta = stream_get_meta_data($this->stream);
                if ($meta['timed_out']) {
                    throw new TimeoutException("Write timed out.");
                }
                throw new ConnectionException("Failed to write to stream.");
            }

            if ($result === 0) {
                if (feof($this->stream)) {
                    throw new ConnectionException("Stream closed unexpectedly.");
                }
            }

            $written += $result;
        }
    }

    public function read(int $length): string
    {
        if (!$this->isConnected()) {
            throw new ConnectionException("Cannot read from closed stream.");
        }

        $data = '';
        $read = 0;

        while ($read < $length) {
            $chunk = @fread($this->stream, $length - $read);

            if ($chunk === false) {
                $meta = stream_get_meta_data($this->stream);
                if ($meta['timed_out']) {
                    throw new TimeoutException("Read timed out.");
                }
                throw new ConnectionException("Failed to read from stream.");
            }

            if ($chunk === '') {
                $meta = stream_get_meta_data($this->stream);
                if ($meta['timed_out']) {
                    throw new TimeoutException("Read timed out.");
                }
                if (feof($this->stream)) {
                    throw new ConnectionException("Stream closed unexpectedly while reading.");
                }
            }

            $data .= $chunk;
            $read += strlen($chunk);
        }

        return $data;
    }

    public function close(): void
    {
        if ($this->stream === false) {
            $this->stream = null;
        }

        if ($this->stream !== null) {
            @fclose($this->stream);
            $this->stream = null;
        }
    }

    public function isConnected(): bool
    {
        return $this->stream !== null && is_resource($this->stream) && !feof($this->stream);
    }

    public function setTimeout(int $seconds): void
    {
        $this->timeout = $seconds;
        if ($this->stream !== null && is_resource($this->stream)) {
            stream_set_timeout($this->stream, $seconds);
        }
    }

    protected function upgradeToTls(SslConfig $sslConfig): void
    {
        if (!$this->isConnected()) {
            throw new ConnectionException("Cannot upgrade closed stream to TLS.");
        }

        $contextOptions = [
            'ssl' => [
                'verify_peer' => $sslConfig->verifyPeer,
                'verify_peer_name' => $sslConfig->verifyPeerName,
                'allow_self_signed' => $sslConfig->allowSelfSigned,
            ],
        ];

        if ($sslConfig->caFile !== null) {
            $contextOptions['ssl']['cafile'] = $sslConfig->caFile;
        }

        stream_context_set_option($this->stream, $contextOptions);

        $cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT;
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
            $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
        }
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
            $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
        }

        $result = @stream_socket_enable_crypto($this->stream, true, $cryptoMethod);

        if ($result === false) {
            throw new ConnectionException("Failed to upgrade connection to TLS.");
        }
    }
}
