<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Facade;

class HotspotUser extends AbstractFacade
{
    protected function getBaseCommand(): string
    {
        return '/ip/hotspot/user';
    }
}
