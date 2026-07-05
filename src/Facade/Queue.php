<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Facade;

use Bennito254\RouterOS\Query\Query;
use Bennito254\RouterOS\Response\ResponseCollection;

/**
 * Facade for managing simple queues and traffic shaping on RouterOS.
 *
 * API Path: /queue/simple
 */
class Queue extends AbstractFacade
{
    protected function getBaseCommand(): string
    {
        return '/queue/simple';
    }

    /**
     * Add a simple queue configuration.
     *
     * @param string $name Name of the queue.
     * @param string $target Target IP address or range (e.g. 192.168.88.10 or 192.168.88.0/24).
     * @param string $maxLimit Max upload/download speed limits (e.g. "10M/10M" or "512k/2M").
     * @param array $extra Additional attributes.
     * @return ResponseCollection
     */
    public function addSimpleQueue(string $name, string $target, string $maxLimit, array $extra = []): ResponseCollection
    {
        $data = array_merge([
            'name' => $name,
            'target' => $target,
            'max-limit' => $maxLimit,
        ], $extra);

        return $this->add($data);
    }

    /**
     * Find a simple queue by its target IP.
     *
     * @param string $target Target IP address or range.
     * @return ResponseCollection
     */
    public function findByTarget(string $target): ResponseCollection
    {
        $query = Query::make($this->getBaseCommand() . '/print')
            ->where('target', $target);
        return $this->client->query($query);
    }
}
