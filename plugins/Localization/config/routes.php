<?php

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

Router::scope('/InstitutionTranslations', [
    'plugin' => 'Localization',
    'controller' => 'InstitutionTranslations'
], function (RouteBuilder $routes) {
    $routes->connect('/');
    $routes->connect('/:action/*');
});

Router::scope('/ModuleTranslations', [
    'plugin' => 'Localization',
    'controller' => 'ModuleTranslations'
], function (RouteBuilder $routes) {
    $routes->connect('/');
    $routes->connect('/:action/*');
});

Router::scope('/Translations', ['plugin' => 'Localization', 'controller' => 'Translations'], function (RouteBuilder $routes) {
    $routes->connect('/');
    $routes->connect('/translate/*', ['action' => 'translate', '_ext' => 'json']);
    $routes->connect('/:action/*');
});
