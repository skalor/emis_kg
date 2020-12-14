<?= $this->Html->script('app/components/alert/alert.svc', ['block' => true]); ?>
<?= $this->Html->script('Institution.angular/institutionclasses/institution.class.students.svc', ['block' => true]); ?>
<?= $this->Html->script('Institution.angular/institutionclasses/institution.class.students.ctrl', ['block' => true]); ?>
<?= $this->Html->css('ControllerAction.../plugins/chosen/css/chosen.min', ['block' => true]); ?>
<?= $this->Html->script('ControllerAction.../plugins/chosen/js/chosen.jquery.min', ['block' => true]); ?>
<?= $this->Html->script('ControllerAction.../plugins/chosen/js/angular-chosen.min', ['block' => true]); ?>
<?php
$this->extend('OpenEmis./Layout/Panel');
$this->start('toolbar');
?>
<style type='text/css'>
    .ag-grid-duration {
        width: 50%;
        border: none;
        background-color: inherit;
        text-align: center;
    }

    .ag-grid-dir-ltr {
        direction: ltr !important;
    }
</style>
<?= $this->Html->link('<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M22.404 11.1517H7.18845L14.1773 4.16275L12.402 2.3999L2.39999 12.4019L12.402 22.4039L14.1648 20.6411L7.18845 13.6522H22.404V11.1517Z" fill="#004A51"></path> </svg>', $viewUrl, ['class' => 'btn btn-xs btn-default', 'data-toggle' => 'tooltip', 'data-placement' => 'bottom', 'data-container' => 'body', 'title' => __('Back'), 'escapeTitle' => false]) ?>

<?= $this->Html->link('<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"> <path fill-rule="evenodd" clip-rule="evenodd" d="M2.75 6C2.75 5.17 3.42 4.5 4.25 4.5C5.08 4.5 5.75 5.17 5.75 6C5.75 6.83 5.08 7.5 4.25 7.5C3.42 7.5 2.75 6.83 2.75 6ZM2.75 12C2.75 11.17 3.42 10.5 4.25 10.5C5.08 10.5 5.75 11.17 5.75 12C5.75 12.83 5.08 13.5 4.25 13.5C3.42 13.5 2.75 12.83 2.75 12ZM4.25 16.5C3.42 16.5 2.75 17.18 2.75 18C2.75 18.82 3.43 19.5 4.25 19.5C5.07 19.5 5.75 18.82 5.75 18C5.75 17.18 5.08 16.5 4.25 16.5ZM21.25 19H7.25V17H21.25V19ZM7.25 13H21.25V11H7.25V13ZM7.25 7V5H21.25V7H7.25Z" fill="#FF6C6C"/> </svg>', $indexUrl, ['class' => 'btn btn-xs btn-default', 'data-toggle' => 'tooltip', 'data-placement' => 'bottom', 'data-container' => 'body', 'title' => __('List'), 'escapeTitle' => false]) ?>
<?php
$this->end();
$this->start('panelBody');
?>
<form accept-charset="utf-8" id="content-main-form" class="form-horizontal ng-pristine ng-valid" novalidate="novalidate" ng-controller="InstitutionClassStudentsCtrl as InstitutionClassStudentsController">
    <div class="alert {{InstitutionClassStudentsController.class}}" ng-hide="InstitutionClassStudentsController.message == null">
        <a class="close" aria-hidden="true" href="#" data-dismiss="alert">×</a>{{InstitutionClassStudentsController.message}}
    </div>
    <div class="input string required">
        <label><?= __('Academic Period') ?></label>
        <input ng-model="InstitutionClassStudentsController.academicPeriodName" type="text" disabled="disabled">
    </div>
    <div class="input string required" ng-controller="inputClassValidationCtr">
        <label><?= __('Class Name') ?></label>
        <input ng-model="InstitutionClassStudentsController.className" type="string" ng-init="InstitutionClassStudentsController.className='';">
        <div ng-if="InstitutionClassStudentsController.postError.name" class="error-message">
            <p ng-repeat="error in InstitutionClassStudentsController.postError.name">{{ error }}</p>
        </div>
    </div>
    <div class="input select required error">
        <label><?= __('Shift') ?></label>
        <div class="input-select-wrapper">
            <select name="InstitutionClasses[institution_shift_id]" id="institutionclasses-institution-shift-id"
                ng-options="option.id as option.name for option in InstitutionClassStudentsController.shiftOptions"
                ng-model="InstitutionClassStudentsController.selectedShift"
                ng-init="InstitutionClassStudentsController.selectedShift=null;"
                >
                <option value="" >-- <?= __('Select') ?> --</option>
            </select>
        </div>
        <div ng-if="InstitutionClassStudentsController.postError.institution_shift_id" class="error-message">
            <p ng-repeat="error in InstitutionClassStudentsController.postError.institution_shift_id">{{ error }}</p>
        </div>
    </div>

    <div class="input select">
        <label><?= __('Language') ?></label>
        <div class="input-select-wrapper">
            <select name="InstitutionClasses[language_id]" id="institutionclasses-language-id"
                    ng-model="InstitutionClassStudentsController.language_id">
                <option value="" selected>-- <?= __('Select') ?> --</option>
                <?php if(isset($classLanguages)) : foreach($classLanguages as $key => $item): ?>
                    <option value="<?= $key ?>" <?= ($language_id == $item) ? 'select="selected" ' :''?> ng-selected="InstitutionClassStudentsController.language_id == <?= $key ?>"><?= __($item) ?></option>
                <?php endforeach;endif; ?>
            </select>
        </div>
        <div ng-if="InstitutionClassStudentsController.postError.language_id" class="error-message">
            <p ng-repeat="error in InstitutionClassStudentsController.postError.language_id">{{ error }}</p>
        </div>
    </div>

    <div class="input select">
        <label><?= __('Institution Classes View') ?></label>
        <div class="input-select-wrapper">
            <select name="InstitutionClasses[institution_classes_view_id]" id="institution-classes-view-id"
                    ng-model="InstitutionClassStudentsController.institution_classes_view_id">
                <option value="" selected>-- <?= __('Select') ?> --</option>
                <?php if(isset($classViews)) : foreach($classViews as $key => $item): ?>
                    <option value="<?= $key ?>" <?= ($institution_classes_view_id == $item) ? 'select="selected" ' :''?> ng-selected="InstitutionClassStudentsController.institution_classes_view_id == <?= $key ?>"><?= __($item) ?></option>
                <?php endforeach;endif; ?>
            </select>
        </div>
        <div ng-if="InstitutionClassStudentsController.postError.institution_classes_view_id" class="error-message">
            <p ng-repeat="error in InstitutionClassStudentsController.postError.institution_classes_view_id">{{ error }}</p>
        </div>
    </div>

    <div class="input select">
        <label><?= __('Home Room Teacher') ?></label>
        <div class="input-select-wrapper">
            <select name="InstitutionClasses[staff_id]" id="institutionclasses-staff-id"
                ng-options="option.id as option.name for option in InstitutionClassStudentsController.teacherOptions"
                ng-model="InstitutionClassStudentsController.selectedTeacher"
                ng-init="InstitutionClassStudentsController.selectedTeacher=null;"
                ng-change="InstitutionClassStudentsController.secondaryTeacherOptions = InstitutionClassStudentsController.changeStaff(InstitutionClassStudentsController.selectedTeacher);"
                >
                <option value="" >-- <?= __('Select Teacher or Leave Blank') ?> --</option>
            </select>
        </div>
        <div ng-if="InstitutionClassStudentsController.postError.staff_id" class="error-message">
            <p ng-repeat="error in InstitutionClassStudentsController.postError.staff_id">{{ error }}</p>
        </div>
    </div>
    <div class="input select">
        <label><?= __('Secondary Teachers') ?></label>
        <select chosen
            data-placeholder="-- <?=__('Select Teacher or Leave Blank') ?> --"
            name="InstitutionClasses[secondary_staff_id]"
            id="institutionclasses-secondary-staff-id"
            multiple="multiple"
            class="chosen-select"
            options="InstitutionClassStudentsController.secondaryTeacherOptions"
            ng-model="InstitutionClassStudentsController.selectedSecondaryTeacher"
            ng-options="option.id as option.name for option in InstitutionClassStudentsController.secondaryTeacherOptions"
            ng-init="InstitutionClassStudentsController.selectedSecondaryTeacher=[];"
            ng-change="InstitutionClassStudentsController.teacherOptions = InstitutionClassStudentsController.changeStaff(InstitutionClassStudentsController.selectedSecondaryTeacher);"
>
        </select>
        <div ng-if="InstitutionClassStudentsController.postError.staff_id" class="error-message">
            <p ng-repeat="error in InstitutionClassStudentsController.postError.staff_id">{{ error }}</p>
        </div>
    </div>
    <div class="input string required">
        <label><?=
            __('Capacity') . '&nbsp&nbsp;<i class="fa fa-info-circle fa-lg table-tooltip icon-blue" data-placement="right" data-toggle="tooltip" data-animation="false" data-container="body" title="" data-html="true" data-original-title="' .  __('Capacity must not exceed ') . ' {{InstitutionClassStudentsController.maxStudentsPerClass}} ' . __(' students per class') . '"></i>'
        ?></label>
        <input ng-model="InstitutionClassStudentsController.classCapacity" type="string" ng-init="InstitutionClassStudentsController.classCapacity='';">
        <div ng-if="InstitutionClassStudentsController.postError.capacity" class="error-message">
            <p ng-repeat="error in InstitutionClassStudentsController.postError.capacity">{{ error }}</p>
        </div>
    </div>
	<div class="input select clearfix">
        <label><?= __('Add Student') ?></label>
        <div class="input-form-wrapper" ng-init="InstitutionClassStudentsController.classId='<?= $classId ?>'; InstitutionClassStudentsController.redirectUrl='<?= $this->Url->build($viewUrl) ?>'; InstitutionClassStudentsController.alertUrl='<?= $this->Url->build($alertUrl) ?>';">
    		<kd-multi-select ng-if="InstitutionClassStudentsController.dataReady" grid-options-top="InstitutionClassStudentsController.gridOptionsTop" grid-options-bottom="InstitutionClassStudentsController.gridOptionsBottom"></kd-multi-select>
    	</div>

        <div class="form-buttons">
            <div class="button-label"></div>
            <button class="btn btn-default btn-save" type="button" ng-click="InstitutionClassStudentsController.postForm();">
                <?= __('Save') ?>
            </button>
            <?= $this->Html->link(__('Cancel'), $viewUrl, ['class' => 'btn btn-outline btn-cancel', 'escapeTitle' => false]) ?>

            <button id="reload" type="submit" name="submit" value="reload" class="hidden">reload</button>
        </div>
    </div>
</form>
<?php
$this->end();
?>
