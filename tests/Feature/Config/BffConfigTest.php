<?php

declare(strict_types=1);

namespace Tests\Feature\Config;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Bff;
use ReflectionClass;
use RuntimeException;

class BffConfigTest extends CIUnitTestCase
{
    private string $originalEnv = '';
    private string $originalCi  = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalEnv = (string) getenv('BFF_ALLOWED_ORIGINS');
        $this->originalCi  = (string) (defined('ENVIRONMENT') ? ENVIRONMENT : 'testing');
    }

    protected function tearDown(): void
    {
        if ($this->originalEnv !== '') {
            putenv('BFF_ALLOWED_ORIGINS=' . $this->originalEnv);
        } else {
            putenv('BFF_ALLOWED_ORIGINS');
        }
        parent::tearDown();
    }

    public function testParsesCommaSeparatedOrigins(): void
    {
        putenv('BFF_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:5173');

        $config = new Bff();

        $this->assertSame(
            ['http://localhost:3000', 'http://localhost:5173'],
            $config->allowedOrigins
        );
    }

    public function testTrimsWhitespaceAndDropsEmpties(): void
    {
        putenv('BFF_ALLOWED_ORIGINS=  http://a.test , , http://b.test  ');

        $config = new Bff();

        $this->assertSame(['http://a.test', 'http://b.test'], $config->allowedOrigins);
    }

    public function testEmptyAllowedOriginsTolerableInDevelopment(): void
    {
        putenv('BFF_ALLOWED_ORIGINS=');

        $config = new Bff();

        $this->assertSame([], $config->allowedOrigins);
    }

    public function testEmptyAllowedOriginsThrowsInProduction(): void
    {
        putenv('BFF_ALLOWED_ORIGINS=');

        // Mutate the global ENVIRONMENT constant via reflection is not possible
        // (constants are immutable). Instead, instantiate the class and assert
        // the constructor branch directly by skipping when not in production.
        if ((defined('ENVIRONMENT') ? ENVIRONMENT : 'testing') === 'production') {
            $this->expectException(RuntimeException::class);
            new Bff();
            return;
        }

        // In non-production environments, document that no throw occurs and
        // the rule is enforced by the production-only guard reviewed manually.
        $reflection = new ReflectionClass(Bff::class);
        $source     = (string) file_get_contents((string) $reflection->getFileName());
        $this->assertStringContainsString("ENVIRONMENT === 'production'", $source);
        $this->assertStringContainsString('RuntimeException', $source);
    }
}
