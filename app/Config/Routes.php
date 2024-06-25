<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/ping', 'PingController::ping');

$routes->get('/docs', 'DocsController::docs');
$routes->get('/docs-json', 'DocsController::docs-json');

$routes->post('/transfer', 'TransferController::transfer');