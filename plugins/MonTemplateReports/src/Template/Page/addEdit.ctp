<?php
$this->extend('Page.Layout/container');
$this->start('contentBody');
echo $this->Html->script([
    'MonTemplateReports.ckeditor/ckeditor.js',
    'MonTemplateReports.ckeditor/adapters/jquery.js'
]);

$formOptions = $this->Page->getFormOptions();
$template = $this->Page->getFormTemplate();
$this->Form->templates($template);
echo $this->Form->create($data, $formOptions);
echo $this->Page->renderInputElements();
echo $this->Page->getFormButtons();
echo $this->Form->end();

echo "<script>
    $(document).ready(function() {
        $('.textarea').addClass('clearfix');
        $('textarea').ckeditor();
    });
</script>";
$this->end();
?>
