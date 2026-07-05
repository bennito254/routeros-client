<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Facade;

use Bennito254\RouterOS\Query\Query;
use Bennito254\RouterOS\Response\ResponseCollection;

/**
 * Facade for tracking interface usage statistics and traffic rates on RouterOS.
 *
 * API Path: /interface
 */
class UsageTracker extends AbstractFacade
{
    protected function getBaseCommand(): string
    {
        return '/interface';
    }

    /**
     * Get cumulative stats (bytes in/out, packets, errors) for all or specific interfaces.
     *
     * @param string|null $interface Optional interface name to filter.
     * @return ResponseCollection
     */
    public function getInterfaceStats(?string $interface = null): ResponseCollection
    {
        $query = Query::make('/interface/print');
        
        if ($interface !== null) {
            $query->where('name', $interface);
        }

        // RouterOS prints traffic counters as part of standard print command.
        return $this->client->query($query);
    }

    /**
     * Monitor real-time traffic speeds (bits per second, packets per second) on an interface.
     *
     * @param string $interface Interface name to monitor (e.g. "ether1").
     * @return ResponseCollection
     */
    public function getTrafficRate(string $interface): ResponseCollection
    {
        $query = Query::make('/interface/monitor-traffic')
            ->equal('interface', $interface)
            ->equal('once', 'true');
        return $this->client->query($query);
    }
}
