<?php
use Cake\Routing\Router;

Router::scope('/Faq', ['plugin' => 'Faq'], function ($routes) {
    Router::connect('/Faq', ['plugin' => 'Faq', 'controller' => 'Faq']);
    Router::connect('/Faq/:action/*', ['plugin' => 'Faq', 'controller' => 'Faq']);
});
