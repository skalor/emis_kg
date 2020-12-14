<?php
use Cake\Core\Configure;
echo $this->Html->meta(array('name' => 'viewport', 'content' => 'width=320, initial-scale=1'));
echo $this->element('OpenEmis.styles');
echo $this->Html->css('ControllerAction.../plugins/jasny/css/jasny-bootstrap.min.css?v='.Configure::read('stylesVersion'));

// SCHOOL DASHBOARD CHARTS //
echo $this->Html->css('highchart-override.css?v='.Configure::read('stylesVersion'));
