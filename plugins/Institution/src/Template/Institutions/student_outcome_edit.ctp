<?= $this->Html->script('app/components/alert/alert.svc', ['block' => true]); ?>
<?= $this->Html->script('Institution.angular/student_outcomes/institution.student.outcomes.ctrl', ['block' => true]); ?>
<?= $this->Html->script('Institution.angular/student_outcomes/institution.student.outcomes.svc', ['block' => true]); ?>
<?php
$this->extend('OpenEmis./Layout/Panel');
$this->start('toolbar');
?>
<?= $this->Html->link('<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M22.404 11.1517H7.18845L14.1773 4.16275L12.402 2.3999L2.39999 12.4019L12.402 22.4039L14.1648 20.6411L7.18845 13.6522H22.404V11.1517Z" fill="#004A51"></path> </svg>', $viewUrl, ['class' => 'btn btn-xs btn-default', 'data-toggle' => 'tooltip', 'data-placement' => 'bottom', 'data-container' => 'body', 'title' => __('Back'), 'escapeTitle' => false]) ?>

<?= $this->Html->link('<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"> <path fill-rule="evenodd" clip-rule="evenodd" d="M2.75 6C2.75 5.17 3.42 4.5 4.25 4.5C5.08 4.5 5.75 5.17 5.75 6C5.75 6.83 5.08 7.5 4.25 7.5C3.42 7.5 2.75 6.83 2.75 6ZM2.75 12C2.75 11.17 3.42 10.5 4.25 10.5C5.08 10.5 5.75 11.17 5.75 12C5.75 12.83 5.08 13.5 4.25 13.5C3.42 13.5 2.75 12.83 2.75 12ZM4.25 16.5C3.42 16.5 2.75 17.18 2.75 18C2.75 18.82 3.43 19.5 4.25 19.5C5.07 19.5 5.75 18.82 5.75 18C5.75 17.18 5.08 16.5 4.25 16.5ZM21.25 19H7.25V17H21.25V19ZM7.25 13H21.25V11H7.25V13ZM7.25 7V5H21.25V7H7.25Z" fill="#FF6C6C"/> </svg>', $indexUrl, ['class' => 'btn btn-xs btn-default', 'data-toggle' => 'tooltip', 'data-placement' => 'bottom', 'data-container' => 'body', 'title' => __('List'), 'escapeTitle' => false]) ?>
<?php
$this->end();
$this->start('panelBody');
?>
<style type="text/css">
    .ag-body-container {
        max-height: 380px;
    }
    .ag-floating-bottom-viewport
    .ag-floating-bottom-container .ag-row{min-height:110px;}
</style>
<form accept-charset="utf-8" id="content-main-form" class="ng-pristine ng-valid" novalidate="novalidate" ng-controller="InstitutionStudentOutcomesCtrl as InstitutionStudentOutcomesController" ng-init="InstitutionStudentOutcomesController.classId=<?= $classId ?>; InstitutionStudentOutcomesController.outcomeTemplateId=<?= $outcomeTemplateId ?>;">
    <div class="form-horizontal">
        <div class="alert {{InstitutionStudentOutcomesController.class}}" ng-hide="InstitutionStudentOutcomesController.message == null">
            <a class="close" aria-hidden="true" href="#" data-dismiss="alert">×</a>{{InstitutionStudentOutcomesController.message}}
        </div>
        <div class="input string required">
            <label><?= __('Class Name') ?></label>
            <input ng-model="InstitutionStudentOutcomesController.className" type="text" disabled="disabled">
        </div>
        <div class="input string required">
            <label><?= __('Academic Period') ?></label>
            <input ng-model="InstitutionStudentOutcomesController.academicPeriodName" type="text" disabled="disabled">
        </div>
        <div class="input string required">
            <label><?= __('Outcome Template') ?></label>
            <input ng-model="InstitutionStudentOutcomesController.outcomeTemplateName" type="text" disabled="disabled">
        </div>
    </div>
    <div class="clearfix"></div>
    <hr>
    <h3><?= __('Student') ?></h3>
    <div class="dropdown-filter">
        <div class="filter-label">
            <i class="fa fa-filter"></i>
            <label><?= __('Filter')?></label>
        </div>
        <div class="select">
            <label><?= __('Outcome Period') ?>:</label>
            <div class="input-select-wrapper">
                <select name="outcome_period" ng-options="period.id as period.code_name for period in InstitutionStudentOutcomesController.periodOptions" ng-model="InstitutionStudentOutcomesController.selectedPeriod" ng-change="InstitutionStudentOutcomesController.changeOutcomeOptions(true);">
                    <option value="" ng-if="InstitutionStudentOutcomesController.periodOptions.length == 0"><?= __('No Options') ?></option>
                </select>
            </div>
        </div>
        <div class="select">
            <label><?= __('Subject') ?>:</label>
            <div class="input-select-wrapper">
                <select name="education_subject" ng-options="subject.id as subject.code_name for subject in InstitutionStudentOutcomesController.subjectOptions" ng-model="InstitutionStudentOutcomesController.selectedSubject" ng-change="InstitutionStudentOutcomesController.changeOutcomeOptions(false);">
                    <option value="" ng-if="InstitutionStudentOutcomesController.subjectOptions.length == 0"><?= __('No Options') ?></option>
                </select>
            </div>
        </div>
        <div class="select">
            <label><?= __('Student') ?>:</label>
            <div class="input-select-wrapper">
                <select name="student" ng-options="student.student_id as student.user.name_with_id for student in InstitutionStudentOutcomesController.studentOptions" ng-model="InstitutionStudentOutcomesController.selectedStudent" ng-change="InstitutionStudentOutcomesController.changeStudentOptions(true);">
                    <option value="" ng-if="InstitutionStudentOutcomesController.studentOptions.length == 0"><?= __('No Options') ?></option>
                </select>
            </div>
        </div>
        <div class="text">
            <label><?= __('Status') ?></label>
            <input ng-model="InstitutionStudentOutcomesController.selectedStudentStatus" type="text" disabled="disabled">
        </div>

    </div>
    <div id="institution-student-outcome-table" class="table-wrapper">
        <div ng-if="InstitutionStudentOutcomesController.dataReady" kd-ag-grid="InstitutionStudentOutcomesController.gridOptions"></div>
    </div>
</form>

<?php
$this->end();
?>
