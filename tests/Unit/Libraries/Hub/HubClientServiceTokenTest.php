<?php

declare(strict_types=1);

namespace Tests\Unit\Libraries\Hub;

use App\Libraries\Hub\HubClient;
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Hub as HubConfig;

class HubClientServiceTokenTest extends CIUnitTestCase
{
    private function makeConfig(int $safetyMargin = 30): HubConfig
    {
        $config                           = new HubConfig();
        $config->url                      = 'http://hub.test';
        $config->apiKey                   = 'test-key';
        $config->appCode                  = 'test-app';
        $config->serviceTokenSafetyMargin = $safetyMargin;
        $config->httpTimeout              = 5;

        return $config;
    }

    public function testReturnsCachedTokenWhenWellWithinExpiry(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturn([
            'access_token' => 'cached-token',
            'expires_at'   => time() + 3600,
        ]);

        $http = $this->createMock(CURLRequest::class);
        $http->expects($this->never())->method('post');

        $client = new HubClient($this->makeConfig(), $http, $cache);

        $this->assertSame('cached-token', $client->getServiceToken());
    }

    public function testRefreshesWhenCachedTokenIsCloseToExpiry(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturn([
            'access_token' => 'about-to-expire',
            'expires_at'   => time() + 10, // < safetyMargin of 30
        ]);
        $cache->expects($this->once())->method('save');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn(json_encode([
            'data' => ['access_token' => 'fresh-token', 'expires_in' => 3600],
        ]) ?: '');

        $http = $this->createMock(CURLRequest::class);
        $http->expects($this->once())
            ->method('post')
            ->with($this->stringContains('/api/v1/auth/service-token'))
            ->willReturn($response);

        $client = new HubClient($this->makeConfig(30), $http, $cache);

        $this->assertSame('fresh-token', $client->getServiceToken());
    }

    public function testRefreshesWhenNothingCached(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturn(null);
        $cache->expects($this->once())->method('save');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn(json_encode([
            'data' => ['access_token' => 'first-token', 'expires_in' => 1800],
        ]) ?: '');

        $http = $this->createMock(CURLRequest::class);
        $http->expects($this->once())->method('post')->willReturn($response);

        $client = new HubClient($this->makeConfig(), $http, $cache);

        $this->assertSame('first-token', $client->getServiceToken());
    }

    public function testThrowsOnNon200(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturn(null);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(500);
        $response->method('getBody')->willReturn('upstream broken');

        $http = $this->createMock(CURLRequest::class);
        $http->method('post')->willReturn($response);

        $client = new HubClient($this->makeConfig(), $http, $cache);

        $this->expectException(\RuntimeException::class);
        $client->getServiceToken();
    }

    public function testThrowsOnMalformedPayload(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturn(null);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn('{"data":{}}');

        $http = $this->createMock(CURLRequest::class);
        $http->method('post')->willReturn($response);

        $client = new HubClient($this->makeConfig(), $http, $cache);

        $this->expectException(\RuntimeException::class);
        $client->getServiceToken();
    }
}
