<ul class="nav nav-tabs">
    <li class="active mobile-tab-label hide"
        ng-repeat="(navKey, nav) in entry.editData.navigations"
        ng-if="nav.active"
        ng-click="entry.editData.optionVisible = !entry.editData.optionVisible;">
        <a data-toggle="tab" href="#{{navKey}}">{{nav.title}}</a>
        <span class="mobile-tab-dropdown fa"
              ng-class="{'fa-angle-down':!entry.editData.optionVisible, 'fa-angle-up': !!entry.editData.optionVisible}"></span>
    </li>
    <li class="mobile-tab-option"
        ng-repeat="(navKey, nav) in entry.editData.navigations"
        ng-class="{active: nav.active, 'mobile-tab-option-visible': !!entry.editData.optionVisible}"
        ng-click="entry.editData.optionVisible = false; InstitutionStudentController.selectNavigation(entry, nav);">
        <a data-toggle="tab" href="#{{navKey}}">{{nav.title}}</a>
    </li>
</ul>
<div class="tab-content">
    <div
        ng-repeat="(navKey, nav) in entry.editData.navigations"
        ng-class="{active: nav.active}"
        id="{{navKey}}"
        class="tab-pane fade in">
        <div ng-if="nav.active && entry.editData.tabElementsLength > 0">
            <ul class="nav nav-tabs" ng-init="InstitutionStudentController.getTabContent(entry)">
                <li class="active mobile-tab-label hide"
                    ng-repeat="(modelKey, model) in entry.editData.tabElements"
                    ng-class="{success: model.data!=null && model.data.paging != null,
                               secondary: model.data!=null && model.data.paging != null && model.data.paging.count == 0,
                               warning: model.data!=null && model.data.paging != null && model.data.paging.count > 0}"
                    ng-if="model.active"
                    ng-click="nav.optionVisible = !nav.optionVisible;">
                    <span class="label" ng-show="model.data != null && model.data.paging != null">{{model.data.paging.count}}</span>
                    <a data-toggle="tab" href="#{{modelKey}}">{{model.text}}</a>
                    <span class="mobile-tab-dropdown fa"
                          ng-class="{'fa-angle-down':!nav.optionVisible, 'fa-angle-up': !!nav.optionVisible}"></span>
                </li>
                <li class="mobile-tab-option"
                    ng-repeat="(modelKey, model) in entry.editData.tabElements"
                    ng-click="nav.optionVisible = false; InstitutionStudentController.selectModel(entry, model);"
                    ng-class="{active: model.active,
                               success: model.data!=null && model.data.paging != null,
                               secondary: model.data!=null && model.data.paging != null && model.data.paging.count == 0,
                               warning: model.data!=null && model.data.paging != null && model.data.paging.count > 0,
                               'mobile-tab-option-visible': !!nav.optionVisible}">
                    <span class="label" ng-show="model.data != null && model.data.paging != null">{{model.data.paging.count}}</span>
                    <a data-toggle="tab" href="#{{modelKey}}">{{model.text}}</a>
                </li>
            </ul>
            <div ng-repeat="(modelKey, model) in entry.editData.tabElements"
                 ng-if="model.active"
                 id="{{modelKey}}"
                 class="tab-content">
                <div class="alert {{class}}" ng-hide="message == null">
                    <a class="close" aria-hidden="true" href="#" data-dismiss="alert">×</a>
                    {{message}}
                </div>
                <div ng-if="model.active && entry.editData.action == 'edit'">
                    <?php require(__DIR__.'/custom_student_edit_view.ctp'); ?>
                </div>
                <div ng-if="model.active && entry.editData.action == 'index'">
                    <?php require(__DIR__.'/custom_student_detail_list_view.ctp'); ?>
                    <div class="row">
                        <div class="col-md-12" ng-init="listType=2" ng-repeat="paging in [entry.editData.paging]">
                            <?php require(__DIR__.'/custom_student_list_view_pagination.ctp'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div ng-if="nav.active && entry.editData.tabElementsLength === 0">
            <div class="tab-content">
                <div class="alert {{class}}" ng-hide="message == null">
                    <a class="close" aria-hidden="true" href="#" data-dismiss="alert">×</a>
                    {{message}}
                </div>
                <div ng-if="entry.editData.action == 'edit'">
                    <?php require(__DIR__.'/custom_student_edit_view.ctp'); ?>
                </div>
                <div ng-if="entry.editData.action == 'index'">
                    <?php require(__DIR__.'/custom_student_detail_list_view.ctp'); ?>
                    <div class="row">
                        <div class="col-md-12" ng-init="listType=2" ng-repeat="paging in [entry.editData.paging]">
                            <?php require(__DIR__.'/custom_student_list_view_pagination.ctp'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
