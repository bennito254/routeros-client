<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Facade;

use Bennito254\RouterOS\Query\Query;
use Bennito254\RouterOS\Response\ResponseCollection;

/**
 * Facade for managing DHCP Servers and DHCP static/dynamic leases on RouterOS.
 *
 * API Path: /ip/dhcp-server
 */
class Dhcp extends AbstractFacade
{
    protected function getBaseCommand(): string
    {
        return '/ip/dhcp-server';
    }

    /**
     * Get list of all DHCP servers.
     *
     * @return ResponseCollection
     */
    public function getServers(): ResponseCollection
    {
        return $this->getAll();
    }

    /**
     * Get all current DHCP leases (active, static, or dynamic).
     *
     * @return ResponseCollection
     */
    public function getLeases(): ResponseCollection
    {
        $query = Query::make('/ip/dhcp-server/lease/print');
        return $this->client->query($query);
    }

    /**
     * Add a static DHCP lease.
     *
     * @param string $address IP address to assign (e.g. "192.168.88.50").
     * @param string $macAddress MAC address of client device (e.g. "00:11:22:33:44:55").
     * @param string $server Name of the DHCP server instance or "all".
     * @param array $extra Additional parameters.
     * @return ResponseCollection
     */
    public function addLease(string $address, string $macAddress, string $server = 'all', array $extra = []): ResponseCollection
    {
        $query = Query::make('/ip/dhcp-server/lease/add')
            ->equal('address', $address)
            ->equal('mac-address', $macAddress)
            ->equal('server', $server);
        foreach ($extra as $key => $value) {
            $query->equal($key, $value);
        }
        return $this->client->query($query);
    }

    /**
     * Convert a dynamic DHCP lease into a static lease.
     *
     * @param string $id RouterOS internal ID of the lease (e.g. "*1F").
     * @return ResponseCollection
     */
    public function makeLeaseStatic(string $id): ResponseCollection
    {
        $query = Query::make('/ip/dhcp-server/lease/make-static')
            ->equal('.id', $id);
        return $this->client->query($query);
    }

    /**
     * Remove a lease.
     *
     * @param string $id RouterOS internal ID of the lease.
     * @return ResponseCollection
     */
    public function removeLease(string $id): ResponseCollection
    {
        $query = Query::make('/ip/dhcp-server/lease/remove')
            ->equal('.id', $id);
        return $this->client->query($query);
    }
}
