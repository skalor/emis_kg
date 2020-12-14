<?= $this->Html->script(['highchart-v8/highcharts', 'highchart-v8/modules/exporting', 'dashboards'], ['block' => true]); ?>
<?= $this->Html->script('app/components/alert/alert.svc', ['block' => true]); ?>
<?= $this->Html->script('Institution.angular/students/institutions.students.svc', ['block' => true]); ?>
<?= $this->Html->script('Institution.angular/students/institutions.students.ctrl', ['block' => true]); ?>
<?= $this->Html->script('statistic/chart-staff/StatisticCountStaffType', ['block' => true]); ?>
<?= $this->Html->script('statistic/chart-student/StatisticsCountGender', ['block' => true]); ?>
<?= $this->Html->script('statistic/chart-year/StatisticCountStaffYear', ['block' => true]); ?>

<?php $this->extend('OpenEmis./Layout/Panel'); ?>

<?php $this->start('panelBody'); ?>
<?php
$session = $this->request->session();
$institutionId = $session->read('Institution.Institutions.id');

$this->Html->css('ControllerAction.../plugins/datepicker/css/bootstrap-datepicker.min', ['block' => true]);
$this->Html->script('ControllerAction.../plugins/datepicker/js/bootstrap-datepicker.min', ['block' => true]);
$this->Html->script('ControllerAction.../plugins/datepicker/js/bootstrap-datepicker.ru.min', ['block' => true]);
?>
<?= $this->Html->css([
    //'owl.carousel.min', // переведено в styles.ctp
    //'owl.theme.default.min',
    //'slick',
    //'slick-theme',
    //'custom',
    //'customCSS'
]) ?>

<div class="overlay" id="overlay" style="display:none;"></div>
<input type="hidden" ng-model="InstitutionStudentController.institutionId" ng-init="InstitutionStudentController.institutionId=<?= $institutionId; ?>;"/>

<div class="institution-dashboard">
    <div class="overlay" id="overlay" style="display:none;"></div>
    <div class="header-wrapper">
        <h2 id="main-header"><?= $this->fetch('contentHeader') ?></h2>
        <div class="spacer"></div>
        <div class="header-selects"
             ng-init="InstitutionStudentController.classesData=<?= htmlspecialchars(json_encode($classes), ENT_QUOTES, 'UTF-8') ?>; InstitutionStudentController.institution_id='<?= $institution_encode ?>';">
            <div class="selectWrapper year">
                <span class="selectSpan specialSelectSpan">
                    <?php echo __('Year of study') ?>:
                </span>
                <select id="affiliation" class="selectBox specialSelect" title="<?php echo __('Level') ?>"
                        ng-if="InstitutionStudentController.academicPeriodOptions.availableOptions"
                        ng-options="item.name for item in InstitutionStudentController.academicPeriodOptions.availableOptions track by item.id"
                        ng-change="InstitutionStudentController.onChangeSelectedAcademicPeriod()"
                        ng-model="InstitutionStudentController.academicPeriodOptions.selectedOption">
                </select>
            </div>
            <div class="selectWrapper cohorts">
                <span class="selectSpan">
                    <?php echo __('Level') ?>:
                </span>
                <select class="selectBox"
                        ng-model="InstitutionStudentController.grade"
                        ng-change="InstitutionStudentController.onGradeSelect()"
                        ng-init="InstitutionStudentController.grade = ''">
                    <option ng-if="InstitutionStudentController.classesData.length > 0" value="" disabled="disabled" style="display: none;"><?=__('-- Select level --')?></option>
                    <option ng-repeat="grade in InstitutionStudentController.classesData"
                            value={{grade}}>
                        {{grade.name}}
                    </option>
                    <option value="" disabled="disabled" ng-if="InstitutionStudentController.classesData.length == 0">
                        <?php echo __('There are no records.') ?>
                    </option>
                </select>
            </div>

            <div class="selectWrapper litera" >
                <span class="selectSpan">
                    <?php echo __('Class') ?>:
                </span>
                <select class="selectBox"
                        ng-model="InstitutionStudentController.class"
                        ng-disabled="!InstitutionStudentController.selectedGrade"
                        ng-change="InstitutionStudentController.onClassSelect()"
                        ng-init="InstitutionStudentController.class = ''">
                    <option ng-if="InstitutionStudentController.classesData.length > 0" value="" disabled="disabled" style="display: none;"><?=__('-- Select class --')?></option>
                    <option ng-repeat="class in InstitutionStudentController.selectedGrade.values"
                            value={{class}}>
                        {{class.name}}
                    </option>
                    <option value="" disabled="disabled" ng-if="InstitutionStudentController.selectedGrade.values.length == 0 || InstitutionStudentController.classesData.length == 0">
                        <?php echo __('There are no records.') ?>
                    </option>
                </select>
            </div>

        </div>

    </div>
    <!-- Popup -->
    <div class="popup_for_delete" id="popupfordelete" style="display:none;">
        <div class="popup-inner">
            <h2>{{InstitutionStudentController.question.title}}</h2>
            <p>{{InstitutionStudentController.question.message}}</p>
            <input type="button" class="btn btn-danger"
                   ng-click="InstitutionStudentController.question.onOk()"
                   value="{{InstitutionStudentController.question.ok}}">
            <input type="button" class="btn btn-info" onclick="popupClose();"
                   value="{{InstitutionStudentController.question.cancel}}">
        </div>
    </div>

    <div id="dashboard-spinner" class="spinner-wrapper">
        <div class="spinner-text">
            <div class="spinner lt-ie9"></div>
            <p><?= __('Loading'); ?> ...</p>
        </div>
    </div>
    <div class="alert {{class}}" ng-hide="message == null || InstitutionStudentController.isEntryCollapsed(InstitutionStudentController.listView.entries)">
        <a class="close" aria-hidden="true" href="#" data-dismiss="alert">×</a>
        {{message}}
    </div>
    <div class="info-block" ng-if="InstitutionStudentController.listView.editData!=null" ng-repeat="listView in [InstitutionStudentController.listView]">
        <?php require(__DIR__.'/custom_student_list_view.ctp'); ?>
        <div class="row">
            <div class="col-md-12" ng-if="!listView.studentAdding" ng-init="listType=1" ng-repeat="paging in [InstitutionStudentController.listView.editData.paging]">
                <?php require(__DIR__.'/custom_student_list_view_pagination.ctp'); ?>
            </div>
        </div>

    </div>
    <div id="display_visible" ng-if="InstitutionStudentController.listView.editData==null">
        <div class="highcharts">
            <?php foreach($highChartDatas as $key => $highChartData) : ?>
                <div class="highchart dashboard-custom-chart" id="dashboard-custom-chart-<?=$key?>" style="visibility: hidden">
                    <?php echo $highChartData; ?>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</div>

<?php echo $this->element('ControllerAction.modal'); unset($this->viewVars['modals']); ?>

<?= $this->Html->script([
//    'validation',   # Переведено в scripts.ctp
//    'slick.min',
//    'owl.carousel',
//    'custom',

]) ?>
<!--<script>-->
<!--    $(function () {-->
<!--        var datepicker0 = $('#Students_start_date').datepicker({"format":"dd-mm-yyyy","todayBtn":"linked","orientation":"auto","autoclose":true});-->
<!--        var datepicker1 = $('#Students_date_of_birth').datepicker({"format":"dd-mm-yyyy","todayBtn":"linked","orientation":"auto","autoclose":true});-->
<!--        var datepicker2 = $('#Student_date_of_birth').datepicker({"format":"dd-mm-yyyy","todayBtn":"linked","orientation":"auto","autoclose":true});-->
<!--        var datepicker3 = $('#Students_transfer_start_date').datepicker({"format":"dd-mm-yyyy","todayBtn":"linked","orientation":"auto","autoclose":true});-->
<!--        $( document ).on('DOMMouseScroll mousewheel scroll', function(){-->
<!--            window.clearTimeout( t );-->
<!--            t = window.setTimeout( function(){-->
<!--                datepicker0.datepicker('place');-->
<!--                datepicker1.datepicker('place');-->
<!--                datepicker2.datepicker('place');-->
<!--                datepicker3.datepicker('place');-->
<!--        });-->
<!--    });-->
<!--    });-->
<!--</script>-->
<?php $this->end(); ?>
