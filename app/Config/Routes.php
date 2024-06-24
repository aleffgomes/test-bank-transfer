<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

/*
  * --------------------------------------------------------------------
  * V1
  * --------------------------------------------------------------------
  */

  $routes->get('/ping', 'PingController::ping');
  $routes->post('/transfer', 'TransferController::transfer');
  $routes->get('/docs', 'DocsController::docs');
  $routes->get('/docs-json', 'DocsController::docs-json');
