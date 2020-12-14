<?php
/**
* Mini Dashboard
*/
$toolbar = $this->fetch('toolbar');
$toolbarClass = [];

if (isset($toolbarButtons) && $toolbarButtons->offsetExists('search')) {
    $toolbarClass[] = 'toolbar-search';

    $found = false;
    foreach ($toolbarButtons as $button) {
        if ((array_key_exists('type', $button) && $button['type'] == 'button') || !array_key_exists('type', $button)) {
            $found = true;
            break;
        }
    }
    if ($found == false) {
        $toolbarClass[] = 'btn-none';
    }
}
if (isset($indexElements) && array_key_exists('advanced_search', $indexElements)) {
    $toolbarClass[] = 'toolbar-search-adv';
}

echo $this->Html->script('highchart/highcharts', ['block' => true]);
echo $this->Html->script('dashboards', ['block' => true]);
?>
<!-- Please take note of the CSS for this chart place holder -->
<style type="text/css">
	.data-section {
		vertical-align: middle;
	}
	.minidashboard-donut {
		height: 100px;
		width: 100px;
		visibility: hidden;
	}
</style>
<div class="row alert" ng-class="disableElement" style="align-items: center">
	<a data-dismiss="alert" href="#" aria-hidden="true" class="close hide">×</a>
	<div class="col-sm-12 col-md-4 text-center count-section">
		<!--Getting the correct icon and the header name base on the calling method-->

		<div class="data-field" style="color: #293845">
			<h4><?= __('Total ' . ucfirst($model)) ?>:</h4>
			<h1 class="data-header">
			<?= number_format($modelCount) ?>
			</h1>
		</div>
	</div>

    <div class="col-sm-12 col-md-8 charts-section" >
	<?php foreach ( $modelArray as $highChartData ) : ?>

		<div class="data-field">
			<div class="highchart minidashboard-donut"><?php echo $highChartData; ?></div>
		</div>

	<?php endforeach ?>
    </div>
</div>
