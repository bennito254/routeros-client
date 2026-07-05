<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Facade;

use Bennito254\RouterOS\Query\Query;
use Bennito254\RouterOS\Response\ResponseCollection;

/**
 * Facade for managing Hotspot server instances on RouterOS.
 *
 * API Path: /ip/hotspot
 */
class Hotspot extends AbstractFacade
{
    protected function getBaseCommand(): string
    {
        return '/ip/hotspot';
    }

    /**
     * Get list of all Hotspot server instances.
     *
     * @return ResponseCollection
     */
    public function getServers(): ResponseCollection
    {
        return $this->getAll();
    }

    /**
     * Add a new Hotspot server.
     *
     * @param string $name Name of the Hotspot server.
     * @param string $interface Interface name to bind the Hotspot to.
     * @param string $profile Hotspot Profile name.
     * @param array $extra Additional attributes.
     * @return ResponseCollection
     */
    public function addServer(string $name, string $interface, string $profile = 'default', array $extra = []): ResponseCollection
    {
        $data = array_merge([
            'name' => $name,
            'interface' => $interface,
            'profile' => $profile,
        ], $extra);

        return $this->add($data);
    }
}
