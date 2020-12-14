<?php
$this->extend('StatisticReportHistory.Layout/container');

$this->start('contentBody');

echo $this->element('StatisticReportHistory.table');

$this->end();
?>
