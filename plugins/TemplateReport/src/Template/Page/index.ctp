<?php
$this->extend('TemplateReport.Layout/container');

$this->start('contentBody');

echo $this->element('TemplateReport.table');

$this->end();
?>
