<?php

declare(strict_types=1);

namespace App\Filters;

use App\Libraries\Hub\HubClient;
use Config\Services;
use dcardenasl\Ci4ApiCore\Http\Filters\AbstractJwtAuthFilter;
use stdClass;

/**
 * Opt-in JWT auth filter for aggregator endpoints that need the user context.
 *
 * The BFF is forward-only by default — most routes never crack the token.
 * Routes that *need* `auth_user_id` / `auth_permissions` (e.g. dashboards that
 * fan out to several services and tag each result with the user) opt in by
 * attaching this filter:
 *
 *     $routes->get('me/dashboard', 'Me\DashboardController::index', [
 *         'filter' => 'introspectauth',
 *     ]);
 *
 * Token validation is delegated to the hub via {@see HubClient::introspect()},
 * which caches positive results. The BFF therefore never holds the JWT secret
 * and remains stateless.
 */
class IntrospectAuthFilter extends AbstractJwtAuthFilter
{
    protected function decodeToken(string $token): ?object
    {
        $result = $this->hubClient()->introspect($token);

        if (! $result->valid) {
            return null;
        }

        // Adapt IntrospectResult to the shape AbstractJwtAuthFilter expects:
        // an object exposing `uid` (int), `scope` (list<string>), and `jti`.
        $decoded              = new stdClass();
        $decoded->uid         = $result->uid ?? 0;
        $decoded->scope       = $result->permissions;
        $decoded->jti         = null;

        return $decoded;
    }

    private function hubClient(): HubClient
    {
        return Services::hubClient();
    }
}
