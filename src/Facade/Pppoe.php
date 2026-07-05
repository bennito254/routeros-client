<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Facade;

use Bennito254\RouterOS\Query\Query;
use Bennito254\RouterOS\Response\ResponseCollection;

/**
 * Facade for managing PPPoE Server configurations on RouterOS.
 *
 * API Path: /interface/pppoe-server/server
 */
class Pppoe extends AbstractFacade
{
    protected function getBaseCommand(): string
    {
        return '/interface/pppoe-server/server';
    }

    /**
     * Get list of all PPPoE server instances.
     *
     * @return ResponseCollection
     */
    public function getServers(): ResponseCollection
    {
        return $this->getAll();
    }

    /**
     * Add a new PPPoE server instance.
     *
     * @param string $serviceName Name of the PPPoE service.
     * @param string $interface Name of the interface to bind to.
     * @param string $defaultProfile PPP Profile name to use.
     * @param array $extra Additional attributes.
     * @return ResponseCollection
     */
    public function addServer(string $serviceName, string $interface, string $defaultProfile = 'default', array $extra = []): ResponseCollection
    {
        $data = array_merge([
            'service-name' => $serviceName,
            'interface' => $interface,
            'default-profile' => $defaultProfile,
        ], $extra);

        return $this->add($data);
    }
}
