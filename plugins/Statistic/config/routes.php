<?php
use Cake\Routing\Router;

Router::scope('/Statistic', ['plugin' => 'Employees'], function ($routes) {
    Router::connect('/Statistic', ['plugin' => 'Statistic', 'controller' => 'Statistic']);
    Router::connect('/Statistic/:action/*', ['plugin' => 'Statistic', 'controller' => 'Statistic']);
});
