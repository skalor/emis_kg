<?php
use Cake\Routing\Router;

Router::scope('/MonGeneratedStatisticReports', ['plugin' => 'MonGeneratedStatisticReports'], function ($routes) {
    $routes->scope('/', ['controller' => 'MonGeneratedStatisticReports'], function ($routes) {
        $routes->connect( '/');
        $routes->connect( '/:action/*');
    });
});
