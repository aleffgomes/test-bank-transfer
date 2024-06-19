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

  $routesApiDirectory = APPPATH . 'Routes/V1';
  $routeFilesApi = scandir($routesApiDirectory);
  
  $routes->group('api/v1', function ($routes) use ($routesApiDirectory) {
      $routeFilesApi = scandir($routesApiDirectory);
      foreach ($routeFilesApi as $file) {
          if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
              require $routesApiDirectory . $file;
          }
      }
  });