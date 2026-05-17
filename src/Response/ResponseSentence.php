<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Response;

class ResponseSentence
{
    public const TYPE_RE = '!re';
    public const TYPE_DONE = '!done';
    public const TYPE_TRAP = '!trap';
    public const TYPE_FATAL = '!fatal';

    public function __construct(
        private readonly string $type,
        private readonly array $attributes = [],
        private readonly ?string $tag = null
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function isType(string $type): bool
    {
        return $this->type === $type;
    }

    public static function parse(array $words): self
    {
        if (empty($words)) {
            throw new \InvalidArgumentException("Cannot parse empty words array.");
        }

        $type = array_shift($words);
        $attributes = [];
        $tag = null;

        foreach ($words as $word) {
            if (str_starts_with($word, '.tag=')) {
                $tag = substr($word, 5);
                continue;
            }

            if (str_starts_with($word, '=')) {
                $parts = explode('=', substr($word, 1), 2);
                if (count($parts) === 2) {
                    $attributes[$parts[0]] = $parts[1];
                } else {
                    $attributes[$parts[0]] = '';
                }
            }
        }

        return new self($type, $attributes, $tag);
    }
}
