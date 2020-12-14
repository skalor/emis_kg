<?php echo $this->Html->css('Statistic.multi-select-lib/css/isteven-multi-select');?>
<?php echo $this->Html->script('Statistic.angular/multi-select-lib/libs/isteven-multi-select.js');?>
<?php echo $this->Html->script('Statistic.angular/controller/statisticController.js');?>

<?php //echo $this->Html->script('Statistic.angular/components/app.component.ts');?>
<style>
    .ng-hide:not(.ng-hide-animate) {
        display: none !important;
    }

    .slide-toggle {
        overflow: hidden;
        transition: all 2.5s;
    }

    .big-fat-border {
        border: 10px solid blue;
    }

    .active{
        background-color: #69c;
        color: #fff;
    }

    .show-discrict {
        background-color: #efefef;;
    }

    .border-bottom {
        border-bottom: 1px solid #ddd;
        background: azure;
        color: black;
    }

    .organization_ {
        background: #e4e4e4;
    }

    .ng-hide:not(.ng-hide-animate) {
        /* These are just alternative ways of hiding an element */
        display: block!important;
        position: absolute;
        top: -9999px;
        left: -9999px;
    }

    .border-bottom-org {
        background: #96abcc;
    }

    .multiSelect > button {
        width: 100%;
        text-align: left;
    }
    .multiSelect .caret {
        position: absolute;
        right: 10px;
        top: 16px;
    }
    .multiSelect .buttonLabel {
        max-width: 100%;
    }
    .multiSelect .multiSelectItem {
        font-size: 14px;
    }
    .multiSelect .multiSelectItem:not(.multiSelectGroup).selected {
        color: #fff;
    }
</style>
<div  class="employees index large-9 medium-8 columns content" ng-controller="StatisticController" style="min-height: 100vh">

    <div class="container-fluid table-container" style="padding-top: 15px;">
        <span class="col-lg-2"><label class="col-lg-12"><h3><?php echo __('Statistic') ?></h3></label></span>
        <span class="col-lg-6">
            <label class="col-lg-4"><h3><?= __('Type organization') ?></h3></label>
            <span class="col-sm-12 col-lg-8" >
            <div class="row">
                <div class="col-sm-12 " style="padding-top: 10px;margin: 3px;">
                    <div
                            isteven-multi-select
                            input-model="_selectedOption"
                            output-model="selectedOrganization"
                            max-labels="1"
                            button-label="icon name"
                            item-label="icon name maker"
                            tick-property="ticked"

                            on-close="fClose()"
                            on-item-click="fClick( data )"
                            on-select-all="fSelectAll()"
                            on-select-none="fSelectNone()"
                    >
                    </div>
                </div>
            </div>
            </span>
        </span>
        <span class="col-lg-4"><label class="col-lg-12"><h3><?php echo __('Last updated statistic'); echo "<br>". date('d-m-Y H:i:s',strtotime($lastUpdateStatistic[0]));?></h3></label></span>
    </div>

    <div class="mini-box">
        <div class="container-fluid table-container fix-width"   style="width: 95%">
            <div class="accordion-title-content fixed2">
                <ul class="container-fluid ul-content background-blue">
                    <li class="col-3 col-sm-3 col-md-3 col-lg-3 text-center"><?= __('Region education') ?></li>
                    <li class="col-3 col-sm-3 col-md-3 col-lg-3 text-center"><?= __('Count organization') ?></li>
                    <li class="col-3 col-sm-3 col-md-3 col-lg-3 text-center underline"><?= __('Count employees') ?></li>
                    <li class="col-3 col-sm-3 col-md-3 col-lg-3 text-center underline"><?= __('Count students') ?></li>
                </ul>
                <ul class="container-fluid ul-content background-blue">
                    <li class="col-3 col-sm-3 col-lg-3 col-md-3 c3-po"><span>12</span></li>
                    <li class="col-3 col-sm-3 col-lg-3 col-md-3 c3-po"><span>12</span></li>
                    <li  class="col-sm-1 col-lg-1 col-md-1 col-1" ><?= __('Count total') ?></li>
                    <li  class="col-sm-1 col-lg-1 col-md-1 col-1" ><?= __('Count male') ?></li>
                    <li  class="col-sm-1 col-lg-1 col-md-1 col-1" ><?= __('Count female') ?></li>
                    <li  class="col-sm-1 col-lg-1 col-md-1 col-1" ><?= __('Count total') ?></li>
                    <li  class="col-sm-1 col-lg-1 col-md-1 col-1" ><?= __('Count boys') ?></li>
                    <li  class="col-sm-1 col-lg-1 col-md-1 col-1" ><?= __('Count girls') ?></li>
                </ul>
            </div>

            <div class='accordion-item' ng-repeat="( key_r, region ) in regions track by key_r">
                <div class='accordion-title glav-ul ' id="header-district-{{ key_r }}" data-region="{{ region.region }}"  data-tab="item-{{ key_r }}">
                    <div class='container-fluid ul-content regions-sel {{ region._active }}' ng-init="showDistrict( region,key_r );" id="header-region-{{ key_r }}" >
                        <li class='col-3 col-sm-3 col-lg-3 col-md-3' ng-click="showDistrict( region,key_r );">{{ region.region }}</li>
                        <li class='col-3 col-sm-3 col-lg-3 col-md-3' ng-click="showDistrict( region,key_r );">{{ region['org'] | spases_number}}</li>
                        <li class='col-1 col-sm-1 col-lg-1 col-md-1' ng-click="showDistrict( region,key_r );">{{ region['staff_total_count'] | spases_number}}</li>
                        <li class='col-1 col-sm-1 col-lg-1 col-md-1' ng-click="showDistrict( region,key_r );">{{ region['staff_male_count'] | spases_number}}</li>
                        <li class='col-1 col-sm-1 col-lg-1 col-md-1' ng-click="showDistrict( region,key_r );">{{ region['staff_female_count'] | spases_number}}</li>
                        <li class='col-1 col-sm-1 col-lg-1 col-md-1' ng-click="showDistrict( region,key_r );">{{ region['student_total_count'] | spases_number}}</li>
                        <li class='col-1 col-sm-1 col-lg-1 col-md-1' ng-click="showDistrict( region,key_r );">{{ region['student_male_count'] | spases_number}}</li>
                        <li class='col-1 col-sm-1 col-lg-1 col-md-1' ng-click="showDistrict( region,key_r );">{{ region['student_female_count'] | spases_number}}</li>
                    </div>
                    <div ng-hide="region._hidden" ng-init="tmp_key = key_r+key_d.toString();" ng-animate="{show: 'show-animation', hide: 'hide-animation'}" id="header-district-{{ key_d }}" data-tab="{{ tmp_key }}" class='accordion-item ng-hide district' ng-repeat="( key_d, district ) in districts[region.region] track by key_d">
                        <ul class='container-fluid ul-content' >
                            <li class='col-3 col-sm-3 col-lg-3 col-md-3' ng-click="_showOrganizations( district.name, tmp_key )">{{ district.name }}</li>
                            <li class='col-3 col-sm-3 col-lg-3 col-md-3' ng-click="_showOrganizations( district.name, tmp_key )">{{ district['org'] | spases_number}}</li>
                            <li class='col-1 col-sm-1 col-lg-1 col-md-1' ng-click="_showOrganizations( district.name, tmp_key )">{{ district['staff_total_count'] | spases_number}}</li>
                            <li class='col-1 col-sm-1 col-lg-1 col-md-1' ng-click="_showOrganizations( district.name, tmp_key )">{{ district['staff_male_count'] | spases_number }}</li>
                            <li class='col-1 col-sm-1 col-lg-1 col-md-1' ng-click="_showOrganizations( district.name, tmp_key )">{{ district['staff_female_count'] | spases_number }}</li>
                            <li class='col-1 col-sm-1 col-lg-1 col-md-1' ng-click="_showOrganizations( district.name, tmp_key )">{{ district['student_total_count'] | spases_number }}</li>
                            <li class='col-1 col-sm-1 col-lg-1 col-md-1' ng-click="_showOrganizations( district.name, tmp_key )">{{ district['student_male_count'] | spases_number }}</li>
                            <li class='col-1 col-sm-1 col-lg-1 col-md-1' ng-click="_showOrganizations( district.name, tmp_key )">{{ district['student_female_count'] | spases_number }}</li>
                        </ul>
                        <div id="item-{{ tmp_key }}" class="organization_struct"></div>
                    </div>
                    <!--                    <div class='accordion-item' ng-hide="region._hidden">-->
                    <!--                        <div class='accordion-title' id="header-district-{{ key_r }}" data-region="{{ region.region }}"  data-tab="item-{{ key_r }}">-->
                    <!--                            <ul class='container-fluid ul-content regions-sel {{ region._active }}' id="header-region-{{ key_r }}" ng-init="region._hidden = true;region._active = ''">-->
                    <!--                                <li class='col-3 col-sm-3 col-lg-3 col-md-3' >--><?php //echo __('Total') ?><!--</li>-->
                    <!--                                <li class='col-3 col-sm-3 col-lg-3 col-md-3' >{{ _regions[region.region]['org'] | spases_number}}</li>-->
                    <!--                                <li class='col-1 col-sm-1 col-lg-1 col-md-1' >{{ _regions[region.region]['staff_total_count'] | spases_number}}</li>-->
                    <!--                                <li class='col-1 col-sm-1 col-lg-1 col-md-1' >{{ _regions[region.region]['staff_male_count'] | spases_number}}</li>-->
                    <!--                                <li class='col-1 col-sm-1 col-lg-1 col-md-1' >{{ _regions[region.region]['staff_female_count'] | spases_number}}</li>-->
                    <!--                                <li class='col-1 col-sm-1 col-lg-1 col-md-1' >{{ _regions[region.region]['student_total_count'] | spases_number}}</li>-->
                    <!--                                <li class='col-1 col-sm-1 col-lg-1 col-md-1' >{{ _regions[region.region]['student_male_count'] | spases_number}}</li>-->
                    <!--                                <li class='col-1 col-sm-1 col-lg-1 col-md-1' >{{ _regions[region.region]['student_female_count'] | spases_number}}</li>-->
                    <!--                            </ul>-->
                    <!--                        </div>-->
                    <!--                    </div>-->
                </div>
            </div>
            <div class='accordion-item'>
                <div class='accordion-title active'>
                    <ul class='container-fluid ul-content regions-sel {{ region._active }}' id="header-region-{{ key_r }}">
                        <li class='col-3 col-sm-3 col-lg-3 col-md-3' ><?php echo __('Total') ?></li>
                        <li class='col-3 col-sm-3 col-lg-3 col-md-3' >{{ region_total['org'] | spases_number }}</li>
                        <li class='col-1 col-sm-1 col-lg-1 col-md-1' >{{ region_total['staff_total_count'] | spases_number}}</li>
                        <li class='col-1 col-sm-1 col-lg-1 col-md-1' >{{ region_total['staff_male_count'] | spases_number}}</li>
                        <li class='col-1 col-sm-1 col-lg-1 col-md-1' >{{ region_total['staff_female_count'] | spases_number}}</li>
                        <li class='col-1 col-sm-1 col-lg-1 col-md-1' >{{ region_total['student_total_count'] | spases_number}}</li>
                        <li class='col-1 col-sm-1 col-lg-1 col-md-1' >{{ region_total['student_male_count'] | spases_number}}</li>
                        <li class='col-1 col-sm-1 col-lg-1 col-md-1' >{{ region_total['student_female_count'] | spases_number}}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!--<script>-->
<!--    $(".accordion-title").click(function(e){-->
<!--        var accordionitem = $(this).attr("data-tab");-->
<!--        $("#"+accordionitem).slideToggle().parent().siblings().find(".accordion-content").slideUp();-->
<!---->
<!--        $(this).toggleClass("active-title");-->
<!--        $("#"+accordionitem).parent().siblings().find(".accordion-title").removeClass("active-title");-->
<!---->
<!--        $("i.fa-chevron-down",this).toggleClass("chevron-top");-->
<!--        $("#"+accordionitem).parent().siblings().find(".accordion-title i.fa-chevron-down").removeClass("chevron-top");-->
<!--    });-->
<!--</script>-->
<!--<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>-->
</div>


