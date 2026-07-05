<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Facade;

use Bennito254\RouterOS\Query\Query;
use Bennito254\RouterOS\Response\ResponseCollection;

/**
 * Facade for configuring system DNS settings and managing static DNS records on RouterOS.
 *
 * API Path: /ip/dns/static
 */
class Dns extends AbstractFacade
{
    protected function getBaseCommand(): string
    {
        return '/ip/dns/static';
    }

    /**
     * Get system DNS settings.
     *
     * @return ResponseCollection
     */
    public function getSettings(): ResponseCollection
    {
        $query = Query::make('/ip/dns/print');
        return $this->client->query($query);
    }

    /**
     * Set primary/secondary DNS servers.
     *
     * @param array|string $servers IP addresses of DNS servers (comma-separated or array).
     * @return ResponseCollection
     */
    public function setServers(array|string $servers): ResponseCollection
    {
        if (is_array($servers)) {
            $servers = implode(',', $servers);
        }

        $query = Query::make('/ip/dns/set')
            ->equal('servers', $servers);
        return $this->client->query($query);
    }

    /**
     * List all static DNS records.
     *
     * @return ResponseCollection
     */
    public function getStaticRecords(): ResponseCollection
    {
        return $this->getAll();
    }

    /**
     * Add a static DNS record.
     *
     * @param string $name Domain name (e.g. "example.local").
     * @param string $address Target IP address.
     * @param string $ttl TTL duration (e.g. "1d" or "01:00:00").
     * @param array $extra Additional attributes.
     * @return ResponseCollection
     */
    public function addStaticRecord(string $name, string $address, string $ttl = '1d', array $extra = []): ResponseCollection
    {
        $data = array_merge([
            'name' => $name,
            'address' => $address,
            'ttl' => $ttl,
        ], $extra);

        return $this->add($data);
    }
}
