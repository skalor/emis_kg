<?php
//TemplateDetail
$this->extend('Page.Layout/container');

echo $this->Html->script([
    'TemplateReport.ckeditor/adapter/jquery.js',
    'TemplateReport.ckeditor/ckeditor.js',
    'TemplateReport.angular/controller/TemplateDetail',
]);

$this->start('contentBody');

if (isset($elements)) {
    echo "<div ng-controller='TemplateDetail'>";
    echo $this->Page->renderViewElements($elements);
    echo '<div>';
} else {
    echo 'There are no elements';
}

$this->end();
?>
