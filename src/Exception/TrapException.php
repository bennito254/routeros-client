<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Exception;

class TrapException extends RouterOSException
{
    private array $data;

    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null, array $data = [])
    {
        parent::__construct($message, $code, $previous);
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
