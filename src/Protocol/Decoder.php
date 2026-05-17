<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Protocol;

use Bennito254\RouterOS\Contracts\TransportInterface;
use Bennito254\RouterOS\Exception\ProtocolException;

class Decoder
{
    public function __construct(private readonly TransportInterface $transport)
    {
    }

    /**
     * Read and decode a single sentence (array of words).
     */
    public function readSentence(): array
    {
        $words = [];
        while (true) {
            $word = $this->readWord();
            if ($word === '') {
                break;
            }
            $words[] = $word;
        }

        return $words;
    }

    /**
     * Read and decode a single word.
     */
    private function readWord(): string
    {
        $length = $this->readLength();
        if ($length === 0) {
            return '';
        }

        return $this->transport->read($length);
    }

    /**
     * Read and decode the length prefix.
     */
    private function readLength(): int
    {
        $byte = $this->transport->read(1);
        if ($byte === '') {
            return 0; // Empty byte means end of sentence or stream
        }

        $b = ord($byte);

        if (($b & 0x80) === 0x00) {
            return $b;
        }

        if (($b & 0xC0) === 0x80) {
            $byte2 = ord($this->transport->read(1));
            return (($b & ~0xC0) << 8) | $byte2;
        }

        if (($b & 0xE0) === 0xC0) {
            $byte2 = ord($this->transport->read(1));
            $byte3 = ord($this->transport->read(1));
            return (($b & ~0xE0) << 16) | ($byte2 << 8) | $byte3;
        }

        if (($b & 0xF0) === 0xE0) {
            $byte2 = ord($this->transport->read(1));
            $byte3 = ord($this->transport->read(1));
            $byte4 = ord($this->transport->read(1));
            return (($b & ~0xF0) << 24) | ($byte2 << 16) | ($byte3 << 8) | $byte4;
        }

        if (($b & 0xF8) === 0xF0) {
            $byte2 = ord($this->transport->read(1));
            $byte3 = ord($this->transport->read(1));
            $byte4 = ord($this->transport->read(1));
            $byte5 = ord($this->transport->read(1));
            return ($byte2 << 24) | ($byte3 << 16) | ($byte4 << 8) | $byte5;
        }

        throw new ProtocolException("Invalid length encoding received.");
    }
}
