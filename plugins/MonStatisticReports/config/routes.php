<?php
use Cake\Routing\Router;

Router::scope('/MonStatisticReports', ['plugin' => 'MonStatisticReports'], function ($routes) {
    $routes->scope('/', ['controller' => 'MonStatisticReports'], function ($routes) {
        $routes->connect( '/');
        $routes->connect( '/:action/*');
    });
});
