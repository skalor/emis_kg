<?php
$this->extend('Page.Layout/container');
$this->start('contentBody');

$formOptions = $this->Page->getFormOptions();
$template = $this->Page->getFormTemplate();
$this->Form->templates($template);

echo $this->Form->create($data, $formOptions);
echo $this->MonFields->renderInputElements();
echo $this->MonFields->getFormButtons();
echo $this->Form->end();

$this->end();
?>
