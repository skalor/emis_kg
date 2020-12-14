<?php

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

Router::scope('/HiddenFields', ['plugin' => 'HiddenField'], function (RouteBuilder $routes) {
    $routes->connect('/', ['plugin' => 'HiddenField', 'controller' => 'HiddenFields']);
    $routes->connect('/:action/*', ['plugin' => 'HiddenField', 'controller' => 'HiddenFields']);
});
