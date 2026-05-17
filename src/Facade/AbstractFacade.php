<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Facade;

use Bennito254\RouterOS\Client\Client;
use Bennito254\RouterOS\Query\Query;
use Bennito254\RouterOS\Response\ResponseCollection;

abstract class AbstractFacade
{
    protected Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get the base command for this facade (e.g., '/ip/address').
     */
    abstract protected function getBaseCommand(): string;

    /**
     * Get all items.
     */
    public function getAll(): ResponseCollection
    {
        $query = Query::make($this->getBaseCommand() . '/print');
        return $this->client->query($query);
    }

    /**
     * Get a specific item by its .id.
     */
    public function get(string $id): ResponseCollection
    {
        $query = Query::make($this->getBaseCommand() . '/print')
            ->where('.id', $id);
        return $this->client->query($query);
    }

    /**
     * Add a new item.
     */
    public function add(array $data): ResponseCollection
    {
        $query = Query::make($this->getBaseCommand() . '/add');
        foreach ($data as $key => $value) {
            $query->equal($key, $value);
        }
        return $this->client->query($query);
    }

    /**
     * Update an existing item by its .id.
     */
    public function set(string $id, array $data): ResponseCollection
    {
        $query = Query::make($this->getBaseCommand() . '/set')
            ->equal('.id', $id);
        foreach ($data as $key => $value) {
            $query->equal($key, $value);
        }
        return $this->client->query($query);
    }

    /**
     * Remove an item by its .id.
     */
    public function remove(string $id): ResponseCollection
    {
        $query = Query::make($this->getBaseCommand() . '/remove')
            ->equal('.id', $id);
        return $this->client->query($query);
    }

    /**
     * Enable an item by its .id.
     */
    public function enable(string $id): ResponseCollection
    {
        $query = Query::make($this->getBaseCommand() . '/enable')
            ->equal('.id', $id);
        return $this->client->query($query);
    }

    /**
     * Disable an item by its .id.
     */
    public function disable(string $id): ResponseCollection
    {
        $query = Query::make($this->getBaseCommand() . '/disable')
            ->equal('.id', $id);
        return $this->client->query($query);
    }
}
