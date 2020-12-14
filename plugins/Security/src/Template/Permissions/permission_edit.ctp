<?= $this->Html->script('app/components/alert/alert.svc', ['block' => true]); ?>
<?= $this->Html->script('Security.angular/permission/security.permission.edit.svc', ['block' => true]); ?>
<?= $this->Html->script('Security.angular/permission/security.permission.edit.ctrl', ['block' => true]); ?>
<?= $this->Html->css('Security.permission', ['block' => true]); ?>
<?php
$this->extend('OpenEmis./Layout/Panel');
$this->start('toolbar');
?>

<?= $this->Html->link('<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M22.404 11.1517H7.18845L14.1773 4.16275L12.402 2.3999L2.39999 12.4019L12.402 22.4039L14.1648 20.6411L7.18845 13.6522H22.404V11.1517Z" fill="#004A51"></path> </svg>', $viewUrl, ['class' => 'btn btn-xs btn-default', 'data-toggle' => 'tooltip', 'data-placement' => 'bottom', 'data-container' => 'body', 'title' => __('Back'), 'escapeTitle' => false, 'id' => 'back_url']) ?>
<?= $this->Html->link('<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"> <path fill-rule="evenodd" clip-rule="evenodd" d="M2.75 6C2.75 5.17 3.42 4.5 4.25 4.5C5.08 4.5 5.75 5.17 5.75 6C5.75 6.83 5.08 7.5 4.25 7.5C3.42 7.5 2.75 6.83 2.75 6ZM2.75 12C2.75 11.17 3.42 10.5 4.25 10.5C5.08 10.5 5.75 11.17 5.75 12C5.75 12.83 5.08 13.5 4.25 13.5C3.42 13.5 2.75 12.83 2.75 12ZM4.25 16.5C3.42 16.5 2.75 17.18 2.75 18C2.75 18.82 3.43 19.5 4.25 19.5C5.07 19.5 5.75 18.82 5.75 18C5.75 17.18 5.08 16.5 4.25 16.5ZM21.25 19H7.25V17H21.25V19ZM7.25 13H21.25V11H7.25V13ZM7.25 7V5H21.25V7H7.25Z" fill="#FF6C6C"/> </svg>', $indexUrl, ['class' => 'btn btn-xs btn-default', 'data-toggle' => 'tooltip', 'data-placement' => 'bottom', 'data-container' => 'body', 'title' => __('List'), 'escapeTitle' => false]) ?>

<?php
$this->end();
$this->start('panelBody');
?>
    <form accept-charset="utf-8" id="content-main-form" class="form-horizontal ng-pristine ng-valid"
          novalidate="novalidate"
          ng-controller="SecurityPermissionEditCtrl as SecurityPermissionEditController">
        <div class="alert {{SecurityPermissionEditController.class}}"
             ng-hide="SecurityPermissionEditController.message == null">
            <a class="close" aria-hidden="true" href="#" data-dismiss="alert">×</a>{{SecurityPermissionEditController.message}}
        </div>
        <div class="SecurityPermissionEditController"
                ng-init="SecurityPermissionEditController.roleId=<?= $roleId ?>; SecurityPermissionEditController.redirectUrl='<?= $this->Url->build($viewUrl) ?>'; SecurityPermissionEditController.alertUrl='<?= $this->Url->build($alertUrl) ?>'; SecurityPermissionEditController.moduleKey='<?= $moduleKey ?>'">
            <div class="scrolltabs sticky-content">
                <scrollable-tabset show-tooltips="false" show-drop-down="false">
                    <uib-tabset justified="true">
                        <uib-tab heading="{{module.name}}"
                                 ng-repeat="module in SecurityPermissionEditController.modules"
                                 ng-click="SecurityPermissionEditController.changeModule(module)">
                        </uib-tab>
                    </uib-tabset>
                    <div class="tabs-divider"></div>
                </scrollable-tabset>
                <div class="section-header security-permission-checkbox"
                     ng-repeat-start="(key, section) in SecurityPermissionEditController.pageSections">
                    <input
                            class="no-selection-label"
                            kd-checkbox-radio={{section.name}}
                            type="checkbox"
                            ng-true-value="1"
                            ng-false-value="0"
                            ng-model="section.enabled"
                            ng-change="SecurityPermissionEditController.checkAllInSection(key);">
                </div>
                <div class="table-wrapper" ng-repeat-end>
                    <div class="table-responsive">
                            <table class="table table-curved">
                                <thead>
                                <th style="width: 300px"><?= __('Function') ?></th>
                                <th class="center"><?= __('View') ?></th>
                                <th class="center"><?= __('Edit') ?></th>
                                <th class="center"><?= __('Add') ?></th>
                                <th class="center"><?= __('Delete') ?></th>
                                <th class="center"><?= __('Execute') ?></th>
                                <th class="center"><?= __('Fields') ?></th>
                                </thead>
                                <tbody ng-repeat="function in section.items">
                                    <tr>
                                        <td>{{function.name}}
                                            <i class="fa fa-info-circle fa-lg fa-right icon-blue" tooltip-placement="right"
                                               uib-tooltip={{function.description}} tooltip-append-to-body="true"
                                               tooltip-class="tooltip-blue" ng-hide="function.description==null;"></i>
                                        <td class="center"><input class="no-selection-label" kd-checkbox-radio type="checkbox"
                                                                  ng-true-value="1" ng-false-value="0"
                                                                  ng-model="function.Permissions._view"
                                                                  ng-disabled="function._view==null;"
                                                                  ng-change="SecurityPermissionEditController.changePermission(function, 'view', function.Permissions._view);">
                                        </td>
                                        <td class="center"><input class="no-selection-label" kd-checkbox-radio type="checkbox"
                                                                  ng-true-value="1" ng-false-value="0"
                                                                  ng-model="function.Permissions._edit"
                                                                  ng-disabled="function._edit==null;"
                                                                  ng-change="SecurityPermissionEditController.changePermission(function, 'edit', function.Permissions._edit);">
                                        </td>
                                        <td class="center"><input class="no-selection-label" kd-checkbox-radio type="checkbox"
                                                                  ng-true-value="1" ng-false-value="0"
                                                                  ng-model="function.Permissions._add"
                                                                  ng-disabled="function._add==null;"
                                                                  ng-change="SecurityPermissionEditController.changePermission(function, 'add', function.Permissions._add);">
                                        </td>
                                        <td class="center"><input class="no-selection-label" kd-checkbox-radio type="checkbox"
                                                                  ng-true-value="1" ng-false-value="0"
                                                                  ng-model="function.Permissions._delete"
                                                                  ng-disabled="function._delete==null;"
                                                                  ng-change="SecurityPermissionEditController.changePermission(function, 'delete', function.Permissions._delete);">
                                        </td>
                                        <td class="center"><input class="no-selection-label" kd-checkbox-radio type="checkbox"
                                                                  ng-true-value="1" ng-false-value="0"
                                                                  ng-model="function.Permissions._execute"
                                                                  ng-disabled="function._execute==null;"
                                                                  ng-change="SecurityPermissionEditController.changePermission(function, 'execute', function.Permissions._execute);">
                                        </td>
                                        <td class="center open-fields" ng-if="isNotHiddable(function.id)">
                                            <button ng-click="toggle(function.id)"><i ng-class="function.id == showContainer ? 'fa fa-arrow-up' : 'fa fa-arrow-down'"></i></button>
                                        </td>
                                    </tr>

                                    <tr ng-if="function.id == showContainer" class="hider">
                                        <td>
                                            <label for="institution_type"><?= __('Choose an institution type') ?>:</label>
                                            <select id="institution_type" name="institution_type" ng-model="data.institutionType" ng-change="showFields(function.id)">
                                                <?php foreach($institutionTypes as $type) { ?>
                                                    <option value="<?= $type->id ?>"><?= $type->name ?></option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                        <td class="" colspan="6">
                                            <div class="dots-box">
                                                <ul>
                                                    <li><i class="fa fa-circle red-circle" aria-hidden="true"></i> <span><?= __('Invisible') ?></span></li>
                                                    <li><i class="fa fa-circle orange-circle" aria-hidden="true"></i> <span><?= __('Visible') ?></span></li>
                                                    <li><i class="fa fa-circle green-circle" aria-hidden="true"></i> <span><?= __('Editable') ?></span></li>
                                                </ul>
                                            </div></td>
                                    </tr>

                                    <tr class="toggler" ng-if="function.id == showContainer && fields.length !== 0">
                                        <td class="row" colspan="6">
                                            <section class="checkbox_block col-md-4" ng-repeat="field in fields">
                                                <div class="switch" ng-class="{switch: true, required: field.required}">
                                                    <input name="{{field.name}}" id="invisible-{{field.name}}" class="one" type="radio" ng-disabled="field.required" value="index" ng-model="field.state" ng-click="changed(field)"/>
                                                    <label for="invisible-{{field.name}}" class="switch__label"></label>
                                                    <input name="{{field.name}}" id="read-{{field.name}}" class="two" type="radio" ng-disabled="field.required" value="view" ng-model="field.state" ng-click="changed(field)"/>
                                                    <label for="read-{{field.name}}" class="switch__label"></label>
                                                    <input name="{{field.name}}" id="edit-{{field.name}}" class="three" type="radio" ng-disabled="field.required" value="edit" ng-model="field.state" ng-click="changed(field)"/>
                                                    <label for="edit-{{field.name}}" class="switch__label"></label>
                                                    <div class="switch__indicator"></div>
                                                </div>
                                                <label>{{field.label}}</label>
                                            </section>
                                            <div class="col-md-12 buttons">
                                                <button class="btn btn-primary" ng-click="saveFields(function.id)"><?= __('Save') ?></button>
                                                <button class="btn btn-primary" ng-click="hide()"><?= __('Cancel') ?></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                    </div>
                </div>
                <div class="form-buttons" ng-show="SecurityPermissionEditController.ready">
                    <div class="button-label"></div>
                    <button class="btn btn-default btn-save" type="button"
                            ng-click="SecurityPermissionEditController.postForm();">
                        <i class="fa fa-check"></i> <?= __('Save') ?>
                    </button>
                    <?= $this->Html->link('<i class="fa fa-close"></i> ' . __('Cancel'), $viewUrl, ['class' => 'btn btn-outline btn-cancel', 'escapeTitle' => false]) ?>
                </div>
            </div>
        </div>
    </form>
<?php
$this->end();
?>
