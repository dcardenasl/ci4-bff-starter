<?php

/** @var \CodeIgniter\Router\RouteCollection $routes */

$routes->get('users/(:num)', '\App\Controllers\Api\V1\Users\UsersProxyController::show/$1');
