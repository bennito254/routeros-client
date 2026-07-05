<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Tests\Unit;

use Bennito254\RouterOS\Client\Client;
use Bennito254\RouterOS\Facade\Bridge;
use Bennito254\RouterOS\Facade\Dhcp;
use Bennito254\RouterOS\Facade\Dns;
use Bennito254\RouterOS\Facade\Firewall;
use Bennito254\RouterOS\Facade\Hotspot;
use Bennito254\RouterOS\Facade\IpPool;
use Bennito254\RouterOS\Facade\Pppoe;
use Bennito254\RouterOS\Facade\Queue;
use Bennito254\RouterOS\Facade\RouterInterface;
use Bennito254\RouterOS\Facade\RouterUser;
use Bennito254\RouterOS\Facade\Radius;
use Bennito254\RouterOS\Facade\SessionMonitor;
use Bennito254\RouterOS\Facade\System;
use Bennito254\RouterOS\Facade\UsageTracker;
use Bennito254\RouterOS\Facade\Vpn;
use Bennito254\RouterOS\Facade\Wireless;
use Bennito254\RouterOS\Query\Query;
use Bennito254\RouterOS\Response\ResponseCollection;
use PHPUnit\Framework\TestCase;

class FacadeTest extends TestCase
{
    private function getMockClient(string $expectedCommand, ResponseCollection $responseToReturn): Client
    {
        $client = $this->createMock(Client::class);
        $client->method('query')
            ->with($this->callback(function ($query) use ($expectedCommand) {
                if (is_string($query)) {
                    return $query === $expectedCommand;
                }
                if ($query instanceof Query) {
                    return str_contains($query->getCommand(), $expectedCommand);
                }
                return false;
            }))
            ->willReturn($responseToReturn);

        return $client;
    }

    public function testPppoeFacade(): void
    {
        $mockResponse = new ResponseCollection();
        $client = $this->getMockClient('/interface/pppoe-server/server', $mockResponse);
        $facade = new Pppoe($client);

        $this->assertSame($mockResponse, $facade->getServers());
    }

    public function testHotspotFacade(): void
    {
        $mockResponse = new ResponseCollection();
        $client = $this->getMockClient('/ip/hotspot', $mockResponse);
        $facade = new Hotspot($client);

        $this->assertSame($mockResponse, $facade->getServers());
    }

    public function testQueueFacade(): void
    {
        $mockResponse = new ResponseCollection();
        $client = $this->getMockClient('/queue/simple', $mockResponse);
        $facade = new Queue($client);

        $this->assertSame($mockResponse, $facade->getAll());
    }

    public function testFirewallFacade(): void
    {
        $mockResponse = new ResponseCollection();
        $client = $this->getMockClient('/ip/firewall/filter', $mockResponse);
        $facade = new Firewall($client);

        $this->assertSame($mockResponse, $facade->getRules());
    }

    public function testSystemFacade(): void
    {
        $mockResponse = new ResponseCollection();
        $client = $this->getMockClient('/system/resource', $mockResponse);
        $facade = new System($client);

        $this->assertSame($mockResponse, $facade->getResources());
    }

    public function testRouterInterfaceFacade(): void
    {
        $mockResponse = new ResponseCollection();
        $client = $this->getMockClient('/interface', $mockResponse);
        $facade = new RouterInterface($client);

        $this->assertSame($mockResponse, $facade->getInterfaces());
    }

    public function testDhcpFacade(): void
    {
        $mockResponse = new ResponseCollection();
        $client = $this->getMockClient('/ip/dhcp-server', $mockResponse);
        $facade = new Dhcp($client);

        $this->assertSame($mockResponse, $facade->getServers());
    }

    public function testWirelessFacade(): void
    {
        $mockResponse = new ResponseCollection();
        $client = $this->getMockClient('/interface/wireless', $mockResponse);
        $facade = new Wireless($client);

        $this->assertSame($mockResponse, $facade->getInterfaces());
    }

    public function testIpPoolFacade(): void
    {
        $mockResponse = new ResponseCollection();
        $client = $this->getMockClient('/ip/pool', $mockResponse);
        $facade = new IpPool($client);

        $this->assertSame($mockResponse, $facade->getPools());
    }

    public function testRouterUserFacade(): void
    {
        $mockResponse = new ResponseCollection();
        $client = $this->getMockClient('/user', $mockResponse);
        $facade = new RouterUser($client);

        $this->assertSame($mockResponse, $facade->getUsers());
    }

    public function testVpnFacade(): void
    {
        $mockResponse = new ResponseCollection();
        $client = $this->getMockClient('/ppp/secret', $mockResponse);
        $facade = new Vpn($client);

        $this->assertSame($mockResponse, $facade->getSecrets());
    }

    public function testBridgeFacade(): void
    {
        $mockResponse = new ResponseCollection();
        $client = $this->getMockClient('/interface/bridge', $mockResponse);
        $facade = new Bridge($client);

        $this->assertSame($mockResponse, $facade->getBridges());
    }

    public function testDnsFacade(): void
    {
        $mockResponse = new ResponseCollection();
        $client = $this->getMockClient('/ip/dns/static', $mockResponse);
        $facade = new Dns($client);

        $this->assertSame($mockResponse, $facade->getStaticRecords());
    }

    public function testUsageTrackerFacade(): void
    {
        $mockResponse = new ResponseCollection();
        $client = $this->getMockClient('/interface', $mockResponse);
        $facade = new UsageTracker($client);

        $this->assertSame($mockResponse, $facade->getInterfaceStats());
    }

    public function testSessionMonitorFacade(): void
    {
        $mockResponse = new ResponseCollection();
        $client = $this->getMockClient('/ip/hotspot/active', $mockResponse);
        $facade = new SessionMonitor($client);

        $this->assertSame($mockResponse, $facade->getHotspotSessions());
    }

    public function testRadiusFacade(): void
    {
        $mockResponse = new ResponseCollection();
        $client = $this->getMockClient('/radius', $mockResponse);
        $facade = new Radius($client);

        $this->assertSame($mockResponse, $facade->getServers());
    }

    public function testFacadeChunking(): void
    {
        // 1. Setup client that returns raw IDs first
        $client = $this->createMock(Client::class);
        
        $sentence1 = new \Bennito254\RouterOS\Response\ResponseSentence('!re', ['.id' => '*1']);
        $sentence2 = new \Bennito254\RouterOS\Response\ResponseSentence('!re', ['.id' => '*2']);
        $sentence3 = new \Bennito254\RouterOS\Response\ResponseSentence('!re', ['.id' => '*3']);
        
        $idsResponse = new ResponseCollection([$sentence1, $sentence2, $sentence3]);

        $chunk1Response = new ResponseCollection([$sentence1, $sentence2]);
        $chunk2Response = new ResponseCollection([$sentence3]);

        $client->expects($this->exactly(3))
            ->method('query')
            ->willReturnCallback(function ($query) use ($idsResponse, $chunk1Response, $chunk2Response) {
                $words = $query->getWords();
                // If it's querying for ID list (.proplist=.id)
                if (in_array('=.proplist=.id', $words, true)) {
                    return $idsResponse;
                }
                
                // If it's chunk 1 (contains *1 or *2)
                if (in_array('?.id=*1', $words, true)) {
                    return $chunk1Response;
                }
                
                // If it's chunk 2 (contains *3)
                if (in_array('?.id=*3', $words, true)) {
                    return $chunk2Response;
                }

                return new ResponseCollection();
            });

        $facade = new Bridge($client);

        $chunksReceived = [];
        $facade->chunk(2, function (ResponseCollection $chunk) use (&$chunksReceived) {
            $chunksReceived[] = $chunk;
        });

        $this->assertCount(2, $chunksReceived);
        $this->assertSame($chunk1Response, $chunksReceived[0]);
        $this->assertSame($chunk2Response, $chunksReceived[1]);
    }
}

