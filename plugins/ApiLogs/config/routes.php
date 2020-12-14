<?php
use Cake\Routing\Router;

Router::scope('/ApiLogs', ['plugin' => 'ApiLogs'], function ($routes) {
    $routes->scope('/log', ['controller' => 'ApiLogs'], function ($route) {
        $route->connect(
            '/',
            ['action' => 'index']
        );

        $route->connect(
            '/:action/*',
            [],
            ['action' => '[a-zA-Z]+']
        );
    });
});
