<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Protocol;

use Bennito254\RouterOS\Exception\ProtocolException;

class Encoder
{
    /**
     * Encode a single word (with length prefix).
     */
    public function encodeWord(string $word): string
    {
        return $this->encodeLength(strlen($word)) . $word;
    }

    /**
     * Encode a sentence (array of words) ending with a zero byte.
     */
    public function encodeSentence(array $words): string
    {
        $encoded = '';
        foreach ($words as $word) {
            $encoded .= $this->encodeWord((string)$word);
        }
        $encoded .= chr(0);

        return $encoded;
    }

    /**
     * Encode the length of a word.
     */
    public function encodeLength(int $length): string
    {
        if ($length < 0) {
            throw new ProtocolException("Length cannot be negative.");
        }

        if ($length < 0x80) {
            return chr($length);
        }

        if ($length < 0x4000) {
            $length |= 0x8000;
            return pack('n', $length);
        }

        if ($length < 0x200000) {
            $length |= 0xC00000;
            $packed = pack('N', $length);
            return substr($packed, 1);
        }

        if ($length < 0x10000000) {
            $length |= 0xE0000000;
            return pack('N', $length);
        }

        if ($length <= 0xFFFFFFFF) {
            return chr(0xF0) . pack('N', $length);
        }

        throw new ProtocolException("Length too large to encode.");
    }
}
