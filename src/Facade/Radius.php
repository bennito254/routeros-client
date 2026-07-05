<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Facade;

use Bennito254\RouterOS\Query\Query;
use Bennito254\RouterOS\Response\ResponseCollection;

/**
 * Facade for managing RADIUS client configuration on RouterOS.
 *
 * API Path: /radius
 */
class Radius extends AbstractFacade
{
    protected function getBaseCommand(): string
    {
        return '/radius';
    }

    /**
     * Get list of configured RADIUS servers.
     *
     * @return ResponseCollection
     */
    public function getServers(): ResponseCollection
    {
        return $this->getAll();
    }

    /**
     * Add a new RADIUS server configuration.
     *
     * @param string $address IP address of the RADIUS server.
     * @param string $secret Shared secret key.
     * @param array $services Array of services to enable (e.g., ['ppp', 'hotspot', 'login']).
     * @param array $extra Additional attributes (e.g., ports, timeout).
     * @return ResponseCollection
     */
    public function addServer(string $address, string $secret, array $services = ['ppp'], array $extra = []): ResponseCollection
    {
        $data = array_merge([
            'address' => $address,
            'secret' => $secret,
        ], $extra);

        // In RouterOS, services are list of enabled service flags (e.g., service=ppp,hotspot)
        $data['service'] = implode(',', $services);

        return $this->add($data);
    }
}
