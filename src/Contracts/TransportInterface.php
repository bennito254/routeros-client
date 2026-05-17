<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Contracts;

interface TransportInterface
{
    /**
     * Connect to the endpoint.
     */
    public function connect(): void;

    /**
     * Write data to the stream.
     */
    public function write(string $data): void;

    /**
     * Read exactly $length bytes from the stream.
     */
    public function read(int $length): string;

    /**
     * Close the connection.
     */
    public function close(): void;

    /**
     * Check if the transport is connected.
     */
    public function isConnected(): bool;

    /**
     * Set a timeout for stream operations.
     */
    public function setTimeout(int $seconds): void;
}
