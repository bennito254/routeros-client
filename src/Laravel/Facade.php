<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Laravel;

use Illuminate\Support\Facades\Facade as IlluminateFacade;

/**
 * @method static \Bennito254\RouterOS\Response\ResponseCollection query(\Bennito254\RouterOS\Query\Query|string $query)
 * @method static void connect()
 * @method static void disconnect()
 * @method static bool isConnected()
 * 
 * @see \Bennito254\RouterOS\Client\Client
 */
class Facade extends IlluminateFacade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'routeros';
    }
}
