<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Query;

class Query
{
    private string $command;
    private array $attributes = [];
    private array $queries = [];
    private ?string $tag = null;

    public function __construct(string $command)
    {
        $this->command = $command;
    }

    public static function make(string $command): self
    {
        return new self($command);
    }

    /**
     * Add an attribute assignment (=key=value).
     */
    public function equal(string $key, string|int|bool $value): self
    {
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }
        $this->attributes[$key] = (string)$value;
        return $this;
    }

    /**
     * Set a property value without equal sign syntax, or shorthand.
     */
    public function set(string $key, string|int|bool $value): self
    {
        return $this->equal($key, $value);
    }

    /**
     * Add a query condition (?key=value).
     */
    public function where(string $key, string|int|bool $value): self
    {
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }
        $this->queries[] = sprintf('?%s=%s', $key, $value);
        return $this;
    }

    /**
     * Add a query condition (?key).
     */
    public function whereExists(string $key): self
    {
        $this->queries[] = sprintf('?%s', $key);
        return $this;
    }

    /**
     * Add a negative query condition (?-key).
     */
    public function whereNotExists(string $key): self
    {
        $this->queries[] = sprintf('?-%s', $key);
        return $this;
    }

    /**
     * Add a query condition with operator.
     * e.g., whereOp('name', 'ether1', '>') becomes '?<key>=<value>' ... no wait,
     * RouterOS uses ?<key>=<value> or ?>key=value or ?<key=value etc.
     */
    public function whereOp(string $key, string $operator, string|int|bool $value): self
    {
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }
        $this->queries[] = sprintf('?%s%s=%s', $operator, $key, $value);
        return $this;
    }

    /**
     * Query operations: ?#&, ?#|, ?#!
     */
    public function whereAnd(): self
    {
        $this->queries[] = '?#&';
        return $this;
    }

    public function whereOr(): self
    {
        $this->queries[] = '?#|';
        return $this;
    }

    public function whereNot(): self
    {
        $this->queries[] = '?#!';
        return $this;
    }

    public function tag(string $tag): self
    {
        $this->tag = $tag;
        return $this;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getWords(): array
    {
        $words = [$this->command];

        foreach ($this->attributes as $key => $value) {
            $words[] = sprintf('=%s=%s', $key, $value);
        }

        foreach ($this->queries as $query) {
            $words[] = $query;
        }

        if ($this->tag !== null) {
            $words[] = '.tag=' . $this->tag;
        }

        return $words;
    }
}
