<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Facade;

use Bennito254\RouterOS\Query\Query;
use Bennito254\RouterOS\Response\ResponseCollection;

/**
 * Facade for monitoring and managing active network sessions (Hotspot, PPP/VPN) on RouterOS.
 *
 * API Path: /ip/hotspot/active
 */
class SessionMonitor extends AbstractFacade
{
    protected function getBaseCommand(): string
    {
        return '/ip/hotspot/active';
    }

    /**
     * Get list of all active Hotspot user sessions.
     *
     * @return ResponseCollection
     */
    public function getHotspotSessions(): ResponseCollection
    {
        return $this->getAll();
    }

    /**
     * Get list of all active PPP/VPN sessions.
     *
     * @return ResponseCollection
     */
    public function getPppSessions(): ResponseCollection
    {
        $query = Query::make('/ppp/active/print');
        return $this->client->query($query);
    }

    /**
     * Terminate/force disconnect an active Hotspot user session.
     *
     * @param string $id Active Hotspot session internal ID.
     * @return ResponseCollection
     */
    public function removeHotspotSession(string $id): ResponseCollection
    {
        return $this->remove($id);
    }

    /**
     * Terminate/force disconnect an active PPP/VPN session.
     *
     * @param string $id Active PPP session internal ID.
     * @return ResponseCollection
     */
    public function removePppSession(string $id): ResponseCollection
    {
        $query = Query::make('/ppp/active/remove')
            ->equal('.id', $id);
        return $this->client->query($query);
    }
}
