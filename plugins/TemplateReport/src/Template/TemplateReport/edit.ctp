
<?php
$this->extend('Page.Layout/container');
$this->start('contentBody');

echo $this->Html->script([
    'TemplateReport.ckeditor/adapter/jquery.js',
    'TemplateReport.ckeditor/ckeditor.js',
    'TemplateReport.angular/controller/TemplateEdit',
]);

echo "
<style>
    .right-pane {
        left: 100px!important;
        width: 94%!important;
    }
    
    
    #templatereport-content, .textarea {
        width: 100%!important;
        height: 500px!important;
    }
    
    div#cke_1_contents {
        height: 376px;
        min-height: 376px;
    }
</style>
";

$formOptions = $this->Page->getFormOptions();
$template = $this->Page->getFormTemplate();

$this->Form->templates($template);
echo "<div ng-controller='TemplateEdit'>";
echo $this->Form->create(!is_array($data) ? $data : null, $formOptions);
echo $this->Page->renderInputElements();
//echo '<div id="editor"></div>';
echo $this->Page->getFormButtons();
echo $this->Form->end();
echo "</div>";
$this->end();
?>
