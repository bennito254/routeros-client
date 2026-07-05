<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Facade;

use Bennito254\RouterOS\Query\Query;
use Bennito254\RouterOS\Response\ResponseCollection;

/**
 * Facade for managing bridge interfaces and ports on RouterOS.
 *
 * API Path: /interface/bridge
 */
class Bridge extends AbstractFacade
{
    protected function getBaseCommand(): string
    {
        return '/interface/bridge';
    }

    /**
     * Get list of all bridges.
     *
     * @return ResponseCollection
     */
    public function getBridges(): ResponseCollection
    {
        return $this->getAll();
    }

    /**
     * Add a new bridge.
     *
     * @param string $name Name of the bridge.
     * @param array $extra Additional attributes.
     * @return ResponseCollection
     */
    public function addBridge(string $name, array $extra = []): ResponseCollection
    {
        $data = array_merge([
            'name' => $name,
        ], $extra);

        return $this->add($data);
    }

    /**
     * Add an interface port to a bridge.
     *
     * @param string $bridge Bridge interface name.
     * @param string $interface Port interface name.
     * @param array $extra Additional parameters.
     * @return ResponseCollection
     */
    public function addPort(string $bridge, string $interface, array $extra = []): ResponseCollection
    {
        $query = Query::make('/interface/bridge/port/add')
            ->equal('bridge', $bridge)
            ->equal('interface', $interface);
        foreach ($extra as $key => $value) {
            $query->equal($key, $value);
        }
        return $this->client->query($query);
    }

    /**
     * Get list of bridge port associations.
     *
     * @return ResponseCollection
     */
    public function getPorts(): ResponseCollection
    {
        $query = Query::make('/interface/bridge/port/print');
        return $this->client->query($query);
    }

    /**
     * Remove a port association from a bridge.
     *
     * @param string $id Port assignment internal ID (e.g. "*1F").
     * @return ResponseCollection
     */
    public function removePort(string $id): ResponseCollection
    {
        $query = Query::make('/interface/bridge/port/remove')
            ->equal('.id', $id);
        return $this->client->query($query);
    }
}
