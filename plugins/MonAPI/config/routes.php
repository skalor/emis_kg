<?php
use Cake\Routing\Router;

Router::scope('/MonAPI', ['plugin' => 'MonAPI'], function ($routes) {
    $routes->scope('/', ['controller' => 'Restful'], function ($routes) {
        $routes->extensions(['json', 'xml']);
        $routes->connect( '/', ['action' => 'token', '_method' => 'GET']);
        $routes->connect( '/:action/*');
    });

    $routes->scope('/restful', ['controller' => 'Restful'], function ($routes) {
        $routes->extensions(['json', 'xml']);
        $routes->connect( '/', ['action' => 'token', '_method' => 'GET']);
        $routes->connect( '/:model', ['action' => 'index', '_method' => 'GET']);
        $routes->connect( '/:model/:id', ['action' => 'view', '_method' => 'GET'], ['pass' => ['id']]);
    });

    $routes->scope('/ApiAccessControl', ['controller' => 'MonApi'], function ($routes) {
        $routes->connect( '/');
        $routes->connect( '/:action/*');
    });
});
