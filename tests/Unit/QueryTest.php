<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Tests\Unit;

use Bennito254\RouterOS\Query\Query;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    public function testQueryMake(): void
    {
        $query = Query::make('/ip/address/print');
        $this->assertSame('/ip/address/print', $query->getCommand());
    }

    public function testQueryBuilding(): void
    {
        $query = Query::make('/ip/address/print')
            ->where('interface', 'ether1')
            ->equal('disabled', false)
            ->tag('mytag');

        $expected = [
            '/ip/address/print',
            '=disabled=false',
            '?interface=ether1',
            '.tag=mytag'
        ];

        $this->assertSame($expected, $query->getWords());
    }
}
