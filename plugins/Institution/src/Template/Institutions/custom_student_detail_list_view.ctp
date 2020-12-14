<table class="table custom-table" ng-if="detailListView = entry.listView" ng-hide="detailListView.headers.titles.length === 0">
    <thead ng-if="!entry.editData.moreAction">
        <tr class="head-table">
            <th colspan="{{detailListView.headers.titles.length + !!detailListView.headers.actionTitle - 1}}">
                <div class="input-group customImageButtons">
                    <div class="btn customAdd" type="button" ng-if="detailListView.actions.add" ng-click="InstitutionStudentController.detailRecordAction(detailListView.actions.add, entry, model)">
                        <span data-placement="bottom" data-toggle="tooltip" data-original-title="<?=__('Add')?>">
                            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"> <path fill-rule="evenodd" clip-rule="evenodd" d="M12.0418 9.95792H21.4168V12.0413H12.0418V21.4162H9.95847V12.0413H0.583496V9.95792H9.95847V0.582947H12.0418V9.95792Z" fill="white"></path> </svg>
                        </span>
                    </div>
                    <div class="btn btn-default" type="button" ng-if="detailListView.actions.export" ng-click="InstitutionStudentController.detailRecordAction(detailListView.actions.export, entry, model)">
                        <span data-placement="bottom" data-toggle="tooltip" data-original-title="<?=__('Export')?>">
                            <svg width="20" height="22" viewBox="0 0 20 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M16.9669 8.95441L10.1596 0.109985L3.1118 8.76391L7.28895 8.82134L8.27641 14.9772L11.4761 15.0212L12.6324 8.89481L16.9669 8.95441Z" fill="#009966"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M17.5428 12.7758V20.2758C17.5428 21.1963 16.7966 21.9425 15.8761 21.9425H4.20945C3.28898 21.9425 2.54279 21.1963 2.54279 20.2758V12.7758H4.20945V20.2758H15.8761V12.7758H17.5428Z" fill="#564242"/>
                            </svg>
                        </span>
                    </div>
                    <div class="btn btn-default" type="button" ng-if="detailListView.actions.promotion" ng-click="InstitutionStudentController.detailRecordAction(detailListView.actions.promotion, entry, model)">
                        <span data-placement="bottom" data-toggle="tooltip" data-original-title="<?=__('Promotion / Graduation')?>">
                            <svg width="35" height="20" viewBox="0 0 35 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17.0711 12.9289C15.9819 11.8398 14.6855 11.0335 13.2711 10.5454C14.786 9.50199 15.7812 7.75578 15.7812 5.78125C15.7812 2.59348 13.1878 0 10 0C6.81223 0 4.21875 2.59348 4.21875 5.78125C4.21875 7.75578 5.21402 9.50199 6.72898 10.5454C5.31453 11.0335 4.01813 11.8398 2.92895 12.9289C1.0402 14.8177 0 17.3289 0 20H1.5625C1.5625 15.3475 5.34754 11.5625 10 11.5625C14.6525 11.5625 18.4375 15.3475 18.4375 20H20C20 17.3289 18.9598 14.8177 17.0711 12.9289ZM10 10C7.67379 10 5.78125 8.1075 5.78125 5.78125C5.78125 3.455 7.67379 1.5625 10 1.5625C12.3262 1.5625 14.2188 3.455 14.2188 5.78125C14.2188 8.1075 12.3262 10 10 10Z" fill="#004A51"/>
                                <path d="M27.5002 10L25.8336 19.1667L22.5002 19.1667L20.8336 10L27.5002 10Z" fill="#009966"/>
                                <path d="M24.1668 1.66669L31.3837 10.4167H16.95L24.1668 1.66669Z" fill="#009966"/>
                            </svg>
                        </span>
                    </div>
                    <div class="btn btn-default" type="button" ng-if="detailListView.actions.transfer" ng-click="InstitutionStudentController.detailRecordAction(detailListView.actions.transfer, entry, model)">
                        <span data-placement="bottom" data-toggle="tooltip" data-original-title="<?=__('Transfer')?>">
                            <svg width="40" height="20" viewBox="0 0 40 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17.0711 12.9289C15.9819 11.8398 14.6855 11.0335 13.2711 10.5454C14.786 9.50199 15.7812 7.75578 15.7812 5.78125C15.7812 2.59348 13.1878 0 10 0C6.81223 0 4.21875 2.59348 4.21875 5.78125C4.21875 7.75578 5.21402 9.50199 6.72898 10.5454C5.31453 11.0335 4.01813 11.8398 2.92895 12.9289C1.0402 14.8177 0 17.3289 0 20H1.5625C1.5625 15.3475 5.34754 11.5625 10 11.5625C14.6525 11.5625 18.4375 15.3475 18.4375 20H20C20 17.3289 18.9598 14.8177 17.0711 12.9289ZM10 10C7.67379 10 5.78125 8.1075 5.78125 5.78125C5.78125 3.455 7.67379 1.5625 10 1.5625C12.3262 1.5625 14.2188 3.455 14.2188 5.78125C14.2188 8.1075 12.3262 10 10 10Z" fill="#004A51"/>
                                <path d="M39.1667 10L30.4167 17.2169L30.4167 2.78314L39.1667 10Z" fill="#009966"/>
                                <path d="M21.6667 17.5C21.752 9.94392 23.4467 7.3513 30.4167 6.66667L30.4167 13.3333C26.0669 12.8465 24.2592 14.0035 21.6667 17.5Z" fill="#009966"/>
                            </svg>
                        </span>
                    </div>
                    <div class="btn btn-default" type="button" ng-if="detailListView.actions.import" ng-click="InstitutionStudentController.detailRecordAction(detailListView.actions.import, entry, model)">
                        <span data-placement="bottom" data-toggle="tooltip" data-original-title="<?=__('Import')?>">
                            <svg width="20" height="23" viewBox="0 0 20 23" fill="none" xmlns="http://www.w3.org/2000/svg"> <path fill-rule="evenodd" clip-rule="evenodd" d="M17.5 12.1667V19.6667C17.5 20.5871 16.7538 21.3333 15.8333 21.3333H4.16667C3.24619 21.3333 2.5 20.5871 2.5 19.6667V12.1667H4.16667V19.6667H15.8333V12.1667H17.5Z" fill="#293845"></path> <path d="M6.8 8.64166L8.4 0.849998L11.6 0.849999L13.2 8.64166L6.8 8.64166Z" fill="#009966"></path> <path d="M10 15.725L3.0718 8.28751L16.9282 8.28751L10 15.725Z" fill="#009966"></path> </svg>
                        </span>
                    </div>
                    <div class="btn btn-default" type="button" ng-if="detailListView.actions.undo" ng-click="InstitutionStudentController.detailRecordAction(detailListView.actions.undo, entry, model)">
                        <span data-placement="bottom" data-toggle="tooltip" data-original-title="<?=__('Cancel')?>">
                            <svg width="20" height="22" viewBox="0 0 20 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M16.9669 8.95441L10.1596 0.109985L3.1118 8.76391L7.28895 8.82134L8.27641 14.9772L11.4761 15.0212L12.6324 8.89481L16.9669 8.95441Z" fill="#009966"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M17.5428 12.7758V20.2758C17.5428 21.1963 16.7966 21.9425 15.8761 21.9425H4.20945C3.28898 21.9425 2.54279 21.1963 2.54279 20.2758V12.7758H4.20945V20.2758H15.8761V12.7758H17.5428Z" fill="#564242"/>
                            </svg>
                        </span>
                    </div>
                    <br>
                    <form action="" class="header-form content-main-form-default" id="content-main-form-2"
                          ng-if="detailListView.actions.search"
                          ng-submit="InstitutionStudentController.onClickSearch2($event)">
                        <input id="search_input-2" type="text" class="search-input form-control" name=""
                               placeholder="Поиск">
                        <button class="btn btn-xs btn-reset" type="button" onclick="$('#search_input-2').val('');$('#content-main-form-2').submit()" style="margin-right: 35px !important;">
                            <i class="fa fa-close"></i>
                        </button>
                        <button class="d btn btn-xs btn-reset" type="submit">
                            <i class="fa fa-search" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            </th>
            <th colspan="1"> <?=__('Total')?>:{{entry.editData.paging.count}}</th>
        </tr>
        <tr class="title-table">
            <th ng-repeat="title in detailListView.headers.titles">
                <span data-original-title="{{title.title}}" data-toggle="tooltip" data-placement="top">{{title.title}}</span>
                <i class="fa fa-sort {{title.sort.type}} hide" ng-if="!!title.sort" aria-hidden="true"></i>
            </th>
            <th ng-if="!!detailListView.headers.actionTitle">
                <span data-original-title="{{detailListView.headers.actionTitle}}" data-toggle="tooltip" data-placement="top">{{detailListView.headers.actionTitle}}</span>
            </th>
        </tr>
    </thead>
    <tbody ng-if="!entry.editData.moreAction">
    <tr class="tbtn zebr"
        ng-repeat-start="detail_entry in detailListView.entries"
        ng-if="!detail_entry._deleted"
        ng-class="{'student-selected-header':detail_entry._collapsed}">
        <td data-label="{{detailListView.headers.titles[field_index2].title}}"
            ng-repeat="(field_index2, field) in detail_entry.data"
            ng-click="InstitutionStudentController.getEntryEditData(detail_entry)">
            <p ng-bind-html="field.value | trust"></p>
        </td>
        <td data-label="{{detailListView.headers.actionTitle}}" class="" ng-if="!!detailListView.headers.actionTitle">
            <a href="#"
                style="color:#009966;font-size: 17px;margin-right: 10px"
                ng-if="!!detail_entry.action.view"
                ng-click="InstitutionStudentController.getEntryEditData(detail_entry);$event.stopPropagation();">
                <span data-toggle="tooltip" data-placement="top" data-original-title="<?=__('Edit')?>">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <g clip-path="url(#clip0)"> <path d="M1.6 11.04L0 16L4.96 14.4L1.6 11.04Z" fill="#009966"/> <path d="M10.5301 2.08406L2.72375 9.89038L6.11781 13.2844L13.9241 5.47812L10.5301 2.08406Z" fill="#009966"/> <path d="M15.7601 2.48L13.5201 0.24C13.2001 -0.08 12.7201 -0.08 12.4001 0.24L11.6801 0.96L15.0401 4.32L15.7601 3.6C16.0801 3.28 16.0801 2.8 15.7601 2.48Z" fill="#009966"/> </g> <defs> <clipPath id="clip0"> <rect width="16" height="16" fill="white"/> </clipPath> </defs> </svg>
                </span>
            </a>
            <a ng-if="!!detail_entry.action.remove"
               href="#"
               data-url="{{detail_entry.action.remove.urlBuild}}"
               role="{{detail_entry.action.remove.attr['role']}}"
               tabindex="{{detail_entry.action.remove.attr['tabindex']}}"
               data-toggle="{{detail_entry.action.remove.attr['data-toggle']}}"
               data-target="{{detail_entry.action.remove.attr['data-target']}}"
               field-target="{{detail_entry.action.remove.attr['field-target']}}"
               field-value="{{detail_entry.action.remove.attr['field-value']}}"
               onclick="ControllerAction.fieldMapping(this);return false;"
               style="color:#c71100;font-size: 17px;">
                <span data-toggle="tooltip" data-placement="top" data-original-title="<?=__('Remove')?>">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M2.66675 14.2222C2.66675 15.2045 3.46229 16 4.44454 16H11.5557C12.5379 16 13.3334 15.2045 13.3334 14.2222V3.55554H2.66675V14.2222Z" fill="#C71100"/> <path d="M11.1112 0.888875L10.2222 0H5.77783L4.88892 0.888875H1.77783V2.66667H14.2222V0.888875H11.1112Z" fill="#C71100"/> </svg>
                </span>
            </a>
            <a ng-if="!!detail_entry.action.delete"
               href="#"
               style="color:#C71100;font-size: 17px;"
               ng-click="InstitutionStudentController.onClickDeleteEntry(detail_entry);">
                <span data-toggle="tooltip" data-placement="top" data-original-title="<?=__('Remove')?>">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M2.66675 14.2222C2.66675 15.2045 3.46229 16 4.44454 16H11.5557C12.5379 16 13.3334 15.2045 13.3334 14.2222V3.55554H2.66675V14.2222Z" fill="#C71100"/> <path d="M11.1112 0.888875L10.2222 0H5.77783L4.88892 0.888875H1.77783V2.66667H14.2222V0.888875H11.1112Z" fill="#C71100"/> </svg>
                </span>
            </a>
        </td>
    </tr>
    <tr class="" ng-if="detail_entry._collapsed" ng-class="{'student-selected-body':detail_entry._collapsed}" ng-repeat-end>
        <td colspan="100%" ng-class="{'iframe-body': !!detail_entry.editData.iframeUrl}">
            <?php require(__DIR__.'/custom_student_detail_edit_view.ctp'); ?>
        </td>
    </tr>
    </tbody>
    <tbody ng-if="!!entry.editData.moreAction">
        <tr>
            <td colspan="100%">
                <script>
                    function iframeLoad(iframe) {
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
                        if ($('.alert', iframe.contentWindow.document).hasClass('alert-success')) {
                            window.alertSuccess = $('.alert.alert-success', iframe.contentWindow.document).text().replace('×', '').trim();
                            $(iframe).next('input').next('input').click();
                            $(iframe).next('input').click();
                        }
                    }
                </script>
                <iframe class="iframe-edit-view" ng-src="{{entry.editData.iframeUrl}}" height="500px" width="100%;" onload="iframeLoad(this);"> <!--content--> </iframe>
                <input type="hidden" ng-click="entry.editData.moreAction = null;entry.editData.paging.hide = null;InstitutionStudentController.resetAlert()"/>
                <input type="hidden" ng-click="InstitutionStudentController.selectModel(entry, model);"/>
                <input type="hidden" ng-click="InstitutionStudentController.isAppendLoader(false);"/>
            </td>
        </tr>
    </tbody>
</table>
