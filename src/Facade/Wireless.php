<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Facade;

use Bennito254\RouterOS\Query\Query;
use Bennito254\RouterOS\Response\ResponseCollection;

/**
 * Facade for managing Wireless interfaces and monitoring client associations on RouterOS.
 *
 * API Path: /interface/wireless
 */
class Wireless extends AbstractFacade
{
    protected function getBaseCommand(): string
    {
        return '/interface/wireless';
    }

    /**
     * Get list of all wireless interfaces.
     *
     * @return ResponseCollection
     */
    public function getInterfaces(): ResponseCollection
    {
        return $this->getAll();
    }

    /**
     * Get list of currently connected wireless clients.
     *
     * @return ResponseCollection
     */
    public function getRegistrationTable(): ResponseCollection
    {
        $query = Query::make('/interface/wireless/registration-table/print');
        return $this->client->query($query);
    }
}
