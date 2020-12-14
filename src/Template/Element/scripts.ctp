<?php
echo $this->element('OpenEmis.scripts');

/*echo sprintf('<script type="text/javascript" src="%s%s"></script>', $this->webroot, 'Config/getJSConfig');*/
use Cake\Core\Configure;
echo $this->element('ControllerAction.scripts');
echo $this->Html->script('doughnutchart/Chart.min.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('doughnutchart/Chart.Doughnut.js?v='.Configure::read('scriptsVersion'));

// Slider //
echo $this->Html->script('app/shared/ngSlider/slider.js?v='.Configure::read('scriptsVersion'));

echo $this->Html->script('app/app.ctrl.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('app/app.svc.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('app/services/app/utils.svc.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('Restful.kd.orm.svc.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('Restful.kd.data.svc.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('app/services/app/aggrid.locale.svc.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('app/services/app/kd.session.svc.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('app/services/app/kd.access.svc.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('Area.tree/sg.tree.ctrl.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('Area.tree/sg.tree.svc.js?v='.Configure::read('scriptsVersion'));

echo $this->Html->script('angular/kdModule/controllers/kd.ctrl.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('angular/kdModule/directives/kd.drt.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('angular/kdModule/services/kd.common.svc.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('angular/kdModule/kd.module.js?v='.Configure::read('scriptsVersion'));

// Assessments specific controller
echo $this->Html->script('Assessment.angular/assessments/assessmentAdminModule.js?v='.Configure::read('scriptsVersion'));

//JS use in Core
echo $this->Html->script('app.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('app.table.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('config.js?v='.Configure::read('scriptsVersion'));
