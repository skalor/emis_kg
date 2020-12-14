<?php
use Cake\Routing\Router;

Router::scope('/MonFields', ['plugin' => 'MonFields'], function ($routes) {
    Router::connect('/MonFields', ['plugin' => 'MonFields', 'controller' => 'MonFields']);
    Router::connect('/MonFields/:action/*', ['plugin' => 'MonFields', 'controller' => 'MonFields']);
    Router::connect('/MonSections', ['plugin' => 'MonFields', 'controller' => 'MonSections']);
    Router::connect('/MonSections/:action/*', ['plugin' => 'MonFields', 'controller' => 'MonSections']);
});
