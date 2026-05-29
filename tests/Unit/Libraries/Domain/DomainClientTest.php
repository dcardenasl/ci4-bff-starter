<?php

declare(strict_types=1);

namespace Tests\Unit\Libraries\Domain;

use App\Libraries\Domain\DomainClient;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use InvalidArgumentException;

class DomainClientTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Reset services instances to prevent tests leakage
        Services::reset();
    }

    public function testDomainClientInstantiation(): void
    {
        $http = $this->createMock(CURLRequest::class);
        $client = new DomainClient($http, 'http://catalog-domain.test', 10);

        $this->assertInstanceOf(DomainClient::class, $client);
    }

    public function testServicesDomainClientThrowsOnUnconfiguredDomain(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Upstream domain URL for 'unconfigured' is not defined in Config\Bff::\$domains.");

        Services::domainClient('unconfigured');
    }

    public function testServicesDomainClientReturnsConfiguredClient(): void
    {
        $bffConfig = config('Bff');
        $bffConfig->domains = [
            'billing' => 'http://billing-domain.test',
        ];

        $client = Services::domainClient('billing', false);

        $this->assertInstanceOf(DomainClient::class, $client);
    }

    public function testServicesDomainClientReturnsSharedInstanceByDefault(): void
    {
        $bffConfig = config('Bff');
        $bffConfig->domains = [
            'billing' => 'http://billing-domain.test',
        ];

        $client1 = Services::domainClient('billing');
        $client2 = Services::domainClient('billing');

        $this->assertSame($client1, $client2);
    }
}
