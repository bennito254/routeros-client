<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Facade;

use Bennito254\RouterOS\Query\Query;
use Bennito254\RouterOS\Response\ResponseCollection;

/**
 * Facade for managing IP pools on RouterOS.
 *
 * API Path: /ip/pool
 */
class IpPool extends AbstractFacade
{
    protected function getBaseCommand(): string
    {
        return '/ip/pool';
    }

    /**
     * Get list of all IP pools.
     *
     * @return ResponseCollection
     */
    public function getPools(): ResponseCollection
    {
        return $this->getAll();
    }

    /**
     * Add a new IP Pool.
     *
     * @param string $name Name of the IP pool.
     * @param string $ranges Ranges assigned (e.g. "192.168.88.10-192.168.88.250").
     * @param array $extra Additional attributes.
     * @return ResponseCollection
     */
    public function addPool(string $name, string $ranges, array $extra = []): ResponseCollection
    {
        $data = array_merge([
            'name' => $name,
            'ranges' => $ranges,
        ], $extra);

        return $this->add($data);
    }
}
