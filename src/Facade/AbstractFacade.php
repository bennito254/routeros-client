<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Facade;

use Bennito254\RouterOS\Client\Client;
use Bennito254\RouterOS\Query\Query;
use Bennito254\RouterOS\Response\ResponseCollection;
use Bennito254\RouterOS\Response\ResponseSentence;

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

    /**
     * Retrieve items in chunks to avoid memory exhaustion and router CPU spikes.
     *
     * @param int $size Number of items per chunk.
     * @param callable $callback Callback function receiving a ResponseCollection. Return false to stop.
     * @param array $proplist Optional specific fields to retrieve (to save memory/bandwidth).
     */
    public function chunk(int $size, callable $callback, array $proplist = []): void
    {
        // 1. Get all IDs to chunk by
        $query = Query::make($this->getBaseCommand() . '/print')
            ->equal('.proplist', '.id');

        $idsResponse = $this->client->query($query);
        $ids = [];
        foreach ($idsResponse->getSentences() as $sentence) {
            $id = $sentence->getAttribute('.id');
            if ($id !== null) {
                $ids[] = $id;
            }
        }

        if (empty($ids)) {
            return;
        }

        // 2. Chunk the IDs and query full details for each chunk
        $chunks = array_chunk($ids, $size);
        foreach ($chunks as $chunkIds) {
            $chunkQuery = Query::make($this->getBaseCommand() . '/print');

            if (!empty($proplist)) {
                $chunkQuery->equal('.proplist', implode(',', $proplist));
            }

            // Build query filter: .id=*1 OR .id=*2 OR ...
            foreach ($chunkIds as $index => $id) {
                $chunkQuery->where('.id', $id);
                if ($index > 0) {
                    $chunkQuery->whereOr();
                }
            }

            $response = $this->client->query($chunkQuery);
            if ($callback($response) === false) {
                break;
            }
        }
    }
}

