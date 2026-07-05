<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Facade;

use Bennito254\RouterOS\Query\Query;
use Bennito254\RouterOS\Response\ResponseCollection;

/**
 * Facade for managing IP Firewall filter rules, NAT, and address lists on RouterOS.
 *
 * API Path: /ip/firewall/filter
 */
class Firewall extends AbstractFacade
{
    protected function getBaseCommand(): string
    {
        return '/ip/firewall/filter';
    }

    /**
     * Get all filter rules.
     *
     * @return ResponseCollection
     */
    public function getRules(): ResponseCollection
    {
        return $this->getAll();
    }

    /**
     * Add a firewall filter rule.
     *
     * @param string $chain Chain to match (e.g. input, forward, output).
     * @param string $action Action to take (e.g. accept, drop, reject).
     * @param array $extra Additional criteria (e.g. src-address, dst-port, protocol).
     * @return ResponseCollection
     */
    public function addFilterRule(string $chain, string $action, array $extra = []): ResponseCollection
    {
        $data = array_merge([
            'chain' => $chain,
            'action' => $action,
        ], $extra);

        return $this->add($data);
    }

    /**
     * Get all NAT rules.
     *
     * @return ResponseCollection
     */
    public function getNatRules(): ResponseCollection
    {
        $query = Query::make('/ip/firewall/nat/print');
        return $this->client->query($query);
    }

    /**
     * Add a NAT rule.
     *
     * @param string $chain NAT Chain (e.g. srcnat, dstnat).
     * @param string $action Action to take (e.g. masquerade, dst-nat, src-nat).
     * @param array $extra Additional attributes.
     * @return ResponseCollection
     */
    public function addNatRule(string $chain, string $action, array $extra = []): ResponseCollection
    {
        $query = Query::make('/ip/firewall/nat/add')
            ->equal('chain', $chain)
            ->equal('action', $action);
        foreach ($extra as $key => $value) {
            $query->equal($key, $value);
        }
        return $this->client->query($query);
    }

    /**
     * Get all mangle rules.
     *
     * @return ResponseCollection
     */
    public function getMangleRules(): ResponseCollection
    {
        $query = Query::make('/ip/firewall/mangle/print');
        return $this->client->query($query);
    }

    /**
     * Add a mangle rule.
     *
     * @param string $chain Mangle Chain (e.g. prerouting, postrouting, forward).
     * @param string $action Action (e.g. mark-routing, mark-packet, mark-connection).
     * @param array $extra Additional parameters.
     * @return ResponseCollection
     */
    public function addMangleRule(string $chain, string $action, array $extra = []): ResponseCollection
    {
        $query = Query::make('/ip/firewall/mangle/add')
            ->equal('chain', $chain)
            ->equal('action', $action);
        foreach ($extra as $key => $value) {
            $query->equal($key, $value);
        }
        return $this->client->query($query);
    }

    /**
     * Get all firewall address lists.
     *
     * @return ResponseCollection
     */
    public function getAddressLists(): ResponseCollection
    {
        $query = Query::make('/ip/firewall/address-list/print');
        return $this->client->query($query);
    }

    /**
     * Add an IP address to a firewall address list.
     *
     * @param string $list Name of the address list.
     * @param string $address IP address or subnet range.
     * @param string|null $timeout Optional timeout duration (e.g. "1d", "01:00:00").
     * @return ResponseCollection
     */
    public function addToAddressList(string $list, string $address, ?string $timeout = null): ResponseCollection
    {
        $query = Query::make('/ip/firewall/address-list/add')
            ->equal('list', $list)
            ->equal('address', $address);
        if ($timeout !== null) {
            $query->equal('timeout', $timeout);
        }
        return $this->client->query($query);
    }
}
