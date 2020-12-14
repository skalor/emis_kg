<?php
use Cake\Routing\Router;

Router::scope('/StatisticReport', ['plugin' => 'StatisticReport'], function ($routes) {
	Router::connect('/StatisticReport', ['plugin' => 'StatisticReport', 'controller' => 'StatisticReport']);
	Router::connect('/StatisticReport/:action/*', ['plugin' => 'StatisticReport', 'controller' => 'StatisticReport']);
});
