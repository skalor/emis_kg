<?php
use Cake\Routing\Router;

Router::scope('/TemplateReport', ['plugin' => 'TemplateReport'], function ($routes) {
	Router::connect('/TemplateReport', ['plugin' => 'TemplateReport', 'controller' => 'TemplateReport']);
	Router::connect('/TemplateReport/:action/*', ['plugin' => 'TemplateReport', 'controller' => 'TemplateReport']);
});
