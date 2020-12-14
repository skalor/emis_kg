<?php
use Cake\Routing\Router;

Router::scope('/StatisticReportHistory', ['plugin' => 'StatisticReportHistory'], function ($routes) {
	Router::connect('/StatisticReportHistory', ['plugin' => 'StatisticReportHistory', 'controller' => 'StatisticReportHistory']);
	Router::connect('/StatisticReportHistory/:action/*', ['plugin' => 'StatisticReportHistory', 'controller' => 'StatisticReportHistory']);
});
