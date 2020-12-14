<table class="table custom-table StudentsTable">
    <thead>
        <tr class="head-table">
            <th colspan="1"><!--2-->
                <strong>
                    <?php echo __('Class') ?>:
                </strong>
                <span class="text-blue">
                    {{listView.className}}
                </span>

                <br>
                <strong>
                    {{listView.teacherPosition}}:
                </strong>
                <span class="text-blue">
                    {{listView.teacher}}
                </span>

            </th>
            <th colspan="1"><!--4-->
                <div class="input-group customImageButtons">

                    <div class="btn customAdd" type="button" title="<?=__('Add')?>" ng-if="listView.actions.add" ng-click="InstitutionStudentController.onClickStudentAdd();">
                        <span class="m-0" data-toggle="tooltip" data-placement="bottom" data-original-title="<?=__('Add')?>">
                            <svg title="Добавить" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"> <path fill-rule="evenodd" clip-rule="evenodd" d="M12.0418 9.95792H21.4168V12.0413H12.0418V21.4162H9.95847V12.0413H0.583496V9.95792H9.95847V0.582947H12.0418V9.95792Z" fill="white"></path> </svg>
                        </span>
                    </div>

                    <div class="btn " type="button" ng-if="listView.actions.export" ng-click="InstitutionStudentController.onClickExport()">
                        <span class="m-0" data-placement="bottom" data-toggle="tooltip" data-original-title="<?= __('Export') ?>">
                            <svg width="20" height="22" viewBox="0 0 20 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M16.9669 8.95441L10.1596 0.109985L3.1118 8.76391L7.28895 8.82134L8.27641 14.9772L11.4761 15.0212L12.6324 8.89481L16.9669 8.95441Z" fill="#009966"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M17.5428 12.7758V20.2758C17.5428 21.1963 16.7966 21.9425 15.8761 21.9425H4.20945C3.28898 21.9425 2.54279 21.1963 2.54279 20.2758V12.7758H4.20945V20.2758H15.8761V12.7758H17.5428Z" fill="#564242"/>
                            </svg>
                        </span>
                    </div>

                    <div class="btn " type="button" ng-if="listView.actions.graduate" ng-click="InstitutionStudentController.detailRecordAction(listView.actions.graduate, listView)">
                        <span class="m-0" data-placement="bottom" data-toggle="tooltip" data-original-title="<?=__('Promotion / Graduation')?>">
                            <svg width="35" height="20" viewBox="0 0 35 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17.0711 12.9289C15.9819 11.8398 14.6855 11.0335 13.2711 10.5454C14.786 9.50199 15.7812 7.75578 15.7812 5.78125C15.7812 2.59348 13.1878 0 10 0C6.81223 0 4.21875 2.59348 4.21875 5.78125C4.21875 7.75578 5.21402 9.50199 6.72898 10.5454C5.31453 11.0335 4.01813 11.8398 2.92895 12.9289C1.0402 14.8177 0 17.3289 0 20H1.5625C1.5625 15.3475 5.34754 11.5625 10 11.5625C14.6525 11.5625 18.4375 15.3475 18.4375 20H20C20 17.3289 18.9598 14.8177 17.0711 12.9289ZM10 10C7.67379 10 5.78125 8.1075 5.78125 5.78125C5.78125 3.455 7.67379 1.5625 10 1.5625C12.3262 1.5625 14.2188 3.455 14.2188 5.78125C14.2188 8.1075 12.3262 10 10 10Z" fill="#004A51"/>
                                <path d="M27.5002 10L25.8336 19.1667L22.5002 19.1667L20.8336 10L27.5002 10Z" fill="#009966"/>
                                <path d="M24.1668 1.66669L31.3837 10.4167H16.95L24.1668 1.66669Z" fill="#009966"/>
                            </svg>
                        </span>
                    </div>

                    <div class="btn " type="button" ng-if="listView.actions.transfer" ng-click="InstitutionStudentController.detailRecordAction(listView.actions.transfer, listView)">
                        <span class="m-0" data-placement="bottom" data-toggle="tooltip" data-original-title="<?=__('Transfer')?>">
                            <svg width="40" height="20" viewBox="0 0 40 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17.0711 12.9289C15.9819 11.8398 14.6855 11.0335 13.2711 10.5454C14.786 9.50199 15.7812 7.75578 15.7812 5.78125C15.7812 2.59348 13.1878 0 10 0C6.81223 0 4.21875 2.59348 4.21875 5.78125C4.21875 7.75578 5.21402 9.50199 6.72898 10.5454C5.31453 11.0335 4.01813 11.8398 2.92895 12.9289C1.0402 14.8177 0 17.3289 0 20H1.5625C1.5625 15.3475 5.34754 11.5625 10 11.5625C14.6525 11.5625 18.4375 15.3475 18.4375 20H20C20 17.3289 18.9598 14.8177 17.0711 12.9289ZM10 10C7.67379 10 5.78125 8.1075 5.78125 5.78125C5.78125 3.455 7.67379 1.5625 10 1.5625C12.3262 1.5625 14.2188 3.455 14.2188 5.78125C14.2188 8.1075 12.3262 10 10 10Z" fill="#004A51"/>
                                <path d="M39.1667 10L30.4167 17.2169L30.4167 2.78314L39.1667 10Z" fill="#009966"/>
                                <path d="M21.6667 17.5C21.752 9.94392 23.4467 7.3513 30.4167 6.66667L30.4167 13.3333C26.0669 12.8465 24.2592 14.0035 21.6667 17.5Z" fill="#009966"/>
                            </svg>
                        </span>
                    </div>

                    <div class="btn " type="button" ng-if="listView.actions.import" ng-click="InstitutionStudentController.detailRecordAction(listView.actions.import, listView)">
                        <span class="m-0" data-placement="bottom" data-toggle="tooltip" data-original-title="<?= __('Import') ?>">
                            <svg width="20" height="23" viewBox="0 0 20 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M17.5 12.1667V19.6667C17.5 20.5871 16.7538 21.3333 15.8333 21.3333H4.16667C3.24619 21.3333 2.5 20.5871 2.5 19.6667V12.1667H4.16667V19.6667H15.8333V12.1667H17.5Z" fill="#293845"/>
                                <path d="M6.8 8.64166L8.4 0.849998L11.6 0.849999L13.2 8.64166L6.8 8.64166Z" fill="#009966"/>
                                <path d="M10 15.725L3.0718 8.28751L16.9282 8.28751L10 15.725Z" fill="#009966"/>
                            </svg>
                        </span>
                    </div>

                    <div class="btn " type="button" ng-if="listView.actions.undo" ng-click="InstitutionStudentController.detailRecordAction(listView.actions.undo, listView)">
                        <span class="m-0" data-placement="bottom" data-toggle="tooltip" data-original-title="<?= __('Cancel') ?>">
                            <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M4.875 13C4.875 7.61583 9.24083 3.25 14.625 3.25C20.0092 3.25 24.375 7.61583 24.375 13C24.375 18.3842 20.0092 22.75 14.625 22.75C12.3933 22.75 10.335 21.9917 8.68833 20.735L10.2267 19.175C11.4725 20.0525 12.9892 20.5833 14.625 20.5833C18.8175 20.5833 22.2083 17.1925 22.2083 13C22.2083 8.8075 18.8175 5.41667 14.625 5.41667C10.4325 5.41667 7.04167 8.8075 7.04167 13H10.2917L5.95833 17.3333L1.625 13H4.875ZM14.625 10.8333C15.8167 10.8333 16.7917 11.8083 16.7917 13C16.7917 14.1917 15.8167 15.1667 14.625 15.1667C13.4333 15.1667 12.4583 14.1917 12.4583 13C12.4583 11.8083 13.4333 10.8333 14.625 10.8333Z" fill="#C71100"/>
                            </svg>
                        </span>
                    </div>
                    <br>
                    <form action="" class="header-form content-main-form-default" id="content-main-form" ng-if="listView.actions.search"
                          ng-submit="InstitutionStudentController.onClickSearch($event)">
                        <input id="search_input" type="text" class="search-input form-control" name="" placeholder="Поиск">
                        <button class="btn btn-xs btn-reset" type="button" onclick="$('.search-input').val('');jsForm.submit();" style="margin-right: 35px !important;">
                            <i class="fa fa-close"></i>
                        </button>
                        <button class="btn btn-xs btn-reset d" type="button" data-placement="bottom" data-toggle="tooltip" data-original-title="<?= __('Search') ?>" onclick="jsForm.submit()">
                            <i class="fa fa-search" aria-hidden="true"></i>
                        </button>

                    </form>
                </div>
            </th>

            <th colspan="1" class="gendersAndAllStudents" style="position: relative;display:flex">
                <div class="genders">
                <div><i class="fa fa-mars"></i>{{listView.entriesMaleCount}}</div>
                <div><i class="fa fa-venus" aria-hidden="true"></i>{{listView.entriesFemaleCount}}</div>
                </div>
                <div  class="allStudents"><b><?=__('Count total')?>:</b>{{listView.editData.paging.count}}</div>
            </th><!--3-->
            <button class="btn_close" ng-click="InstitutionStudentController.closeStudentListView()"
                    onclick="setTimeout(function(){dashboards.init();},200);"><i class="fa fa-close"></i>
            </button>
        </tr>
        <tr class="title-table" ng-if="!listView.studentAdding && !listView.editData.moreAction">
            <th ng-if="!!listView.headers.actionTitle">
                <span data-original-title="{{listView.headers.actionTitle}}" data-toggle="tooltip" data-placement="top">{{listView.headers.actionTitle}}</span>
            </th>
            <th ng-repeat="title in listView.headers.titles">
                <span data-original-title="{{title.title}}" data-toggle="tooltip" data-placement="top" >{{title.title}}</span>
                <i class="fa fa-sort {{title.sort.type}} hide" ng-if="!!title.sort" aria-hidden="true"></i>
            </th>
        </tr>
    </thead>
    <tbody ng-if="!listView.studentAdding && !listView.editData.moreAction">
        <tr class="tbtn zebr"
            ng-repeat-start="entry in listView.entries"
            ng-if="!entry._deleted"
            ng-class="{'student-selected-header':entry._collapsed}">
            <td data-label="{{listView.headers.actionTitle}}" class="custom-center" ng-if="!!listView.headers.actionTitle">
                <a href="#" aria-hidden="true" ng-if="!!entry.action.view"
                   ng-click="InstitutionStudentController.onClickStudentEdit(entry);">
                    <span class="m-0" data-toggle="tooltip" data-placement="top" data-original-title="<?=__('Edit')?>">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0)">
                                <path d="M1.6 11.04L0 16L4.96 14.4L1.6 11.04Z" fill="#009966"/>
                                <path d="M10.5301 2.08406L2.72375 9.89038L6.11781 13.2844L13.9241 5.47812L10.5301 2.08406Z" fill="#009966"/>
                                <path d="M15.7601 2.48L13.5201 0.24C13.2001 -0.08 12.7201 -0.08 12.4001 0.24L11.6801 0.96L15.0401 4.32L15.7601 3.6C16.0801 3.28 16.0801 2.8 15.7601 2.48Z" fill="#009966"/>
                            </g>
                            <defs>
                                <clipPath id="clip0"> <rect width="16" height="16" fill="white"/></clipPath>
                            </defs>
                        </svg>
                    </span>
                </a>
                <!-- view buttons -->
                <span class="m-0"
                      ng-repeat="(btnKey, btnItem) in entry.viewButtons"
                      ng-class="{dropdown: !!btnItem.dropdown}">
                    <a href="#" aria-hidden="true" data-toggle="{{!!btnItem.dropdown ? 'dropdown' : ''}}"
                       ng-if="entry._collapsed && btnKey != 'view' && btnKey != 'edit' && btnKey != 'delete' && btnKey != 'remove' && btnKey != 'back'"
                       ng-click="InstitutionStudentController.detailRecordViewAction(btnKey, btnItem, entry)">
                        <span class="m-0" data-placement="top" data-toggle="tooltip" data-original-title="{{btnItem.attr.title}}"
                              ng-if="!!btnItem.label"
                              ng-bind-html="btnItem.label | trust">
                        </span>
                    </a>
                    <ul class="dropdown-menu" ng-if="entry._collapsed && !!btnItem.dropdown">
                        <li ng-repeat="dropdown in btnItem.dropdown">
                            <a href="#" aria-hidden="true" ng-click="InstitutionStudentController.detailRecordViewAction(btnKey, dropdown, entry)">{{dropdown.title}}</a>
                        </li>
                    </ul>
                </span>

                <a ng-if="!!entry.action.remove"
                   href="#" aria-hidden="true"
                   data-url="{{entry.action.remove.urlBuild}}"
                   role="{{entry.action.remove.attr['role']}}"
                   tabindex="{{entry.action.remove.attr['tabindex']}}"
                   data-toggle="{{entry.action.remove.attr['data-toggle']}}"
                   data-target="{{entry.action.remove.attr['data-target']}}"
                   field-target="{{entry.action.remove.attr['field-target']}}"
                   field-value="{{entry.action.remove.attr['field-value']}}"
                   onclick="ControllerAction.fieldMapping(this);return false;"
                   title="<?=__('Remove')?>"
                   data-toggle="tooltip" data-placement="top" data-original-title="<?=__('Remove')?>" style="color:#c71100;font-size: 17px;margin-left: 10px">
                    <span class="m-0" data-toggle="tooltip" data-placement="top" data-original-title="<?=__('Remove')?>">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M2.66675 14.2222C2.66675 15.2045 3.46229 16 4.44454 16H11.5557C12.5379 16 13.3334 15.2045 13.3334 14.2222V3.55554H2.66675V14.2222Z" fill="#C71100"/> <path d="M11.1112 0.888875L10.2222 0H5.77783L4.88892 0.888875H1.77783V2.66667H14.2222V0.888875H11.1112Z" fill="#C71100"/> </svg>
                    </span>
                </a>
            </td>
            <td data-label="{{listView.headers.titles[field_index].title}}"
                ng-repeat="(field_index, field) in entry.data"
                ng-click="InstitutionStudentController.onClickStudentEdit(entry);"
                class="">
                <p ng-bind-html="field.value | trust"></p>
            </td>
        </tr>
        <tr class="" ng-if="entry._collapsed" ng-class="{'student-selected-body':entry._collapsed}" ng-repeat-end>
            <td ng-hide="!!entry.iframeUrl" colspan="100%">
                <?php require(__DIR__.'/custom_student_detail_view.ctp'); ?>
            </td>
            <td ng-show="!!entry.iframeUrl" colspan="100%">
                <script>
                    function iframeMainEntryLoad(iframe) {
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
                <iframe class="iframe-edit-view" ng-src="{{entry.iframeUrl}}" height="500px" width="100%;" onload="iframeMainEntryLoad(this);"> <!--content--> </iframe>
                <input type="hidden" ng-click="entry.iframeUrl = null;InstitutionStudentController.resetAlert()"/>
                <input type="hidden" ng-click="InstitutionStudentController.onClassSelect(1, false);"/>
                <input type="hidden" ng-click="InstitutionStudentController.isAppendLoader(false);"/>
            </td>
        </tr>
    </tbody>
    <tbody ng-if="listView.studentAdding">
        <tr>
            <td colspan="100%" style="max-width: 0">
                <?php require(__DIR__.'/custom_student_add.ctp'); ?>
            </td>
        </tr>
    </tbody>
    <tbody ng-if="!!listView.editData.moreAction">
        <tr>
            <td colspan="100%">
                <script>
                    function iframeMainLoad(iframe) {
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
                <iframe class="iframe-edit-view" ng-src="{{listView.editData.iframeUrl}}" height="500px" width="100%;" onload="iframeMainLoad(this);"> <!--content--> </iframe>
                <input type="hidden" ng-click="listView.editData.moreAction = null;listView.editData.paging.hide = null;InstitutionStudentController.resetAlert()"/>
                <input type="hidden" ng-click="InstitutionStudentController.onClassSelect(1, false);"/>
                <input type="hidden" ng-click="InstitutionStudentController.isAppendLoader(false);"/>
            </td>
        </tr>
    </tbody>
</table>
