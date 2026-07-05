<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Facade;

use Bennito254\RouterOS\Query\Query;
use Bennito254\RouterOS\Response\ResponseCollection;

/**
 * Facade for monitoring system resource usage, managing system identity,
 * and performing system actions (reboot, shutdown) on RouterOS.
 *
 * API Path: /system/resource
 */
class System extends AbstractFacade
{
    protected function getBaseCommand(): string
    {
        return '/system/resource';
    }

    /**
     * Get system resource statistics (CPU, memory, disk, uptime).
     *
     * @return ResponseCollection
     */
    public function getResources(): ResponseCollection
    {
        return $this->getAll();
    }

    /**
     * Get the device system identity.
     *
     * @return ResponseCollection
     */
    public function getIdentity(): ResponseCollection
    {
        $query = Query::make('/system/identity/print');
        return $this->client->query($query);
    }

    /**
     * Set the device system identity (hostname).
     *
     * @param string $name New identity name.
     * @return ResponseCollection
     */
    public function setIdentity(string $name): ResponseCollection
    {
        $query = Query::make('/system/identity/set')
            ->equal('name', $name);
        return $this->client->query($query);
    }

    /**
     * Get details of the Routerboard hardware.
     *
     * @return ResponseCollection
     */
    public function getRouterboard(): ResponseCollection
    {
        $query = Query::make('/system/routerboard/print');
        return $this->client->query($query);
    }

    /**
     * List all installed RouterOS software packages.
     *
     * @return ResponseCollection
     */
    public function getPackages(): ResponseCollection
    {
        $query = Query::make('/system/package/print');
        return $this->client->query($query);
    }

    /**
     * Reboot the RouterOS device.
     *
     * @return ResponseCollection
     */
    public function reboot(): ResponseCollection
    {
        $query = Query::make('/system/reboot');
        return $this->client->query($query);
    }

    /**
     * Shutdown the RouterOS device.
     *
     * @return ResponseCollection
     */
    public function shutdown(): ResponseCollection
    {
        $query = Query::make('/system/shutdown');
        return $this->client->query($query);
    }
}
