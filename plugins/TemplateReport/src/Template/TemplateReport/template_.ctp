<?php echo $this->Html->css('TemplateReport.excel/ej.web.all.min');?>
<!--<script src="https://code.angularjs.org/1.4.0-rc.2/angular.min.js"></script>-->
<script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>


<?php //echo $this->Html->script('TemplateReport.angular/jquery-3.0.0.min');?>
<?php echo $this->Html->script('TemplateReport.angular/jsrender.min');?>
<?php echo $this->Html->script('TemplateReport.angular/jquery.validate.min');?>
<?php echo $this->Html->script('TemplateReport.angular/ej.web.all.min');?>
<?php echo $this->Html->script('TemplateReport.angular/angular-route.min');?>
<?php echo $this->Html->script('TemplateReport.angular/ej.widget.angular.min');?>
<?php echo $this->Html->script('TemplateReport.angular/SpreadsheetCtrl');?>

<div ng-controller="SpreadsheetCtrl" style="padding-top: 85px;">
    <div id="Spreadsheet" ej-spreadsheet></div>
</div>