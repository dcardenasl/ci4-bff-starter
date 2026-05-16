<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseService;

require_once __DIR__ . '/ApiCoreServices.php';

/**
 * Services Configuration file.
 *
 * BFF starter: stateless gateway. No DB, no audit chain, no domain factories.
 * Only HubClient and the ci4-api-core HTTP/DTO helpers via ApiCoreServices.
 */
class Services extends BaseService
{
    use ApiCoreServices;

    public static function hubClient(bool $getShared = true): \App\Libraries\Hub\HubClient
    {
        if ($getShared) {
            return static::getSharedInstance('hubClient');
        }

        return new \App\Libraries\Hub\HubClient(
            config('Hub'),
            \Config\Services::curlrequest(),
            \Config\Services::cache()
        );
    }

    /**
     * The Request Service
     *
     * @param \Config\App|bool $getShared
     */
    public static function request($getShared = true): \dcardenasl\Ci4ApiCore\Http\ApiRequest
    {
        if (is_bool($getShared) && $getShared) {
            return static::getSharedInstance('request');
        }

        $config = $getShared instanceof \Config\App ? $getShared : config('App');

        return new \dcardenasl\Ci4ApiCore\Http\ApiRequest(
            $config,
            static::uri(),
            'php://input',
            new \CodeIgniter\HTTP\UserAgent()
        );
    }
}
