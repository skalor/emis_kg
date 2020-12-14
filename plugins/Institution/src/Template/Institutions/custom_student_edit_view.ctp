<form data-toggle="validator"
      role="form"
      class="tab-pane fade in active container-fluid"
      onsubmit="return false;"
      novalidate="novalidate"
      ng-if="!entry.editData.iframeUrl">
    <div class="row">
        <div
            ng-repeat="field in entry.editData.data_response_attr"
            ng-init="fieldName = field.attr.field;fieldValue = entry.editData.data_response[field.attr.field]"
            ng-hide="field.attr.type == 'hidden'"
            ng-if="field.attr.type != 'image' && field.attr.type != 'binary'"
            ng-class="{
                'col-sm-12 col-12 col-lg-12 col-md-12':field.attr.type == 'section',
                'col-sm-12 col-12 col-lg-3 col-md-6 l': field.attr.type != 'section',
                'required': field.attr.null === false,
                'has-error': !!InstitutionStudentController.postResponse.error[fieldName]
            }"
            class="form-group"
            data-type="{{field.type}}">

            <div ng-if="field.attr.type == 'section'"
                 class="section-header">
                {{field.attr.title}}
            </div>

            <label ng-if="field.attr.type != 'section'"
                   class="control-label" for="name-{{field.attr.field}}">{{field.attr.label}}</label>

            <select ng-if="field.attr.type=='select' || !!field.option.value"
                    ng-model="entry.editData.data_response[fieldName]"
                    ng-disabled="field.type == 'readonly'"
                    ng-options="option.id as option.name for option in InstitutionStudentController.getOptions(field, entry.editData.data_response[fieldName])"
                    class="form-control form_control form_control_select"
                    id="name-{{field.attr.field}}"
                    name="{{field.attr.field}}"
                    data-error=""
                    ng-required="field.attr.null === false">
                <option value="" >-- <?= __('Select') ?> --</option>
            </select>

            <div class="tree-form"
                 id="areapicker-{{fieldName}}"
                 ng-if="field.type == 'areapicker'"
                 ng-controller="SgTreeCtrl as SgTree"
                 ng-init="
                    SgTree.model=field.attr.source_model;
                    SgTree.outputValue=fieldValue;
                    SgTree.userId=<?= $this->request->session()->read('Auth.User.id') ?>;
                    SgTree.displayCountry=1;
                    SgTree.triggerOnChange=false;
                ">
                <kd-tree-dropdown-ng
                    id="{{fieldName}}-tree"
                    expand-parent="SgTree.triggerLoad(refreshList)"
                    output-model="outputModelText"
                    model-type="single"
                    text-config="textConfig"/>
                <input type="hidden"
                       ng-model="entry.editData.data_response[fieldName]"
                       ng-init="entry.editData.data_response[fieldName]=SgTree.outputValue"
                       name="{{field.attr.field}}"
                       />
            </div>

            <div ng-if="field.type == 'date'"
                 class="input-group date "
                 id="group-{{field.attr.field}}">
                <input type="text"
                       class="form-control form_control datepicker-height"
                       ng-model="entry.editData.data_response[fieldName]"
                       ng-init="InstitutionStudentController.initDate('#group-'+field.attr.field, fieldValue)"
                       id="name-{{field.attr.field}}"
                       name="{{field.attr.field}}"
                       ng-required="field.attr.null === false">
                <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
            </div>

            <textarea ng-if="field.type == 'text' || field.type == 'textarea'"
                      ng-model="entry.editData.data_response[fieldName]"
                      class="form-control form_control"
                      id="name-{{field.attr.field}}"
                      name="{{field.attr.field}}"
                      data-error=""
                      ng-required="field.attr.null === false"
                      ng-maxlength="field.attr.length">
                                </textarea>

            <input ng-if="field.type == 'string' || field.type == 'hidden' || !field.option.value && field.type == 'readonly'"
                   ng-model="entry.editData.data_response[fieldName]"
                   ng-readonly="field.type == 'readonly'"
                   class="form-control form_control"
                   id="name-{{field.attr.field}}"
                   name="{{field.attr.field}}"
                   type="text"
                   data-error=""
                   ng-required="field.attr.null === false"
                   ng-maxlength="field.attr.length">

            <input ng-if="field.type == 'integer'"
                   ng-model="entry.editData.data_response[fieldName]"
                   class="form-control form_control"
                   id="name-{{field.attr.field}}"
                   name="{{field.attr.field}}"
                   type="number"
                   data-error=""
                   ng-required="field.attr.null === false">

            <input ng-if="field.type == 'password'"
                   ng-model="entry.editData.data_response[fieldName]"
                   ng-init="entry.editData.data_response[fieldName]=''"
                   class="form-control form_control"
                   id="name-{{field.attr.field}}"
                   name="{{field.attr.field}}"
                   type="password"
                   data-error=""
                   ng-required="field.attr.null === false">

            <select ng-if="field.attr.type=='custom_dropdown'"
                    ng-model="entry.editData.data_response.custom_field_values[field.option.seq].number_value"
                    ng-options="option.id as option.name for option in InstitutionStudentController.getOptions(field, entry.editData.data_response[fieldName])"
                    class="form-control form_control form_control_select"
                    id="name-{{fieldName}}"
                    name="{{fieldName}}"
                    data-error=""
                    ng-required="field.attr.null === false">
                <option value="" >-- <?= __('Select') ?> --</option>
            </select>

            <input ng-if="field.type == 'custom_text'"
                   ng-model="entry.editData.data_response.custom_field_values[field.option.seq].text_value"
                   class="form-control form_control"
                   id="name-{{fieldName}}"
                   name="{{fieldName}}"
                   type="text"
                   data-error=""
                   ng-required="field.attr.null === false"
                   ng-maxlength="field.attr.length"/>
            <div ng-if="field.attr.type != 'section'"
                 class="help-block with-errors">
                <ul class="list-unstyled">
                    <li ng-repeat="error in InstitutionStudentController.postResponse.error[fieldName]">{{ error }}</li>
                </ul>
            </div>
        </div>
        <br>
        <br>
        <div class="col-sm-12 col-12 col-lg-12 col-md-12 form-group" style="">
            <button ng-click="InstitutionStudentController.saveEntry(entry)"
                    type="submit" class="btn btn-primary form_control btn-save"
                    style="margin-bottom: 0;margin-top: 20px">
                <?=__('Save')?>
            </button>
            <button ng-click="InstitutionStudentController.cancelEntry(entry)"
                    type="reset" class="btn btn-primary form_control btn-cancel"
                    style="margin-bottom: 0;margin-top: 20px">
                <?=__('Cancel')?>
            </button>
        </div>
    </div>
</form>
<div ng-if="!!entry.editData.iframeUrl">
<script>
    function iframeMainEditLoad(iframe) {
        $(iframe).next('input').next('input').next('input').click();
        $('.btn-cancel',iframe.contentWindow.document).click(function(){
            $(iframe).next('input').click();
            return false;
        });
        $('.iframe-close',iframe.contentWindow.document).click(function(){
            $(iframe).next('input').next('input').click();
            $(iframe).next('input').click();
            return false;
        });
    }
</script>
<iframe class="iframe-edit-view" ng-src="{{entry.editData.iframeUrl | trustUrl}}" height="500px" width="100%" onload="iframeMainEditLoad(this)"> <!--content--> </iframe>
<input type="hidden" ng-click="entry.editData.iframeUrl = null;InstitutionStudentController.cancelEntry(entry);"/>
<input type="hidden" ng-click="InstitutionStudentController.selectModel(entry, model);"/>
<input type="hidden" ng-click="InstitutionStudentController.isAppendLoader(false);"/>
</div>
