<?= $this->Html->script('app/components/alert/alert.svc', ['block' => true]); ?>
<?= $this->Html->script('Institution.angular/staff/institution.staff.attendances.svc', ['block' => true]); ?>
<?= $this->Html->script('Institution.angular/staff/institution.staff.attendances.ctrl', ['block' => true]); ?>
<?= $this->Html->script('ControllerAction.../plugins/timepicker/js/bootstrap-timepicker.min', ['block' => true]);?>
<?= $this->Html->css('ControllerAction.../plugins/timepicker/css/bootstrap-timepicker.min', ['block' => true]); ?>
<?php
$this->start('toolbar');
?>
<?php if ($_import) : ?>
    <a href="<?=$importUrl ?>" ng-show="$ctrl.action == 'view'">
        <button class="btn btn-xs btn-default" data-toggle="{{test()}}" data-placement="bottom" data-container="body" title="<?= __('Import') ?>" >
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M10.8333 13.3333V4.51183L13.5774 7.25591L14.7559 6.07739L10 1.32147L5.24408 6.07739L6.42259 7.25591L9.16667 4.51183V13.3333H10.8333ZM17.5 16.6667V9.16665H15.8333V16.6667H4.16667V9.16665H2.5V16.6667C2.5 17.5871 3.24619 18.3333 4.16667 18.3333H15.8333C16.7538 18.3333 17.5 17.5871 17.5 16.6667Z" fill="#004A51"></path>
            </svg>
        </button>
    </a>
</button>
<?php endif; ?>
<?php if ($_edit) : ?>
    <button class="btn btn-xs btn-default" data-toggle="tooltip" data-placement="bottom" data-container="body" title="<?= __('Edit');?>" ng-show="$ctrl.action == 'view' && $ctrl.selectedDay != -1" ng-click="$ctrl.onEditClick()">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g clip-path="url(#clip0)">
                <path d="M2.4 16.5601L0 24.0001L7.44 21.6001L2.4 16.5601Z" fill="#009966"></path>
                <path d="M15.795 3.12622L4.08557 14.8357L9.17666 19.9268L20.8861 8.21731L15.795 3.12622Z" fill="#009966"></path>
                <path d="M23.64 3.72L20.28 0.36C19.8 -0.12 19.08 -0.12 18.6 0.36L17.52 1.44L22.56 6.48L23.64 5.4C24.12 4.92 24.12 4.2 23.64 3.72Z" fill="#009966"></path>
            </g>
            <defs>
                <clipPath id="clip0">
                    <rect width="24" height="24" fill="white"></rect>
                </clipPath>
            </defs>
        </svg>
    </button>

    <button class="btn btn-xs btn-default" data-toggle="tooltip" data-placement="bottom" data-container="body" title="<?= __('Back');?>" ng-show="$ctrl.action == 'edit'" ng-click="$ctrl.onBackClick()">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M22.404 11.1517H7.18845L14.1773 4.16275L12.402 2.3999L2.39999 12.4019L12.402 22.4039L14.1648 20.6411L7.18845 13.6522H22.404V11.1517Z" fill="#004A51"></path>
        </svg>
    </button>
<?php endif; ?>
<?php if ($_excel) : ?>
    <button class="btn btn-xs btn-default" data-toggle="tooltip" data-placement="bottom" data-container="body" title="<?= __('Export') ?>" ng-show="$ctrl.action == 'view'" ng-click="$ctrl.onExcelClick()">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M18 12L24 12L12 24L-5.24537e-07 12L6 12L6 -7.86805e-07L18 -2.62268e-07L18 12ZM12 19.755L16.755 15L15 15L15 3L8.99997 3L8.99997 15L7.24497 15L12 19.755Z" fill="#009966"></path>
        </svg>
    </button>
<?php endif; ?>
<?php
$this->end();
?>

<?php
$this->extend('OpenEmis./Layout/Container');
$this->assign('contentHeader', (!empty($contentHeader) ? $contentHeader : $this->Label->get("$model._content_header")));

$this->start('contentBody');
$panelHeader = $this->fetch('panelHeader');
$paramsQuery = $this->ControllerAction->getQueryString();
$institutionId = $paramsQuery['institution_id'];
?>
<style>
    .attendance-dashboard .data-section.single-day {
        width: 32%;
    }

    .splitter-filter select[disabled] {
        background-color: #f2f2f2!important;
        border: 1px solid #ccc!important;
        color: #999!important;
    }

    .splitter-filter .split-content-header {
        margin-bottom: 15px;
    }

    .splitter-filter .input-selection.attendance {
        width: 100%;
    }

    #institution-staff-attendances-table .sg-theme .ag-cell {
        display: flex;
        flex-flow: column wrap;
        justify-content: center;
    }

    #institution-staff-attendances-table .ag-cell textarea#comment:focus {
        outline: none;
    }

    #institution-staff-attendances-table .ag-cell textarea#comment {
        display: block;
        padding: 5px 10px;
        -webkit-border-radius: 3px;
        border-radius: 3px;
        font-size: 12px;
        height: 70px;
        width: 100%;
        border: 1px solid #CCC;
    }

    #institution-staff-attendances-table .ag-cell .input-select-wrapper {
        margin-bottom: 0;
    }

    #institution-staff-attendances-table .ag-cell .input-select-wrapper select {
        background: #FFFFFF;
        display: block;
    }

    #institution-staff-attendances-table .sg-theme .ag-header-cell.children-period .ag-header-cell-label {
        display: flex;
        justify-content: center;
        padding: 10px 0;
    }

    #institution-staff-attendances-table .sg-theme .ag-header-group-cell {
        border-right: 1px solid #DDDDDD;
        border-bottom: 1px solid #DDDDDD;
        font-weight: 700;
        text-align: center;
        padding: 10px;
    }

    #institution-staff-attendances-table .sg-theme .children-cell {
        text-align: center;
    }

    #institution-staff-attendances-table .sg-theme .ag-row-hover {
        background-color: #FDFEE6 !important;
    }

    .rtl #institution-staff-attendances-table .sg-theme .ag-header-group-cell {
        border-right: 0;
        border-left: 1px solid #DDDDDD;
    }

    #institution-staff-attendances-table .sg-theme .time-view {
        padding: 4px;
        font-size: 13px;
    }

    #institution-staff-attendances-table .sg-theme .time-view > i {
        margin: 0 8px 0 0;
        font-weight: bold;
    }

    .rtl #institution-staff-attendances-table .sg-theme .time-view > i {
        margin: 0 0 0 8px;
        font-weight: bold;
    }

    #institution-staff-attendances-table .sg-theme .comment-wrapper {
        display: flex;
        flex-flow: row nowrap;
    }

    #institution-staff-attendances-table .sg-theme .comment-wrapper> i {
        padding: 4px;
        font-weight: bold;
    }

    #institution-staff-attendances-table .sg-theme .comment-text {
        text-overflow: ellipsis;
        white-space: normal;
        overflow: auto;
        width: 90%;
        padding: 0 4px;
        font-size: 13px;
    }

    .mobile-split-btn button.btn-default{z-index:9999!important; bottom:40px; position:fixed !important; right:15px;}

     @media screen and (max-width:667px){
              .table-wrapper ::-webkit-scrollbar {
                  -webkit-appearance: none;
              }

              .table-wrapper ::-webkit-scrollbar:vertical {
                  width: 8px;
              }

              .table-wrapper ::-webkit-scrollbar:horizontal {
                  height: 8px;
              }

              .table-wrapper ::-webkit-scrollbar-thumb {
                  background-color: rgba(0, 0, 0, .3);
                  border-radius: 10px;
                  border: 2px solid #ffffff;
              }

              .table-wrapper ::-webkit-scrollbar-track {
                  border-radius: 10px;
                  background-color: #ffffff;
              }
         }

</style>
<div class="panel">
    <div class="panel-body" style="position: relative;">
        <bg-splitter orientation="horizontal" class="content-splitter" elements="getSplitterElements" ng-init="$ctrl.institutionId=<?= $institution_id ?>; $ctrl.excelUrl='<?= $excelUrl;?>'; $ctrl.history=<?= $_history ? $_history : 0 ?>;" float-btn="false">

            <bg-pane class="main-content">
                <div class="alert {{class}}" ng-hide="message == null">
                    <a class="close" aria-hidden="true" href="#" data-dismiss="alert">×</a>{{message}}
                </div>
                <div class="overview-box alert attendance-dashboard" ng-class="disableElement" ng-show="$ctrl.action == 'view'">
                    <a data-dismiss="alert" href="#" aria-hidden="true" class="close">×</a>
                    <div class="data-section single-day" ng-show="$ctrl.selectedDay != -1">
                        <i class="kd-staff icon"></i>
                        <div class="data-field">
                            <h4><?= __('Total Staff') ?>:</h4>
                            <h1 class="data-header">{{$ctrl.totalStaff}}</h1>
                        </div>
                    </div>
                    <div class="data-section" ng-show="$ctrl.selectedDay == -1">
                        <!-- <i class="kd-address-book icon"></i> -->
                        <i class="kd-staff icon"></i>
                        <div class="data-field">
                            <h4><?= __('Total Attendance') ?></h4>
                            <h1 class="data-header">{{$ctrl.allAttendances}}</h1>
                        </div>
                    </div>
                    <div class="data-section">
                        <div class="data-field">
                            <h4><?= __('No. of Present') ?></h4>
                            <h1 class="data-header">{{$ctrl.allPresentCount}}</h1>
                        </div>
                    </div>
                    <div class="data-section">
                        <div class="data-field">
                            <h4><?= __('No. of Staff on Leave') ?></h4>
                            <h1 class="data-header">{{$ctrl.allLeaveCount}}</h1>
                        </div>
                    </div>
                </div>
                <h4>{{$ctrl.selectedFormattedDayDate}}</h4>
                <div id="institution-staff-attendances-table" class="table-wrapper">
                    <div ng-if="$ctrl.gridReady" kd-ag-grid="$ctrl.gridOptions" has-tabs="true" class="ag-height-fixed"></div>
                </div>
            </bg-pane>
            <bg-pane class="split-content splitter-slide-out splitter-filter" min-size-p="20" max-size-p="30" size-p="20">
                <div class="split-content-header">
                    <h3><?= __('Filter') ?></h3>
                </div>
                <div class="split-content-area">
                    <h5><?= __('Academic Period') ?>: </h5>
                    <div class="input-select-wrapper">
                        <select ng-disabled="$ctrl.action=='edit'" name="academic_period" ng-options="period.id as period.name for period in $ctrl.academicPeriodOptions" ng-model="$ctrl.selectedAcademicPeriod" ng-change="$ctrl.changeAcademicPeriod();">
                            <option value="" ng-if="$ctrl.academicPeriodOptions.length == 0"><?= __('No Options') ?></option>
                        </select>
                    </div>
                    <h5><?= __('Week') ?>: </h5>
                    <div class="input-select-wrapper">
                        <select ng-disabled="$ctrl.action=='edit'" name="week" ng-options="week.id as week.name for week in $ctrl.weekListOptions" ng-model="$ctrl.selectedWeek" ng-change="$ctrl.changeWeek();">
                            <option value="" ng-if="$ctrl.weekListOptions.length == 0"><?= __('No Options') ?></option>
                        </select>
                    </div>
                    <h5><?= __('Day') ?>: </h5>
                    <div class="input-select-wrapper">
                        <select ng-disabled="$ctrl.action=='edit'" name="day" ng-options="day.id as day.name for day in $ctrl.dayListOptions" ng-model="$ctrl.selectedDay" ng-change="$ctrl.changeDay();">
                            <option value="" ng-if="$ctrl.dayListOptions.length == 0"><?= __('No Options') ?></option>
                        </select>
                    </div>
                </div>
            </bg-pane>
        </bg-splitter>
    </div>
</div>
<?php
$this->end();
?>
