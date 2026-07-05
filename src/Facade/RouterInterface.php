<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Facade;

use Bennito254\RouterOS\Query\Query;
use Bennito254\RouterOS\Response\ResponseCollection;

/**
 * Facade for managing network interfaces on RouterOS.
 * Named RouterInterface to avoid conflict with PHP's reserved keyword "interface".
 *
 * API Path: /interface
 */
class RouterInterface extends AbstractFacade
{
    protected function getBaseCommand(): string
    {
        return '/interface';
    }

    /**
     * Get list of all interfaces.
     *
     * @return ResponseCollection
     */
    public function getInterfaces(): ResponseCollection
    {
        return $this->getAll();
    }

    /**
     * Monitor traffic in real-time on a specific interface.
     *
     * @param string $interface Name of the interface to monitor (e.g. "ether1").
     * @return ResponseCollection
     */
    public function monitorTraffic(string $interface): ResponseCollection
    {
        $query = Query::make('/interface/monitor-traffic')
            ->equal('interface', $interface)
            ->equal('once', 'true');
        return $this->client->query($query);
    }
}
