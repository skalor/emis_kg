
<?php
$this->extend('Page.Layout/container');
$this->start('contentBody');
echo $this->Html->script('StatisticReport.angular/controller/StatisticReport');
echo "<style>
    .right-pane {
        left: 100px!important;
        width: 94%!important;
    }
</style>";
$formOptions = $this->Page->getFormOptions();
$template = $this->Page->getFormTemplate();

$this->Form->templates($template);
echo "<div ng-controller='StatisticReport'>";
echo $this->Form->create(!is_array($data) ? $data : null, $formOptions);
echo $this->Page->renderInputElements();
echo $this->Page->getFormButtons();
echo $this->Form->end();
echo "</div>";
echo "<style>
    .right-pane {
        left: 100px!important;
        width: 94%!important;
    }
    .content-wrapper {
        padding: 0px 15px!important;
    }
</style>";
$this->end();
?>
