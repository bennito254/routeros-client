<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Facade;

use Bennito254\RouterOS\Query\Query;
use Bennito254\RouterOS\Response\ResponseCollection;

/**
 * Facade for managing VPN/PPP users and controlling VPN server configurations (L2TP, OVPN, PPTP, SSTP).
 *
 * API Path: /ppp/secret
 */
class Vpn extends AbstractFacade
{
    protected function getBaseCommand(): string
    {
        return '/ppp/secret';
    }

    /**
     * Get list of all PPP secrets (VPN users).
     *
     * @return ResponseCollection
     */
    public function getSecrets(): ResponseCollection
    {
        return $this->getAll();
    }

    /**
     * Add a new VPN user account.
     *
     * @param string $name Login username.
     * @param string $password Login password.
     * @param string $service VPN service type (e.g. l2tp, pptp, ovpn, sstp, or any).
     * @param string $profile PPP Profile name.
     * @param array $extra Additional attributes.
     * @return ResponseCollection
     */
    public function addSecret(string $name, string $password, string $service = 'any', string $profile = 'default', array $extra = []): ResponseCollection
    {
        $data = array_merge([
            'name' => $name,
            'password' => $password,
            'service' => $service,
            'profile' => $profile,
        ], $extra);

        return $this->add($data);
    }

    /**
     * Get active PPP/VPN connections.
     *
     * @return ResponseCollection
     */
    public function getActiveConnections(): ResponseCollection
    {
        $query = Query::make('/ppp/active/print');
        return $this->client->query($query);
    }

    /**
     * Terminate/disconnect an active VPN connection.
     *
     * @param string $id RouterOS internal ID of the active session.
     * @return ResponseCollection
     */
    public function disconnectActiveConnection(string $id): ResponseCollection
    {
        $query = Query::make('/ppp/active/remove')
            ->equal('.id', $id);
        return $this->client->query($query);
    }

    /**
     * Toggle the status of the built-in OpenVPN (OVPN) Server.
     *
     * @param bool $enabled
     * @return ResponseCollection
     */
    public function enableOvpnServer(bool $enabled = true): ResponseCollection
    {
        $query = Query::make('/interface/ovpn-server/server/set')
            ->equal('enabled', $enabled ? 'true' : 'false');
        return $this->client->query($query);
    }

    /**
     * Toggle the status of the built-in L2TP Server.
     *
     * @param bool $enabled
     * @return ResponseCollection
     */
    public function enableL2tpServer(bool $enabled = true): ResponseCollection
    {
        $query = Query::make('/interface/l2tp-server/server/set')
            ->equal('enabled', $enabled ? 'true' : 'false');
        return $this->client->query($query);
    }

    /**
     * Toggle the status of the built-in PPTP Server.
     *
     * @param bool $enabled
     * @return ResponseCollection
     */
    public function enablePptpServer(bool $enabled = true): ResponseCollection
    {
        $query = Query::make('/interface/pptp-server/server/set')
            ->equal('enabled', $enabled ? 'true' : 'false');
        return $this->client->query($query);
    }

    /**
     * Toggle the status of the built-in SSTP Server.
     *
     * @param bool $enabled
     * @return ResponseCollection
     */
    public function enableSstpServer(bool $enabled = true): ResponseCollection
    {
        $query = Query::make('/interface/sstp-server/server/set')
            ->equal('enabled', $enabled ? 'true' : 'false');
        return $this->client->query($query);
    }
}
