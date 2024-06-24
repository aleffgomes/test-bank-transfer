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
  $routes->get('/', 'PingController::ping');
  $routes->post('/transfer', 'TransferController::transfer');