<?php

use Cake\Core\Configure;
//Main Library
echo $this->Html->script('OpenEmis.lib/css_browser_selector.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.lib/respond.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.lib/jquery/jquery.min.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.lib/jquery/jquery-ui.min.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.lib/angular/angular.min.js?v='.Configure::read('scriptsVersion'));
// echo $this->Html->script('OpenEmis.lib/angular/angular-route.min');
echo $this->Html->script('OpenEmis.lib/angular/angular-animate.min.js?v='.Configure::read('scriptsVersion'));
// echo $this->Html->script('OpenEmis.angular/ng.layout-splitter');
echo $this->Html->script('OpenEmis.angular/kd-angular-elem-sizes.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.angular/kd-angular-checkbox-radio-button.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.angular/kd-angular-multi-select/kd-angular-multi-select.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.angular/kd-angular-treedropdown.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.angular/kd-angular-ag-grid.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.lib/holder.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.lib/angular/ui-bootstrap-tpls.min.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.angular/kd-angular-advanced-search.ctrl.js?v='.Configure::read('scriptsVersion'));

//Only when needed this have to be added in ScriptBottom
echo $this->Html->script('OpenEmis.jquery/jq.mobile-menu.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.jquery/jq.loader.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.jquery/jq.chosen.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.jquery/jq.checkable.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.jquery/jq.datetime-picker.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.jquery/jq.table.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.jquery/jq.tabs.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.jquery/jq.tooltip.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.jquery/jq.header.js?v='.Configure::read('scriptsVersion'));
// echo $this->Html->script('OpenEmis.jquery/jq.multiple-image-uploader');
// echo $this->Html->script('OpenEmis.jquery/jq.gallery');

//External Plugins
echo $this->Html->script('OpenEmis.../plugins/bootstrap/js/bootstrap.min.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.../plugins/fuelux/js/fuelux.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.../plugins/scrolltabs/js/jquery.mousewheel.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.../plugins/scrolltabs/js/jquery.scrolltabs.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.../plugins/slider/js/bootstrap-slider.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.../plugins/ng-scrolltabs/js/angular-ui-tab-scroll.js?v='.Configure::read('scriptsVersion'));
// echo $this->Html->script('OpenEmis.../plugins/ng-agGrid/js/ag-grid');
echo $this->Html->script('OpenEmis.../plugins/ag-grid-enterprise/dist/ag-grid-enterprise.min.js?v='.Configure::read('scriptsVersion'));

//Tree Dropdown
echo $this->Html->script('OpenEmis.../plugins/multi-select-tree/dist/angular-multi-select-tree-0.1.0.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis.../plugins/multi-select-tree/dist/angular-multi-select-tree-0.1.0.tpl.js?v='.Configure::read('scriptsVersion'));


//новые
echo $this->Html->script('OpenEmis./js/new-js/owl.carousel.min');
echo $this->Html->script('OpenEmis./js/new-js/script.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('OpenEmis./js/new-js/owl.carousel.js?v='.Configure::read('scriptsVersion'));
echo $this->Html->script('validation');
echo $this->Html->script('slick.min');
echo $this->Html->script('custom.js?v='.Configure::read('scriptsVersion'));

// Moment JS
echo $this->Html->script('OpenEmis./js/moment/moment.js?v='.Configure::read('scriptsVersion'));

// Jquery Mask plugin
echo $this->Html->script('OpenEmis./js/jquery-mask/dist/jquery.mask.min.js?v='.Configure::read('scriptsVersion'));
// Cookie plugin
echo $this->Html->script('OpenEmis.jquery/jq.cookie.js?v='.Configure::read('scriptsVersion'));
