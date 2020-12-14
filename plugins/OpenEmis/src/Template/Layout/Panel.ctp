<?php
$this->extend('OpenEmis./Layout/Container');
$this->assign('contentHeader', (!empty($contentHeader) ? $contentHeader : $this->Label->get("$model._content_header")));

$this->start('contentBody');
$panelHeader = $this->fetch('panelHeader');

// fix PIB Ulan
$paramsPass = $this->request->params['pass'];
$action = $this->request->action;
if (count($paramsPass) > 0) {
    foreach ($paramsPass as $param) {
        if (!is_numeric($param) && in_array(strtolower($param), ['add', 'reconfirm', 'index', 'view', 'edit', 'dashboard'])) { // this is an action
            $action = strtolower($param);
            break;
        }
    }
}
// fix PIB Ulan end
?>

<div class="panel">
	<div class="panel-body panel-<?=$action?>">
		<?= $this->element('OpenEmis.alert') ?>
		<!--?= $this->element('data_overview') ?-->
		<?php
		// if (isset($indexDashboard)) {
		// 	echo $this->element($indexDashboard);
		// }
		?>
		<?= $this->element('nav_tabs') ?>
		<?= $this->fetch('panelBody') ?>
	</div>
</div>

<?php $this->end() ?>
