<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Facade;

class PppActive extends AbstractFacade
{
    protected function getBaseCommand(): string
    {
        return '/ppp/active';
    }
}
