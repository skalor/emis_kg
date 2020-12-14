<?php
use Cake\Routing\Router;

Router::scope('/MonTemplateReports', ['plugin' => 'MonTemplateReports'], function ($routes) {
    $routes->scope('/', ['controller' => 'MonTemplateReports'], function ($routes) {
        $routes->connect( '/');
        $routes->connect( '/:action/*');
    });
});
