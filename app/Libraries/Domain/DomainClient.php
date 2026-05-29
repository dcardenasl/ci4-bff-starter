<?php

declare(strict_types=1);

namespace App\Libraries\Domain;

use CodeIgniter\HTTP\CURLRequest;
use dcardenasl\Ci4ApiCore\Http\Client\AbstractServiceClient;

/**
 * Generic HTTP client for upstream domain apps (e.g., ci4-domain-starter apps).
 *
 * Inherits request routing, automatic linear retries on 5xx or network errors,
 * X-Request-Id distributed tracing propagation, and status code-to-exception mapping
 * from the core's {@see AbstractServiceClient}.
 */
class DomainClient extends AbstractServiceClient
{
    public function __construct(
        CURLRequest $http,
        string $baseUrl,
        int $timeoutSeconds = 5
    ) {
        parent::__construct(
            http: $http,
            baseUrl: $baseUrl,
            timeoutSeconds: $timeoutSeconds
        );
    }
}
