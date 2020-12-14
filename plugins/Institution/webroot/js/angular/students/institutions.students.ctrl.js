var app = angular
    .module('institutions.students.ctrl', ['utils.svc', 'alert.svc', 'aggrid.locale.svc', 'institutions.students.svc'])
    .controller('InstitutionsStudentsCtrl', InstitutionStudentController);

app.filter("trust", ['$sce', function($sce) {
    return function(htmlCode){
        return $sce.trustAsHtml((htmlCode||'').toString());
    }
}]);

app.filter("trustUrl", ['$sce', function($sce) {
    return function(url){
        return $sce.trustAsResourceUrl((url||'').toString());
    }
}]);

InstitutionStudentController.$inject = ['$sce', '$location', '$q', '$scope', '$window', '$filter', 'UtilsSvc', 'AlertSvc', 'AggridLocaleSvc', 'InstitutionsStudentsSvc', '$rootScope', '$timeout'];

function InstitutionStudentController($sce, $location, $q, $scope, $window, $filter, UtilsSvc, AlertSvc, AggridLocaleSvc, InstitutionsStudentsSvc, $rootScope, $timeout) {
    // ag-grid vars


    var StudentController = this;
    var test = $scope; // remove this if unnecessary yo
    const localScope = $scope;

    var pageSize = 10;

    // Variables
    StudentController.studentAddedLocation = 'add?student_added=true';
    StudentController.externalSearch = false;
    StudentController.hasExternalDataSource;
    StudentController.internalGridOptions = null;
    StudentController.externalGridOptions = null;
    StudentController.rowsThisPage = [];
    StudentController.createNewStudent = false;
    StudentController.genderOptions = {};
    StudentController.translatedTexts = {};
    StudentController.academicPeriodOptions = {};
    StudentController.educationGradeOptions = {};
    StudentController.classOptions = {};
    StudentController.transferReasonOptions = {};
    StudentController.step = 'internal_search';
    StudentController.showExternalSearchButton = false;
    StudentController.existingStudent = false;
    StudentController.studentTransferable = false;
    StudentController.institutionId = null;

    // 0 - Non-mandatory, 1 - Mandatory, 2 - Excluded
    StudentController.StudentContacts = 2;
    StudentController.StudentIdentities = 2;
    StudentController.StudentNationalities = 2;
    StudentController.StudentSpecialNeeds = 2;
    StudentController.StudentContactsOptions = [];
    StudentController.StudentIdentitiesOptions = [];
    StudentController.StudentNationalitiesOptions = [];
    StudentController.StudentReasonOptions = [];
    StudentController.StudentLanguageOptions = [];
    StudentController.StudentSpecialNeedsOptions = [];
    StudentController.Student = {};
    StudentController.Student.nationality_id = '';
    StudentController.Student.nationality_name = '';
    StudentController.Student.reason_id = '';
    StudentController.Student.reason_name = '';
    StudentController.Student.language_id = '';
    StudentController.Student.language_name = '';
    StudentController.Student.sop = '';
    StudentController.Student.orphan = '';
    StudentController.Student.identity_type_id = '';
    StudentController.Student.identity_type_name = '';
    StudentController.Student.nationality_class = 'input select error';
    StudentController.Student.identity_type_class = 'input select error';
    StudentController.Student.identity_class = 'input string';

    // list Students
    StudentController.institution_id = null;
    StudentController.classesData = null;
    StudentController.studentsData = null;
    StudentController.listView = {
        clear: function () {
            this.headers = {
                titles: null,
                actionTitle: null
            };
            this.entries = null;
            this.entriesMaleCount = 0;
            this.entriesFemaleCount = 0;
            this.actions = {
                add: true,
                export: true,
                promotion: true,
                transfer: true,
                undo: true,
                import: true,
                search: true
            };
            this.className = null;
            this.teacherPosition = null;
            this.teacher = null;
            this.classId = 0;
            this.educationGradeId = 0;
            this.editData = null;
            this.studentAdding = false;
            return this;
        },
        closeAll: function (except) {
            for (var i in this.entries) {
                if (this.entries[i].recordId != except) {
                    this.entries[i].editData = {};
                    this.entries[i]._collapsed = false;
                }
            }
        }
    };
    StudentController.listView.clear();
    StudentController.question = {
        title: 'Подтвердите!',
        message: 'Вы уверены что?',
        ok: 'Да',
        cancel: 'Отмена',
        onOk: function () {}
    };

    // class/grade select model helper variables
    StudentController.grade;
    StudentController.selectedGrade;
    StudentController.class;
    StudentController.selectedClass;
    StudentController.currentPage;
    StudentController.currentSearch;

    //custom edit view helper variables
    StudentController.selectedCustomViewEntry;
    StudentController.selectOptions = {}; //store field id as the key i.e. `this.selectOptions[field.attr.key] = [option1. option2]`

    // Custom list view
    StudentController.detailListView = {
        clear: function() {
            this.headers = {
                titles: [],
                actionTitle: null
            };
            this.actions = {
                add: false,
                export: false,
                promotion: false,
                transfer: false,
                undo: false,
                import: false,
                search: false
            };
            this.isAddNewEntryVisible = false;
            this.entries = [];
            this.editData = null;
            return this;
        }
    };
    StudentController.detailListView.clear();


    // filter variables
    StudentController.internalFilterOpenemisNo;
    StudentController.internalFilterFirstName;
    StudentController.internalFilterLastName;
    StudentController.internalFilterIdentityNumber;
    StudentController.internalFilterPin;
    StudentController.internalFilterDateOfBirth;

    // Controller functions
    StudentController.initNationality = initNationality;
    StudentController.initIdentityType = initIdentityType;
    StudentController.changeNationality = changeNationality;
    StudentController.changeIdentityType = changeIdentityType;
    StudentController.processStudentRecord = processStudentRecord;
    StudentController.processExternalStudentRecord = processExternalStudentRecord;
    StudentController.createNewInternalDatasource = createNewInternalDatasource;
    StudentController.createNewExternalDatasource = createNewExternalDatasource;
    StudentController.insertStudentData = insertStudentData;
    StudentController.onChangeAcademicPeriod = onChangeAcademicPeriod;
    StudentController.onChangeEducationGrade = onChangeEducationGrade;
    StudentController.getStudentData = getStudentData;
    StudentController.selectStudent = selectStudent;
    StudentController.postForm = postForm;
    StudentController.postTransferForm = postTransferForm;
    StudentController.addStudentUser = addStudentUser;
    StudentController.setStudentName = setStudentName;
    StudentController.appendName = appendName;
    StudentController.changeGender = changeGender;
    StudentController.validateNewUser = validateNewUser;
    StudentController.onExternalSearchClick = onExternalSearchClick;
    StudentController.onAddNewStudentClick = onAddNewStudentClick;
    StudentController.onAddStudentClick = onAddStudentClick;
    StudentController.onAddStudentCompleteClick = onAddStudentCompleteClick;
    StudentController.onTransferStudentClick = onTransferStudentClick;
    StudentController.getUniqueOpenEmisId = getUniqueOpenEmisId;
    StudentController.generatePassword = generatePassword;
    StudentController.reloadInternalDatasource = reloadInternalDatasource;
    StudentController.reloadExternalDatasource = reloadExternalDatasource;
    StudentController.clearInternalSearchFilters = clearInternalSearchFilters;

    // fix pib
    StudentController.onChangeSelectedAcademicPeriod = onChangeSelectedAcademicPeriod;
    StudentController.onClassClick = onClassClick;
    StudentController.onPaginateStudents = onPaginateStudents;
    StudentController.mathRound = mathRound;
    StudentController.getTabContent = getTabContent;
    StudentController.generateUrl = generateUrl;
    StudentController.onClickImport = onClickImport;
    StudentController.onClickExport = onClickExport;
    StudentController.onClickPromotion = onClickPromotion;
    StudentController.onClickTransfer = onClickTransfer;
    StudentController.onClickUndo = onClickUndo;
    StudentController.onClickSearch = onClickSearch;
    StudentController.onClickSearch2 = onClickSearch2;
    StudentController.onClickStudentTrash = onClickStudentTrash;
    StudentController.onClickDeleteEntry = onClickDeleteEntry;
    StudentController.onClickStudentAdd = onClickStudentAdd;
    StudentController.onCloseStudentAdd = onCloseStudentAdd;
    StudentController.onClickStudentEdit = onClickStudentEdit;
    StudentController.getListViewHeaderTitles = getListViewHeaderTitles;
    StudentController.getListViewEntryFields = getListViewEntryFields;
    StudentController.getListViewEntryActions = getListViewEntryActions;
    StudentController.selectNavigation = selectNavigation;
    StudentController.selectModel = selectModel;
    StudentController.initDate = initDate;
    StudentController.changeDate = changeDate;
    StudentController.getOptions = getOptions;
    StudentController.saveEntry = saveEntry;
    StudentController.cancelEntry = cancelEntry;
    StudentController.isEntryCollapsed = isEntryCollapsed;
    StudentController.resetAlert = resetAlert;
    StudentController.validateEntry = validateEntry;
    StudentController.sliderTurnOn = sliderTurnOn;
    StudentController.consoleLog = consoleLog;
    StudentController.iframeNormalize = iframeNormalize;
    StudentController.isAppendLoader = isAppendLoader;

    StudentController.getListView = getListView;
    StudentController.getDetailListView = getDetailListView;
    StudentController.detailRecordAction = detailRecordAction;
    StudentController.detailRecordViewAction = detailRecordViewAction;
    StudentController.getCustomListViewHeaderTitles = getCustomListViewHeaderTitles;
    StudentController.getListEntriesFromRepsonseData = getListEntriesFromRepsonseData;
    StudentController.getCustomListViewEntryData = getCustomListViewEntryData;
    //StudentController.getCustomListViewPaginationInfo = getCustomListViewPaginationInfo;
    StudentController.getCustomViewActions = getCustomViewActions;
    StudentController.getEntryEditData = getEntryEditData;
    StudentController.onCustomEditViewCancel = onCustomEditViewCancel;
    StudentController.setSelectOptions = setSelectOptions;

    StudentController.onGradeSelect = onGradeSelect;
    StudentController.onClassSelect = onClassSelect;
    StudentController.closeStudentListView = closeStudentListView;

    StudentController.initialLoad = true;
    StudentController.date_of_birth = '';
    $scope.endDate;

    StudentController.selectedStudent;
    StudentController.addStudentButton = false;
    StudentController.selectedStudentData = null;
    StudentController.startDate = '';
    StudentController.endDateFormatted;
    StudentController.defaultIdentityTypeName;
    StudentController.defaultIdentityTypeId;
    StudentController.postResponse;


    angular.element(document).ready(function () {
        InstitutionsStudentsSvc.init(angular.baseUrl);
        InstitutionsStudentsSvc.setInstitutionId(StudentController.institutionId);
        UtilsSvc.isAppendLoader(true);

        InstitutionsStudentsSvc.getAcademicPeriods()
        .then(function(periods) {
            var promises = [];
            var selectedPeriod = [];
            angular.forEach(periods, function(value) {
                if (value.current == 1) {
                   this.push(value);
                }
            }, selectedPeriod);
            if (selectedPeriod.length == 0) {
                selectedPeriod = periods;
            }

            StudentController.academicPeriodOptions = {
                availableOptions: periods,
                selectedOption: selectedPeriod[0]
            };

            if (StudentController.academicPeriodOptions.hasOwnProperty('selectedOption')) {
                $scope.endDate = InstitutionsStudentsSvc.formatDate(StudentController.academicPeriodOptions.selectedOption.end_date);
                StudentController.onChangeAcademicPeriod();
            }
            promises.push(InstitutionsStudentsSvc.getAddNewStudentConfig());

            return $q.all(promises);
        }, function(error) {
            console.log(error);
            AlertSvc.warning($scope, error);
            UtilsSvc.isAppendLoader(false);
        })
        .then(function(promisesObj) {
            var promises = [];
            var addNewStudentConfig = promisesObj[0].data;
            for(i=0; i < addNewStudentConfig.length; i++) {
                var code = addNewStudentConfig[i].code;
                StudentController[code] = addNewStudentConfig[i].value;
            }
            if (StudentController.StudentContacts != 2) {
                promises[2] = InstitutionsStudentsSvc.getUserContactTypes();
            }
            if (StudentController.StudentNationalities != 2) {
                if (StudentController.StudentNationalities == 1) {
                    StudentController.Student.nationality_class = StudentController.Student.nationality_class + ' required';
                }
                promises[3] = InstitutionsStudentsSvc.getNationalities();
            }
            if (StudentController.StudentIdentities != 2) {
                if (StudentController.StudentIdentities == 1) {
                    StudentController.Student.identity_class = StudentController.Student.identity_class + ' required';
                    StudentController.Student.identity_type_class = StudentController.Student.identity_type_class + ' required';
                }
                promises[4] = InstitutionsStudentsSvc.getIdentityTypes();
            }
            if (StudentController.StudentSpecialNeeds != 2) {
                promises[5] = InstitutionsStudentsSvc.getSpecialNeedTypes();
            }
            promises[6]  = InstitutionsStudentsSvc.getReason();
            promises[7]  = InstitutionsStudentsSvc.getLanguages();
            promises[0] = InstitutionsStudentsSvc.getGenders();
            var translateFields = {
                'cl_teacher':'Сl.teacher',
                'actions':'Actions',
                'openemis_no': 'OpenEMIS ID',
                'name': 'Name',
                'gender_name': 'Gender',
                'date_of_birth': 'Date Of Birth',
                'nationality_name': 'Nationality',
                'reason_name': 'Reason',
                'identity_type_name': 'Identity Type',
                'identity_number': 'Identity Number'
            };
            promises[1] = InstitutionsStudentsSvc.translate(translateFields);

            return $q.all(promises);
        }, function(error){
            console.log(error);
            AlertSvc.warning($scope, error);
            UtilsSvc.isAppendLoader(false);
        })
        .then(function(promisesObj) {
            StudentController.genderOptions = translateOptions(promisesObj[0]);


            StudentController.translatedTexts = promisesObj[1];
            // User Contacts
            if (promisesObj[2] != undefined && promisesObj[2].hasOwnProperty('data')) {
                StudentController.StudentContactsOptions = promisesObj[2]['data'];
            }
            // User Nationalities
            if (promisesObj[3] != undefined && promisesObj[3].hasOwnProperty('data')) {
                StudentController.StudentNationalitiesOptions = translateOptions(promisesObj[3]['data'], ['identity_type']);
            }
            // User Identities
            if (promisesObj[4] != undefined && promisesObj[4].hasOwnProperty('data')) {
                StudentController.StudentIdentitiesOptions = promisesObj[4]['data'];
            }
            // User Special Needs
            if (promisesObj[5] != undefined && promisesObj[5].hasOwnProperty('data')) {
                StudentController.StudentSpecialNeedsOptions = promisesObj[5]['data'];
            }
            // User Reason for transfer(зачисление)
            if (promisesObj[6] != undefined && promisesObj[6].hasOwnProperty('data')) {
                StudentController.StudentReasonOptions = translateOptions(promisesObj[6]['data']);
                angular.forEach(StudentController.StudentReasonOptions, function(value, key) {
                    if(value['default'] == 1) { StudentController.Student.reason_id = value['id'];}
                });
            }
            // User Reason for transfer(зачисление)
            if (promisesObj[7] != undefined && promisesObj[7].hasOwnProperty('data')) {
                StudentController.StudentLanguageOptions = translateOptions(promisesObj[7]['data']);
                angular.forEach(StudentController.StudentLanguageOptions, function(value) {
                    if(value['default'] == 1){StudentController.Student.language_id = value['id'];}
                });
            }
        }, function(error) {
            console.log(error);
            UtilsSvc.isAppendLoader(false);
            AlertSvc.warning($scope, error);
        })
        .finally(function(result) {
            $scope.initGrid();
            UtilsSvc.isAppendLoader(false);
            if ($location.search().student_added) {
                AlertSvc.success($scope, 'The student is added successfully.');
            } else if ($location.search().student_transfer_added) {
                AlertSvc.success($scope, 'Student transfer request is added successfully.');
            }  else if ($location.search().transfer_exists) {
                AlertSvc.warning($scope, 'There is an existing transfer record for this student.');
            }
        });

    });

    function  translateOptions( options, properties = false  ) {
        InstitutionsStudentsSvc.translate(options.map(o => o.name)).then(function (data){
            for (let i in data) {
                options[i].name = data[i];
            }
        });
        if (properties) {
            for (let p in properties) {
                let property = properties[p];
                InstitutionsStudentsSvc.translate(options.map(o => o[property].name)).then(function (data) {
                    for (let i in data) {
                        options[i][property].name = data[i];
                    }
                });
            }
        }
        return options;
    }

    function initNationality() {
        StudentController.Student.nationality_id = '';
        var options = StudentController.StudentNationalitiesOptions;
        for(var i = 0; i < options.length; i++) {
            if (options[i].default == 1) {
                StudentController.Student.nationality_id = options[i].id;
                StudentController.Student.nationality_name = options[i].name;
                StudentController.Student.identity_type_id = options[i].identity_type_id;
                StudentController.Student.identity_type_name = options[i].identity_type.name;
                break;
            }
        }
    }

    function changeNationality() {
        var nationalityId = StudentController.Student.nationality_id;
        var options = StudentController.StudentNationalitiesOptions;
        for(var i = 0; i < options.length; i++) {
            if (options[i].id == nationalityId) {
                StudentController.Student.identity_type_id = options[i].identity_type_id;
                StudentController.Student.nationality_name = options[i].name;
                StudentController.Student.identity_type_name = options[i].identity_type.name;
                break;
            }
        }
    }

    function changeIdentityType() {
        var identityType = StudentController.Student.identity_type_id;
        var options = StudentController.StudentIdentitiesOptions;
        for(var i = 0; i < options.length; i++) {
            if (options[i].id == identityType) {
                StudentController.Student.identity_type_name = options[i].name;
                break;
            }
        }
    }

    function initIdentityType() {
        if (StudentController.Student.nationality_id == '') {
            var options = StudentController.StudentIdentitiesOptions;
            for(var i = 0; i < options.length; i++) {
                if (options[i].default == 1) {
                    StudentController.Student.identity_type_id = options[i].id;
                    StudentController.Student.identity_type_name = options[i].name;
                    break;
                }
            }
        }
    }

    $scope.initGrid = function() {
        AggridLocaleSvc.getTranslatedGridLocale()
        .then(function(localeText){
            StudentController.internalGridOptions = {
                columnDefs: [
                    {headerName: StudentController.translatedTexts.openemis_no, field: "openemis_no", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.name, field: "name", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.gender_name, field: "gender_name", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.date_of_birth, field: "date_of_birth", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.pin, field: "pin", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.nationality_name, field: "nationality_name", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.identity_type_name, field: "identity_type_name", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.identity_number, field: "identity_number", suppressMenu: true, suppressSorting: true}
                ],
                localeText: localeText,
                enableColResize: false,
                enableFilter: false,
                enableServerSideFilter: true,
                enableServerSideSorting: true,
                enableSorting: false,
                headerHeight: 38,
                rowData: [],
                rowHeight: 38,
                rowModelType: 'infinite',
                // Removed options - Issues in ag-Grid AG-828
                // suppressCellSelection: true,

                // Added options
                suppressContextMenu: true,
                stopEditingWhenGridLosesFocus: true,
                ensureDomOrder: true,
                pagination: true,
                paginationPageSize: 10,
                maxBlocksInCache: 1,
                cacheBlockSize: 10,
                // angularCompileRows: true,
                onRowSelected: (_e) => {
                    StudentController.selectStudent(_e.node.data.id);
                    $scope.$apply();
                }
            };

            StudentController.externalGridOptions = {
                columnDefs: [
                    {headerName: StudentController.translatedTexts.name, field: "name", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.gender_name, field: "gender_name", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.date_of_birth, field: "date_of_birth", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.pin, field: "pin", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.nationality_name, field: "nationality_name", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.identity_type_name, field: "identity_type_name", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.identity_number, field: "identity_number", suppressMenu: true, suppressSorting: true}
                ],
                localeText: localeText,
                enableColResize: false,
                enableFilter: false,
                enableServerSideFilter: true,
                enableServerSideSorting: true,
                enableSorting: false,
                headerHeight: 38,
                rowData: [],
                rowHeight: 38,
                rowModelType: 'infinite',
                // Removed options - Issues in ag-Grid AG-828
                // suppressCellSelection: true,

                // Added options
                suppressContextMenu: true,
                stopEditingWhenGridLosesFocus: true,
                ensureDomOrder: true,
                pagination: true,
                paginationPageSize: 10,
                maxBlocksInCache: 1,
                cacheBlockSize: 10,
                // angularCompileRows: true,
                onRowSelected: (_e) => {
                    StudentController.selectStudent(_e.node.data.id);
                    $scope.$apply();
                }
            };
        }, function(error){
            StudentController.internalGridOptions = {
                columnDefs: [
                    {headerName: StudentController.translatedTexts.openemis_no, field: "openemis_no", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.name, field: "name", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.gender_name, field: "gender_name", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.date_of_birth, field: "date_of_birth", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.pin, field: "pin", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.nationality_name, field: "nationality_name", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.identity_type_name, field: "identity_type_name", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.identity_number, field: "identity_number", suppressMenu: true, suppressSorting: true}
                ],
                enableColResize: false,
                enableFilter: false,
                enableServerSideFilter: true,
                enableServerSideSorting: true,
                enableSorting: false,
                headerHeight: 38,
                rowData: [],
                rowHeight: 38,
                rowModelType: 'infinite',
                // Removed options - Issues in ag-Grid AG-828
                // suppressCellSelection: true,

                // Added options
                suppressContextMenu: true,
                stopEditingWhenGridLosesFocus: true,
                ensureDomOrder: true,
                pagination: true,
                paginationPageSize: 10,
                maxBlocksInCache: 1,
                cacheBlockSize: 10,
                // angularCompileRows: true,
                onRowSelected: (_e) => {
                    StudentController.selectStudent(_e.node.data.id);
                    $scope.$apply();
                }
            };

            StudentController.externalGridOptions = {
                columnDefs: [
                    {headerName: StudentController.translatedTexts.name, field: "name", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.gender_name, field: "gender_name", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.date_of_birth, field: "date_of_birth", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.pin, field: "pin", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.nationality_name, field: "nationality_name", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.identity_type_name, field: "identity_type_name", suppressMenu: true, suppressSorting: true},
                    {headerName: StudentController.translatedTexts.identity_number, field: "identity_number", suppressMenu: true, suppressSorting: true}
                ],
                enableColResize: false,
                enableFilter: false,
                enableServerSideFilter: true,
                enableServerSideSorting: true,
                enableSorting: false,
                headerHeight: 38,
                rowData: [],
                rowHeight: 38,
                rowModelType: 'infinite',
                // Removed options - Issues in ag-Grid AG-828
                // suppressCellSelection: true,

                // Added options
                suppressContextMenu: true,
                stopEditingWhenGridLosesFocus: true,
                ensureDomOrder: true,
                pagination: true,
                paginationPageSize: 10,
                maxBlocksInCache: 1,
                cacheBlockSize: 10,
                // angularCompileRows: true,
                onRowSelected: (_e) => {
                    StudentController.selectStudent(_e.node.data.id);
                    $scope.$apply();
                }
            };
        });
    };

    function reloadInternalDatasource(withData) {
        if (withData !== false) {
           StudentController.showExternalSearchButton = true;
        }
        InstitutionsStudentsSvc.resetExternalVariable();
        StudentController.createNewInternalDatasource(StudentController.internalGridOptions, withData);
    };

    function reloadExternalDatasource(withData) {
        InstitutionsStudentsSvc.resetExternalVariable();
        StudentController.createNewExternalDatasource(StudentController.externalGridOptions, withData);
    };

    function clearInternalSearchFilters() {
        StudentController.internalFilterOpenemisNo = '';
        StudentController.internalFilterFirstName = '';
        StudentController.internalFilterLastName = '';
        StudentController.internalFilterIdentityNumber = '';
        StudentController.internalFilterPin = '';
        StudentController.internalFilterDateOfBirth = '';
        StudentController.initialLoad = true;
        StudentController.createNewInternalDatasource(StudentController.internalGridOptions);
    }

    $scope.$watch('endDate', function (newValue) {
        StudentController.endDateFormatted = $filter('date')(newValue, 'dd-MM-yyyy');
    });

    function createNewInternalDatasource(gridObj, withData) {
        var dataSource = {
            pageSize: pageSize,
            getRows: function (params) {
                AlertSvc.reset($scope);
                delete StudentController.selectedStudent;
                if (withData) {
                   InstitutionsStudentsSvc.getStudentRecords(
                    {
                        startRow: params.startRow,
                        endRow: params.endRow,
                        conditions: {
                            openemis_no: StudentController.internalFilterOpenemisNo,
                            first_name: StudentController.internalFilterFirstName,
                            last_name: StudentController.internalFilterLastName,
                            identity_number: StudentController.internalFilterIdentityNumber,
                            pin: StudentController.internalFilterPin,
                            date_of_birth: StudentController.internalFilterDateOfBirth,
                        }
                    }
                    )
                    .then(function(response) {
                        if (response.conditionsCount == 0) {
                            StudentController.initialLoad = true;
                        } else {
                            StudentController.initialLoad = false;
                        }
                        var studentRecords = response.data;
                        var totalRowCount = response.total;
                        return StudentController.processStudentRecord(studentRecords, params, totalRowCount);
                    }, function(error) {
                        console.log(error);
                        AlertSvc.warning($scope, error);
                    });
                } else {
                    StudentController.rowsThisPage = [];
                    params.successCallback(StudentController.rowsThisPage, 0);
                    return [];
                }
            }
        };
        gridObj.api.setDatasource(dataSource);
        gridObj.api.sizeColumnsToFit();
    }

    function createNewExternalDatasource(gridObj, withData) {
        StudentController.externalDataLoaded = false;
        StudentController.initialLoad = true;
        var dataSource = {
            pageSize: pageSize,
            getRows: function (params) {
                AlertSvc.reset($scope);
                // delete StudentController.selectedStudent;
                if (withData) {
                    InstitutionsStudentsSvc.getExternalStudentRecords(
                        {
                            startRow: params.startRow,
                            endRow: params.endRow,
                            conditions: {
                                first_name: StudentController.internalFilterFirstName,
                                last_name: StudentController.internalFilterLastName,
                                identity_number: StudentController.internalFilterIdentityNumber,
                                pin: StudentController.internalFilterPin,
                                date_of_birth: StudentController.internalFilterDateOfBirth
                            }
                        }
                    )
                    .then(function(response) {
                        var studentRecords = response.data;
                        var totalRowCount = response.total;
                        StudentController.initialLoad = false;
                        return StudentController.processExternalStudentRecord(studentRecords, params, totalRowCount);
                    }, function(error) {
                        console.log(error);
                        var status = error.status;
                        if (status == '401') {
                            var message = 'You have not been authorised to fetch from external data source.';
                            AlertSvc.warning($scope, message);
                        } else {
                            var message = 'External search failed, please contact your administrator to verify the external search attributes';
                            AlertSvc.warning($scope, message);
                        }
                        var studentRecords = [];
                        InstitutionsStudentsSvc.init(angular.baseUrl);
                        return StudentController.processExternalStudentRecord(studentRecords, params, 0);
                    })
                    .finally(function(res) {
                        InstitutionsStudentsSvc.init(angular.baseUrl);
                    });
                } else {
                    StudentController.rowsThisPage = [];
                    params.successCallback(StudentController.rowsThisPage, 0);
                    return [];
                }
            }
        };
        gridObj.api.setDatasource(dataSource);
        gridObj.api.sizeColumnsToFit();
    }

    function processExternalStudentRecord(studentRecords, params, totalRowCount) {
        for(var key in studentRecords) {
            var mapping = InstitutionsStudentsSvc.getExternalSourceMapping();
            studentRecords[key]['institution_name'] = '-';
            studentRecords[key]['academic_period_name'] = '-';
            studentRecords[key]['education_grade_name'] = '-';
            studentRecords[key]['date_of_birth'] = InstitutionsStudentsSvc.formatDate(studentRecords[key][mapping.date_of_birth_mapping]);
            studentRecords[key]['gender_name'] = studentRecords[key][mapping.gender_mapping];
            studentRecords[key]['gender'] = {'name': studentRecords[key][mapping.gender_mapping]};
            studentRecords[key]['identity_type_name'] = studentRecords[key][mapping.identity_type_mapping];
            studentRecords[key]['identity_number'] = studentRecords[key][mapping.identity_number_mapping];
            studentRecords[key]['nationality_name'] = studentRecords[key][mapping.nationality_mapping];
            studentRecords[key]['nationality_name'] = studentRecords[key][mapping.nationality_mapping];
            studentRecords[key]['address'] = studentRecords[key][mapping.address_mapping];
            studentRecords[key]['postal_code'] = studentRecords[key][mapping.postal_mapping];
            studentRecords[key]['name'] = '';
            if (studentRecords[key].hasOwnProperty(mapping.first_name_mapping)) {
                studentRecords[key]['name'] = studentRecords[key][mapping.first_name_mapping];
            }
            StudentController.appendName(studentRecords[key], mapping.middle_name_mapping);
            StudentController.appendName(studentRecords[key], mapping.third_name_mapping);
            StudentController.appendName(studentRecords[key], mapping.last_name_mapping);
        }

        var lastRow = totalRowCount;
        StudentController.rowsThisPage = studentRecords;

        params.successCallback(StudentController.rowsThisPage, lastRow);
        StudentController.externalDataLoaded = true;
        UtilsSvc.isAppendLoader(false);
        return studentRecords;
    }

    function processStudentRecord(studentRecords, params, totalRowCount) {
        for(var key in studentRecords) {
            studentRecords[key]['institution_name'] = '-';
            studentRecords[key]['academic_period_name'] = '-';
            studentRecords[key]['education_grade_name'] = '-';
            if ((studentRecords[key].hasOwnProperty('institution_students') && studentRecords[key]['institution_students'].length > 0)) {
                studentRecords[key]['institution_name'] = ((studentRecords[key].institution_students['0'].hasOwnProperty('institution')))? studentRecords[key].institution_students['0'].institution.name: '-';
                studentRecords[key]['academic_period_name'] = ((studentRecords[key].institution_students['0'].hasOwnProperty('academic_period')))? studentRecords[key].institution_students['0'].academic_period.name: '-';
                studentRecords[key]['education_grade_name'] = ((studentRecords[key].institution_students['0'].hasOwnProperty('education_grade')))? studentRecords[key].institution_students['0'].education_grade.name: '-';
            }

            studentRecords[key]['date_of_birth'] = InstitutionsStudentsSvc.formatDate(studentRecords[key]['date_of_birth']);
            studentRecords[key]['gender_name'] = studentRecords[key]['gender']['name'];

            if (studentRecords[key]['main_nationality'] != null) {
                studentRecords[key]['nationality_name'] = studentRecords[key]['main_nationality']['name'];
            }
            if (studentRecords[key]['main_identity_type'] != null) {
                studentRecords[key]['identity_type_name'] = studentRecords[key]['main_identity_type']['name'];
            }

            if (!studentRecords[key].hasOwnProperty('name')) {
                studentRecords[key]['name'] = '';
                if (studentRecords[key].hasOwnProperty('first_name')) {
                    studentRecords[key]['name'] = studentRecords[key]['first_name'];
                }
                StudentController.appendName(studentRecords[key], 'middle_name');
                StudentController.appendName(studentRecords[key], 'third_name');
                StudentController.appendName(studentRecords[key], 'last_name');
            }
        }

        var lastRow = totalRowCount;
        StudentController.rowsThisPage = studentRecords;

        params.successCallback(StudentController.rowsThisPage, lastRow);
        StudentController.externalDataLoaded = true;
        UtilsSvc.isAppendLoader(false);
        return studentRecords;
    }

    function insertStudentData(studentId, academicPeriodId, educationGradeId, classId, startDate, endDate, reasonId, userRecord) {
        UtilsSvc.isAppendLoader(true);
        AlertSvc.reset($scope);
        var data = {
            student_id: studentId,
            academic_period_id: academicPeriodId,
            education_grade_id: educationGradeId,
            start_date: startDate,
            end_date: endDate,
            institution_class_id: classId,
            institution_reason_for_transfer_id: reasonId
        };

        InstitutionsStudentsSvc.postEnrolledStudent(data)
        .then(function(postResponse) {
            StudentController.postResponse = postResponse.data;
            UtilsSvc.isAppendLoader(false);
            if (postResponse.data.error.length === 0) {
                if (!!document.querySelector('.institution-dashboard')) {
                    StudentController.clearInternalSearchFilters();
                    StudentController.onCloseStudentAdd(false);
                    StudentController.onClickStudentAdd();
                    $timeout(function () {
                        AlertSvc.success($scope, 'The student is added successfully.');
                    }, 200);
                }
                else {
                    AlertSvc.success($scope, 'The student is added successfully.');
                    $timeout(function () {
                        $window.location.href = StudentController.studentAddedLocation;
                    }, 200);
                }
            } else if (userRecord.hasOwnProperty('institution_students') && userRecord.institution_students.length > 0) {
                userRecord.date_of_birth = InstitutionsStudentsSvc.formatDate(userRecord.date_of_birth);
                StudentController.selectedStudentData = userRecord;
                StudentController.existingStudent = true;

                var schoolId = userRecord['institution_students'][0]['institution_id'];
                if (StudentController.institutionId != schoolId) {
                    StudentController.studentTransferable = true;
                    var schoolName = userRecord['institution_students'][0]['institution']['code_name'];
                    AlertSvc.warning($scope, 'This student is already allocated to %s', [schoolName]);
                } else {
                    AlertSvc.warning($scope, 'This student is already allocated to the current institution');
                }
            } else {
                AlertSvc.error($scope, 'The record is not added due to errors encountered.');
            }
        }, function(error) {
            console.log(error);
            AlertSvc.warning($scope, error);
        });
    }

    function onAddNewStudentClick() {
        StudentController.createNewStudent = true;
        StudentController.studentTransferable = false;
        StudentController.existingStudent = false;
        StudentController.selectedStudentData = {};
        StudentController.selectedStudentData.first_name = '';
        StudentController.selectedStudentData.last_name = '';
        StudentController.selectedStudentData.date_of_birth = '';
        StudentController.initNationality();
        StudentController.initIdentityType();
        angular.element(document.querySelector('#wizard')).wizard('selectedItem', {
            step: "createUser"
        });

        //getStudentdTest();
    }

    function onAddStudentClick() {
        angular.element(document.querySelector('#wizard')).wizard('selectedItem', {
            step: "addStudent"
        });
    }

    function onAddStudentCompleteClick() {
        StudentController.postForm();
    }

    function onExternalSearchClick() {
        angular.element(document.querySelector('#wizard')).wizard('selectedItem', {
            step: "externalSearch"
        });
    }

    function onTransferStudentClick() {
        // setup transfer student input fields
        var studentData = StudentController.selectedStudentData;
        var periodEndDate = InstitutionsStudentsSvc.formatDate(studentData['institution_students'][0]['academic_period']['end_date']);

        // only allow transfer start date to be one day after the student's current start date
        var studentStartDate = new Date(studentData['institution_students'][0]['start_date']);
        studentStartDate.setDate(studentStartDate.getDate() + 1);
        var studentStartDateFormatted = $filter('date')(studentStartDate, 'dd-MM-yyyy');

        StudentController.startDate = studentStartDateFormatted;
        $scope.endDate = periodEndDate;

        var startDatePicker = angular.element(document.getElementById('Students_transfer_start_date'));
        startDatePicker.datepicker("setStartDate", studentStartDateFormatted);
        startDatePicker.datepicker("setEndDate", periodEndDate);
        startDatePicker.datepicker("setDate", studentStartDateFormatted);

        StudentController.classOptions = {};
        StudentController.transferReasonOptions = {};

        InstitutionsStudentsSvc.getClasses({
            institutionId: StudentController.institutionId,
            academicPeriodId: studentData['institution_students'][0]['academic_period_id'],
            gradeId: studentData['institution_students'][0]['education_grade_id'],
        })
        .then(function(classes) {
            StudentController.classOptions = {
                availableOptions: classes,
            };
            return InstitutionsStudentsSvc.getStudentTransferReasons();
        }, function(error) {
            console.log(error);
        })
        .then(function(response) {
            if (angular.isDefined(response) && response.hasOwnProperty('data')) {
                StudentController.transferReasonOptions = {
                    availableOptions: response.data
                };
            }
        }, function(error) {
            console.log(error);
        })
        .finally(function(result) {
            angular.element(document.querySelector('#wizard')).wizard('selectedItem', {
                step: "transferStudent"
            });
        });
    }

    function selectStudent(id) {
        StudentController.selectedStudent = id;
        StudentController.getStudentData();
    }

    function setStudentName() {
        var studentData = StudentController.selectedStudentData;
        studentData.name = '';

        if (studentData.hasOwnProperty('first_name')) {
            studentData.name = studentData.first_name.trim();
        }
        StudentController.appendName(studentData, 'middle_name', true);
        StudentController.appendName(studentData, 'third_name', true);
        StudentController.appendName(studentData, 'last_name', true);
        StudentController.selectedStudentData = studentData;
    }

    function appendName(studentObj, variableName, trim) {
        if (studentObj.hasOwnProperty(variableName)) {
            if (trim === true) {
                studentObj[variableName] = studentObj[variableName].trim();
            }
            if (studentObj[variableName] != null && studentObj[variableName] != '') {
                studentObj.name = studentObj.name + ' ' + studentObj[variableName];
            }
        }
        return studentObj;
    }

    function changeGender() {
        var studentData = StudentController.selectedStudentData;
        if (studentData.hasOwnProperty('gender_id')) {
            var genderOptions = StudentController.genderOptions;
            for(var i = 0; i < genderOptions.length; i++) {
                if (genderOptions[i].id == studentData.gender_id) {
                    studentData.gender = {
                        name: genderOptions[i].name
                    };
                }
            }
            StudentController.selectedStudentData = studentData;
        }
    }

    function getStudentData() {
        var log = [];
        angular.forEach(StudentController.rowsThisPage , function(value) {
            if (value.id == StudentController.selectedStudent) {
                StudentController.selectedStudentData = value;
            }
        }, log);
    }

    function onChangeAcademicPeriod() {
        AlertSvc.reset($scope);

        if (StudentController.academicPeriodOptions.hasOwnProperty('selectedOption')) {
            $scope.endDate = InstitutionsStudentsSvc.formatDate(StudentController.academicPeriodOptions.selectedOption.end_date);
            StudentController.startDate = InstitutionsStudentsSvc.formatDate(StudentController.academicPeriodOptions.selectedOption.start_date);
        }

        var startDatePicker = angular.element(document.getElementById('Students_start_date'));
        startDatePicker.datepicker("setStartDate", InstitutionsStudentsSvc.formatDate(StudentController.academicPeriodOptions.selectedOption.start_date));
        startDatePicker.datepicker("setEndDate", InstitutionsStudentsSvc.formatDate(StudentController.academicPeriodOptions.selectedOption.end_date));
        startDatePicker.datepicker("setDate", InstitutionsStudentsSvc.formatDate(StudentController.academicPeriodOptions.selectedOption.start_date));

        StudentController.educationGradeOptions = null;
        InstitutionsStudentsSvc.getEducationGrades({
            institutionId: StudentController.institutionId,
            academicPeriodId: StudentController.academicPeriodOptions.selectedOption.id
        })
        .then(function(educationGrades) {
            StudentController.educationGradeOptions = {
                availableOptions: educationGrades,
            };

            if (StudentController.listView.educationGradeId) {
                StudentController.educationGradeOptions.selectedOption = StudentController.educationGradeOptions.availableOptions.find((value) => {
                    return value.education_grade.id == StudentController.listView.educationGradeId ? value : '';
                });
                onChangeEducationGrade();
            }
        }, function(error) {
            console.log(error);
            AlertSvc.warning($scope, error);
        });

    }

    function onChangeEducationGrade() {
        AlertSvc.reset($scope);

        StudentController.classOptions = null;

        InstitutionsStudentsSvc.getClasses({
            institutionId: StudentController.institutionId,
            academicPeriodId: StudentController.academicPeriodOptions.selectedOption.id,
            gradeId: StudentController.educationGradeOptions.selectedOption.education_grade_id
        })
        .then(function(classes) {
            StudentController.classOptions = {
                availableOptions: classes,
            };

            if (StudentController.listView.classId) {
                StudentController.classOptions.selectedOption = StudentController.classOptions.availableOptions.find((value) => {
                    return value.id == StudentController.listView.classId ? value : '';
                });
            }
        }, function(error) {
            console.log(error);
            AlertSvc.warning($scope, error);
        });
    }

    function postForm() {
        var academicPeriodId = (StudentController.academicPeriodOptions.hasOwnProperty('selectedOption'))? StudentController.academicPeriodOptions.selectedOption.id: '';
        var educationGradeId = (StudentController.educationGradeOptions.hasOwnProperty('selectedOption'))? StudentController.educationGradeOptions.selectedOption.education_grade_id: '';
        var reasonId = (StudentController.educationGradeOptions.hasOwnProperty('selectedOption'))? StudentController.Student.reason_id: '';
        if (educationGradeId == undefined) {
            educationGradeId = '';
        }
        var classId = null;
        if (StudentController.classOptions.hasOwnProperty('selectedOption')) {
            classId = StudentController.classOptions.selectedOption.id;
        }
        var startDate = StudentController.startDate;
        var startDateArr = startDate.split("-");
        startDate = startDateArr[2] + '-' + startDateArr[1] + '-' + startDateArr[0];
        for(i = 0; i < startDateArr.length; i++) {
            if (startDateArr[i] == undefined || startDateArr[i] == null || startDateArr[i] == '') {
                startDate = undefined;
            }
        }
        var endDate = $scope.endDate;

        if (!StudentController.createNewStudent) {
            if (StudentController.externalSearch) {
                var studentData = StudentController.selectedStudentData;
                var amendedStudentData = Object.assign({}, studentData);
                amendedStudentData.date_of_birth = InstitutionsStudentsSvc.formatDate(amendedStudentData.date_of_birth);
                StudentController.addStudentUser(amendedStudentData, academicPeriodId, educationGradeId, classId, startDate, endDate, reasonId);
            } else {
                var studentId = StudentController.selectedStudent;
                StudentController.insertStudentData(studentId, academicPeriodId, educationGradeId, classId, startDate, endDate, reasonId,{});
            }
        } else {
            if (StudentController.selectedStudentData != null) {
                var studentData = {};
                var log = [];
                angular.forEach(StudentController.selectedStudentData, function(value, key) {
                  studentData[key] = value;
                }, log);
                if (studentData.hasOwnProperty('date_of_birth')) {
                    var dateOfBirth = studentData.date_of_birth;
                    var dateOfBirthArr = dateOfBirth.split("-");
                    dateOfBirth = dateOfBirthArr[2] + '-' + dateOfBirthArr[1] + '-' + dateOfBirthArr[0];
                    studentData.date_of_birth = dateOfBirth;
                }
                delete studentData['id'];
                delete studentData['institution_students'];
                delete studentData['is_staff'];
                delete studentData['is_guardian'];
                delete studentData['address'];
                delete studentData['postal_code'];
                delete studentData['address_area_id'];
                delete studentData['birthplace_area_id'];
                delete studentData['date_of_death'];
                studentData['super_admin'] = 0;
                studentData['status'] = 1;
                delete studentData['last_login'];
                delete studentData['photo_name'];
                delete studentData['photo_content'];
                delete studentData['modified'];
                delete studentData['modified_user_id'];
                delete studentData['created'];
                delete studentData['created_user_id'];
                StudentController.addStudentUser(studentData, academicPeriodId, educationGradeId, classId, startDate, endDate, reasonId);
            }
        }
    }

    function postTransferForm() {
        var transferReasonId = (StudentController.transferReasonOptions.hasOwnProperty('selectedOption'))? StudentController.transferReasonOptions.selectedOption.id: null;
        var classId = (StudentController.classOptions.hasOwnProperty('selectedOption'))? StudentController.classOptions.selectedOption.id: null;
        var startDate = StudentController.startDate;
        var startDateArr = startDate.split("-");
        startDate = startDateArr[2] + '-' + startDateArr[1] + '-' + startDateArr[0];
        for(i = 0; i < startDateArr.length; i++) {
            if (startDateArr[i] == undefined || startDateArr[i] == null || startDateArr[i] == '') {
                startDate = undefined;
            }
        }
        var endDate = $scope.endDate;

        var data = {
            start_date: startDate,
            end_date: endDate,
            student_id: StudentController.selectedStudent,
            status_id: 0,
            assignee_id: -1,
            institution_id: StudentController.institutionId,
            academic_period_id: StudentController.selectedStudentData.institution_students[0]['academic_period_id'],
            education_grade_id: StudentController.selectedStudentData.institution_students[0]['education_grade_id'],
            institution_class_id: classId,
            previous_institution_id: StudentController.selectedStudentData.institution_students[0]['institution_id'],
            previous_academic_period_id: StudentController.selectedStudentData.institution_students[0]['academic_period_id'],
            previous_education_grade_id: StudentController.selectedStudentData.institution_students[0]['education_grade_id'],
            student_transfer_reason_id: transferReasonId,
            comment: StudentController.comment
        };

        InstitutionsStudentsSvc.addStudentTransferRequest(data)
        .then(function(postResponse) {
            StudentController.postResponse = postResponse.data;
            var counter = 0;
            angular.forEach(postResponse.data.error , function(value) {
                counter++;
            });

            if (counter == 0) {
                AlertSvc.success($scope, 'Student transfer request is added successfully.');
                $window.location.href = 'add?student_transfer_added=true';
            } else if (counter == 1 && postResponse.data.error.hasOwnProperty('student_transfer') && postResponse.data.error.student_transfer.hasOwnProperty('ruleTransferRequestExists')) {
                AlertSvc.warning($scope, 'There is an existing transfer record for this student.');
                $window.location.href = postResponse.data.error.student_transfer.ruleTransferRequestExists;
            } else {
                AlertSvc.error($scope, 'There is an error in adding student transfer request.');
            }
        }, function(error) {
            console.log(error);
            AlertSvc.error($scope, 'There is an error in adding student transfer request.');
        });
    }

    function addStudentUser(studentData, academicPeriodId, educationGradeId, classId, startDate, endDate, reasonId) {
        var newStudentData = studentData;
        newStudentData['academic_period_id'] = academicPeriodId;
        newStudentData['education_grade_id'] = educationGradeId;
        newStudentData['institution_class_id'] = classId;
        newStudentData['start_date'] = startDate;
        newStudentData['institution_reason_for_transfer_id'] = reasonId;
        newStudentData['institution_id'] = StudentController.institutionId;
        newStudentData['language_id'] = StudentController.Student.language_id;
        newStudentData['sop'] = StudentController.Student.sop;
        newStudentData['orphan'] = StudentController.Student.orphan;
        if (!StudentController.externalSearch) {
            newStudentData['nationality_id'] = StudentController.Student.nationality_id;
            newStudentData['identity_type_id'] = StudentController.Student.identity_type_id;
        }
        InstitutionsStudentsSvc.addUser(newStudentData)
        .then(function(user){
            if (user[0].error.length === 0) {
                var studentId = user[0].data.id;
                StudentController.insertStudentData(studentId, academicPeriodId, educationGradeId, classId, startDate, endDate, reasonId, user[1]);
            } else {
                StudentController.postResponse = user[0];
                console.log(user[0]);
                AlertSvc.error($scope, 'The record is not added due to errors encountered.');
            }
        }, function(error){
            console.log(error);
            AlertSvc.warning($scope, error);
        });
    }


    angular.element(document.body).on('actionclicked.fu.wizard', '#wizard', function(evt, data) {
        // evt.preventDefault();
        AlertSvc.reset($scope);

        if (angular.isDefined(StudentController.postResponse)){
            delete StudentController.postResponse;
            $scope.$apply();
        }
        // To go to add student page if there is a student selected from the internal search
        // or external search
        if (data.step == 3 && data.direction == 'next') {
            if (StudentController.validateNewUser()) {
                evt.preventDefault();
            };
        }
    });

    function validateNewUser() {
        var remain = false;
        var empty = {'_empty': 'This field cannot be left empty'};
        InstitutionsStudentsSvc.translate(empty).then(function (data){
            empty._empty = data._empty;
        });

        var pinMassage = {'_pinMassage': 'The value must be numeric and must be equal to 14'};
        InstitutionsStudentsSvc.translate(pinMassage).then(function (data){
            pinMassage._pinMassage = data._pinMassage;
        });

        var numeric = {'_numeric': 'The value must be numeric.'};
        InstitutionsStudentsSvc.translate(numeric).then(function (data){
            numeric._numeric = data._numeric;
        });

        StudentController.postResponse = {};
        StudentController.postResponse.error = {};
        if (StudentController.selectedStudentData.first_name == '') {
            StudentController.postResponse.error.first_name = empty;
            remain = true;
        }

        if (StudentController.selectedStudentData.last_name == '') {
            StudentController.postResponse.error.last_name = empty;
            remain = true;
        }
        if (StudentController.selectedStudentData.gender_id == '' || StudentController.selectedStudentData.gender_id == null) {
            StudentController.postResponse.error.gender_id = empty;
            remain = true;
        }

        if (StudentController.selectedStudentData.date_of_birth == '') {
            StudentController.postResponse.error.date_of_birth = empty;
            remain = true;
        }

        if (StudentController.StudentNationalities == 1 && (StudentController.Student.nationality_id == '' || StudentController.Student.nationality_id == undefined)) {
            remain = true;
        }
        if(StudentController.selectedStudentData.pin != '' && StudentController.selectedStudentData.pin != undefined){

            var pin = StudentController.selectedStudentData.pin.trim()

            var reg =  /^\d+$/;
            if(!reg.test(pin)){
                StudentController.postResponse.error.pin = numeric;
                remain = true;
            }

            if (StudentController.Student.nationality_id == 1) {
                if(pin.length != 14) {
                    StudentController.postResponse.error.pin = pinMassage;
                    remain = true;
                }
            }
        }

        if (StudentController.selectedStudentData.username == '' || StudentController.selectedStudentData.username == undefined) {
            StudentController.postResponse.error.username = empty;
            remain = true;
        }

        if (StudentController.selectedStudentData.password == '' || StudentController.selectedStudentData.password == undefined) {
            StudentController.postResponse.error.password = empty;
            remain = true;
        }

        var arrNumber = [{}];

        // if (StudentController.StudentIdentities == 1 && (StudentController.Student.identity_type_id == '' || StudentController.Student.identity_type_id == undefined)) {
        //     arrNumber[0]['identity_type_id'] = empty;
        //     StudentController.postResponse.error.identities = arrNumber;
        //     remain = true;
        // }
        if (StudentController.StudentIdentities == 1 && (StudentController.selectedStudentData.identity_number == '' || StudentController.selectedStudentData.identity_number == undefined)) {
            arrNumber[0]['number'] = empty;
            StudentController.postResponse.error.identities = arrNumber;
            remain = true;
        }

        var arrNationality = [{}];
        if (StudentController.StudentNationalities == 1 && (StudentController.Student.nationality_id == '' || StudentController.Student.nationality_id == undefined)) {
            arrNationality[0]['nationality_id'] = empty;
            StudentController.postResponse.error.nationalities = arrNationality;
            remain = true;
        }

        if (remain) {
            //AlertSvc.error($scope, 'Please review the errors in the form.');
            AlertSvc.error($scope, 'The record is not updated due to errors encountered.');
            $scope.$apply();
            angular.element(document.querySelector('#wizard')).wizard('selectedItem', {
                step: 'createUser'
            });
        }
        return remain;
    }

    function getUniqueOpenEmisId() {
        UtilsSvc.isAppendLoader(true);
        InstitutionsStudentsSvc.getUniqueOpenEmisId()
        .then(function(response) {
            var username = StudentController.selectedStudentData.username;
            if (username == StudentController.selectedStudentData.openemis_no || username == '' || typeof username == 'undefined') {
                StudentController.selectedStudentData.username = response;
            }
            StudentController.selectedStudentData.openemis_no = response;
            UtilsSvc.isAppendLoader(false);
        }, function(error) {
            console.log(error);
            UtilsSvc.isAppendLoader(false);
        });
    }

    function getStudentdTest(){
        InstitutionsStudentsSvc.getStudentdTest();
    }

    function generatePassword() {
        UtilsSvc.isAppendLoader(true);
        InstitutionsStudentsSvc.generatePassword()
        .then(function(response) {
            if (StudentController.selectedStudentData.password == '' || typeof StudentController.selectedStudentData.password == 'undefined') {
                StudentController.selectedStudentData.password = response;
            }
            UtilsSvc.isAppendLoader(false);
        }, function(error) {
            console.log(error);
            UtilsSvc.isAppendLoader(false);
        });
    }

    angular.element(document.body).on('finished.fu.wizard', '#wizard', function(evt, data) {
        // The last complete step is now transfer staff, add transfer staff logic function call here
        StudentController.postTransferForm();
    });

    angular.element(document.body).on('changed.fu.wizard', '#wizard', function(evt, data) {
        StudentController.addStudentButton = false;
        // Step 1 - Internal search
        if (data.step == 1) {
            StudentController.Student.identity_type_name = StudentController.defaultIdentityTypeName;
            StudentController.Student.identity_type_id = StudentController.defaultIdentityTypeId;
            StudentController.educationGradeOptions.selectedOption = '';
            StudentController.classOptions.selectedOption = '';
            delete StudentController.postResponse;
            StudentController.reloadInternalDatasource(true);
            StudentController.createNewStudent = false;
            StudentController.externalSearch = false;
            StudentController.step = 'internal_search';
        }
        // Step 2 - External search
        else if (data.step == 2) {
            StudentController.Student.identity_type_name = StudentController.externalIdentityType;
            StudentController.Student.identity_type_id = StudentController.defaultIdentityTypeId;
            StudentController.educationGradeOptions.selectedOption = '';
            StudentController.classOptions.selectedOption = '';
            delete StudentController.postResponse;
            StudentController.reloadExternalDatasource(true);
            StudentController.createNewStudent = false;
            StudentController.externalSearch = true;
            StudentController.step = 'external_search';
        }
        // Step 3 - Create user
        else if (data.step == 3) {
            StudentController.externalSearch = false;
            StudentController.createNewStudent = true;
            StudentController.step = 'create_user';
            StudentController.getUniqueOpenEmisId();
            StudentController.generatePassword();
            onChangeAcademicPeriod();
            InstitutionsStudentsSvc.resetExternalVariable();
        }
        // Step 4 - Add Student
        else if (data.step == 4) {
            if (StudentController.externalSearch) {
                StudentController.getUniqueOpenEmisId();
            }
            onChangeAcademicPeriod();
            var studentData = StudentController.selectedStudentData;
            StudentController.existingStudent = false;
            StudentController.studentTransferable = false;

            if (studentData.hasOwnProperty('institution_students') && studentData.institution_students.length > 0) {
                StudentController.existingStudent = true;

                var schoolId = studentData['institution_students'][0]['institution_id'];
                if (StudentController.institutionId != schoolId) {
                    StudentController.studentTransferable = true;
                    var schoolName = studentData['institution_students'][0]['institution']['code_name'];
                    AlertSvc.warning($scope, 'This student is already allocated to %s', [schoolName]);
                } else {
                    AlertSvc.warning($scope, 'This student is already allocated to the current institution');
                }
            }
            StudentController.step = 'add_student';
        }
        // Step 5 - Transfer Student
        else {
            AlertSvc.info($scope, 'By clicking save, a transfer workflow will be initiated for this student');
            StudentController.step = 'transfer_student';
        }

        // to ensure that the StudentController.step is updated
        setTimeout(function() {
            $scope.$apply();
        });
    });

    // fix pib
    function onChangeSelectedAcademicPeriod()
    {
        var periodID = StudentController.academicPeriodOptions.selectedOption.id;
        UtilsSvc.isAppendLoader(true);
        StudentController.listView.clear();
        setTimeout(function() {
            initCustomChart(periodID);
        },200);
        onChangeAcademicPeriod();

        InstitutionsStudentsSvc.getClassesData({
            institution_id: StudentController.institutionId,
            academic_period_id: StudentController.academicPeriodOptions.selectedOption.id
        }).then(function(data) {
            sliders.removeSlickSlides();

            var result = [];

            angular.forEach(data, function(subclasses) {

                angular.forEach(subclasses.education_grades, function(value) {
                    var finded = false;
                    if (result.length) {
                        angular.forEach(result, function (v) {
                            if (v.edication_grade_id === value.id && v.name === value.name) {
                                finded = true;
                            }
                        });
                    }

                    if (!result.length || !finded) {
                        result.push({
                            education_stage_id: value.education_stage_id,
                            edication_grade_id: value.id,
                            name: value.name,
                            values: []
                        });
                    }

                    if (result.length) {


                        angular.forEach(result, function (v, i) {
                            if (v.edication_grade_id === value.id && v.name === value.name) {
                                result[i].values.push({
                                    id: subclasses.id,
                                    name: subclasses.name,
                                    total_male_students: subclasses.total_male_students,
                                    total_female_students: subclasses.total_female_students,
                                    staff: subclasses.staff
                                });
                            }
                        });
                    }
                });
            });
            // var sortedResult = [];
            // result.forEach(oneClass => {
            //     sortedResult[oneClass.education_grade_id] = oneClass;
            // });
            var newSortedResultWithNormalIndex = []
            result.forEach(oneClass => {
                newSortedResultWithNormalIndex.push(oneClass);
            });
            StudentController.classesData = newSortedResultWithNormalIndex;
            setTimeout(function() {
                sliders.init();
            },200);
            UtilsSvc.isAppendLoader(false);

        }, function(error) {
            AlertSvc.error($scope, 'Unexpected error! Please, try log in.');
            UtilsSvc.isAppendLoader(false);
        });
    }

    function getListViewHeaderTitles(header) {
        var result = [];
        for (var i in header) {
            var title = header[i];
            if (!$.isNumeric( i ) || i === "0")
                continue;
            var $title = $(title);
            //console.log('title', $title);
            result.push({
                title: $title.text() || title,
                sort: $title.prop("tagName") === 'A' ? {
                    type: $title.hasClass('desc') ? 'desc' : ($title.hasClass('asc') ? 'asc' : '')
                } : false
            });
        }
        return result;
    }

    function getListViewEntryFields(row) {
        var result = [];
        for (var i in row) {
            var field = row[i];
            if (!$.isNumeric( i ) || field !== null && (typeof field === "object" || typeof field === "array" || !!field.view || !!field.edit || !!field.remove) )
                continue;
            result.push({
                value: field
            });
        }
        return result;
    }

    function getListViewEntryActions(row) {
        for (let i in row){
            if (row[i] === null) {
                continue;
            }
            if (!!row[i].view || !!row[i].edit || !!row[i].remove) {
                return row[i];
            }
        }
        return {view:false, edit: false, remove: false};
    }

    function initDate(id, date) {
        setTimeout(function (id) {
            $(id+' input.form-control').mask('00-00-0000', {placeholder: "__-__-____"});
            var datepicker = $(id).datepicker({"format":"dd-mm-yyyy","todayBtn":"linked","orientation":"auto","autoclose":true, "language": "ru"});
            $( document ).on('DOMMouseScroll mousewheel scroll', function(){
                var t = window.setTimeout( function(datepicker){
                    datepicker.datepicker('place');
                }, 1, datepicker);
                window.clearTimeout( t );
            });
        }, 100, id);
    }

    function changeDate(date_view) {
        var date = moment(date_view, 'DD-MM-YYYY');
        if (date.isValid())
            return date.format('DD-MM-YYYY');
        else
            return '';
    }

    function getOptions(field, value) {
        if (!!StudentController.selectOptions[field.attr.field])
            return StudentController.selectOptions[field.attr.field];
        let options = [];
        if (field.attr.model === 'StudentUser') {
            if (field.attr.field === 'gender_id') {
                options = StudentController.genderOptions;
            }
            else if (field.attr.field === 'nationality_id') {
                options = StudentController.StudentNationalitiesOptions;
            }
            else if (field.attr.field === 'identity_type_id') {
                options = StudentController.StudentIdentitiesOptions;
            }
            else if (Object.keys(field.attr.options || []).length > 0) {
                options = Object.keys(field.attr.options).map(key => ({ id: key !== "" && !isNaN(+(key)) ? +(key) : key, name: field.attr.options[key] })) || options;
            }
            else if (field.attr.type === 'custom_dropdown' && Object.keys(field.attr.customField.custom_field_options).length > 0) {
                options = field.attr.customField.custom_field_options;
            }
        }
        else if (Object.keys(field.attr.options || []).length > 0) {
            options = Object.keys(field.attr.options).map(key => ({ id: key !== "" && !isNaN(+(key)) ? +(key) : key, name: field.attr.options[key] })) || options;
        }
        else if (field.attr.type === 'custom_dropdown' && Object.keys(field.attr.customField.custom_field_options).length > 0) {
            options = field.attr.customField.custom_field_options;
        }
        else if (!!field.option.value) {
            options.push({id: value, name: field.option.value});
        }
        for (var i in options) {
            if (options[i].id === "") {
                options.splice(i, 1);
            }
        }
        StudentController.selectOptions[field.attr.field] = options;
        return options;
    }

    function onGradeSelect() {
        StudentController.selectedGrade = JSON.parse(StudentController.grade)
        StudentController.currentPage = 1;
        StudentController.currentSearch = null;
    }

    function onClassSelect(page = 1, classEqual = true, search = null) {
        StudentController.currentPage = page;
        StudentController.selectedClass = JSON.parse(StudentController.class);
        StudentController.onClassClick(StudentController.selectedClass.id, StudentController.selectedClass.name, StudentController.selectedGrade.edication_grade_id, page, classEqual, search)
    }

    function onClassClick(class_id, class_name, education_grade_id, page = 1, classEqual = true, search = null) {
        StudentController.currentPage = page;
        StudentController.currentSearch = search;
        if (classEqual && class_id === StudentController.listView.classId) {
            return false;
        }

        UtilsSvc.isAppendLoader(true);

        InstitutionsStudentsSvc.getClassStudents(class_id, StudentController.institution_id, StudentController.academicPeriodOptions.selectedOption.id, StudentController.currentPage, StudentController.currentSearch)
        .then(function(data) {

            StudentController.listView = StudentController.getListView(data);

            angular.forEach(StudentController.classesData, function(item) {
                consoleLog(item);
                if (item.edication_grade_id === education_grade_id) {
                    angular.forEach(item.values, function(classData) {
                        if (classData.id === class_id) {
                            StudentController.listView.entriesMaleCount = classData.total_male_students;
                            StudentController.listView.entriesFemaleCount = classData.total_female_students;
                            if (classData.staff) {
                                StudentController.listView.teacher = classData.staff.last_name + ' ' + classData.staff.first_name;
                            } else {
                                StudentController.listView.teacher = 'отсутствует';
                            }
                        }
                    });
                }
            });

            StudentController.listView.className = class_name;
            StudentController.listView.teacherPosition = StudentController.translatedTexts.cl_teacher;
            StudentController.listView.classId = class_id;
            StudentController.listView.educationGradeId = education_grade_id;

            UtilsSvc.isAppendLoader(false);

            if (window.alertSuccess) {
                AlertSvc.success($scope, window.alertSuccess);
                window.alertSuccess = false;
            }
            setTimeout(function () {
                Tooltip.init();
            }, 0);

        }, function(error) {
            //console.log(error);
            StudentController.listView.clear();
            if (error.status === 403)
                window.location = angular.baseUrl + "/Users/logout";
            else
                AlertSvc.error($scope, 'Unexpected error! Please, try log in.');
            UtilsSvc.isAppendLoader(false);
        });
    }

    function onPaginateStudents(page, listType, entry, model)
    {
        if (listType === 1) {
            StudentController.onClassClick(StudentController.listView.classId, StudentController.listView.className, StudentController.listView.educationGradeId, page, false);
        }
        else {
            getEntry(entry, model.urlBuild + (model.urlBuild.indexOf('?') > -1 ? '&' : '?') + "page=" + page, model);
        }
    }

    function mathRound(number, func = 'round')
    {
        if (func === 'round') {
            return Math.round(number);
        } else if (func === 'ceil') {
            return Math.ceil(number);
        } else if (func === 'floor') {
            return Math.floor(number);
        }

        return 0;
    }

    function getTabContent(entry, i = 0)
    {
        let keys = Object.keys(entry.editData.tabElements);
        let key = keys && keys[i];
        if (!key || !entry.editData.tabElements[key])
            return;
        if (entry.editData.tabElements[key].active) {
            entry.editData.tabElements[key].data = entry.editData;
            return getTabContent(entry, ++i);
        }
        entry.editData.tabElements[key].data = null;
        InstitutionsStudentsSvc.getRequest(entry.editData.tabElements[key].urlBuild).then(
            function (data) {
                if (!entry.editData || !entry.editData.tabElements || !entry.editData.tabElements[key])
                    return;
                if (data) {
                    entry.editData.tabElements[key].data = data;
                } else {
                    entry.editData.tabElements[key].data = null;
                }
                setTimeout(function () {
                    getTabContent(entry, ++i);
                }, 10, [entry, i]);
            },
            function (error) {
                if (!entry.editData || !entry.editData.tabElements || !entry.editData.tabElements[key])
                    return;
                setTimeout(function () {
                    getTabContent(entry, ++i);
                }, 10, [entry, i]);
            });
    }

    function generateUrl(urlObject)
    {
        if (
            !urlObject
            || !urlObject.plugin
            || !urlObject.controller
        ) {
            return '';
        }

        var url = '', urlParams = {};

        if (!urlObject[0] && urlObject.institutionId) {
            url += '/' + urlObject.plugin + '/' + urlObject.institutionId;
        }

        url += '/' + urlObject.controller;

        var notUrl = ['plugin', 'controller', 'action'];
        for (var urlItem in urlObject) {
            if ($.inArray(urlItem, notUrl) === -1) {
                urlParams[urlItem] = urlObject[urlItem];
            }
        }

        if (urlObject[1]) {
            url += '/' + urlObject[1];
        }
        if (urlObject.action) {
            url += '/' + urlObject.action;
        }
        if (urlObject[0]) {
            url += '/' + urlObject[0];
        }

        url += '?' + $.param(urlParams);

        return url;
    }

    function onClickImport()
    {
        var listView = StudentController.listView;
        var selectedPeriod = StudentController.academicPeriodOptions.selectedOption;

        if (
            !StudentController.institution_id
            || !selectedPeriod
            || !listView.educationGradeId
        ) {
            return false;
        }

        var params = {
            academic_period_id: selectedPeriod.id,
            status_id: 1,
            education_grade_id: listView.educationGradeId
        };

        InstitutionsStudentsSvc.universalQuery('/Institution/Institutions/ImportStudentAdmission/add?' + $.param(params))
            .then(function(data) {
                console.log(data);
            }, function(error) {
                console.log(error);
            });
    }

    function onClickExport()
    {
        var listView = StudentController.listView;
        var selectedPeriod = StudentController.academicPeriodOptions.selectedOption;

        if (
            !StudentController.institution_id
            || !selectedPeriod
            || !listView.educationGradeId
            || !listView.classId
        ) {
            return false;
        }

        window.location = angular.baseUrl + '/Institutions/'+StudentController.institution_id+'/Students/excel'
            +'?academic_period_id='+selectedPeriod.id+'&status_id='+1
            +'&education_grade_id='+listView.educationGradeId+'&class_id='+listView.classId
        ;
    }

    function onClickPromotion()
    {
        InstitutionsStudentsSvc.universalQuery('/Institution/Institutions/Promotion/add')
            .then(function(data) {
                console.log(data);
            }, function(error) {
                console.log(error);
            });
    }

    function onClickTransfer()
    {
        InstitutionsStudentsSvc.universalQuery('/Institution/Institutions/Transfer/add')
            .then(function(data) {
                console.log(data);
            }, function(error) {
                console.log(error);
            });
    }

    function onClickUndo()
    {
        InstitutionsStudentsSvc.universalQuery('/Institution/Institutions/Undo/add')
            .then(function(data) {
                console.log(data);
            }, function(error) {
                console.log(error);
            });
    }

    function onClickSearch(e)
    {
        e.preventDefault();

        var searchInput = angular.element(document).find('#search_input');

        if (e.target.nodeName !== 'FORM') {
            searchInput.val('');
        }

        var searchInputVal = searchInput.val();
        var search = {
            openemis_no: searchInputVal,
            first_name: searchInputVal,
            last_name: searchInputVal,
            identity_number: searchInputVal,
            pin: searchInputVal
        };

        onClassClick(StudentController.listView.classId, StudentController.listView.className, StudentController.listView.educationGradeId, 1, false, search);

        return false;
    }

    function onClickSearch2(e) {
        e.preventDefault();

        UtilsSvc.isAppendLoader(true);
        setTimeout(function () {
            UtilsSvc.isAppendLoader(false);
        }, 1000);

        return false;
    }

    function onClickStudentTrash(e) {
        e.preventDefault();
        let id   = $('#recordId', e.target).val();
        let a    = $("a[field-value='" + id + "']");
        let url  = a.data('url');
        let data = $(e.target).serializeArray();

        InstitutionsStudentsSvc.postRequest(url, data).then(function (data) {
            if (a.closest('.tab-content').closest('.tab-content').length > 0) {
                $timeout(function() {
                    angular.element(document).find('.tab-content .mobile-tab-option.active').triggerHandler('click');
                }, 0);
            } else{
                StudentController.onClassSelect(StudentController.currentPage, false, StudentController.currentSearch);
            }
        }, function (error) {
            console.log(error);
            AlertSvc.error($scope, 'Sorry, there was an error. Please retry your request.');
        });

        $('#delete-modal .close').click();

        return false;
    }

    function onClickDeleteEntry(entry) {
        console.log(entry);
        if (entry._collapsed) {
            entry._collapsed = false;
            return;
        }
        let url = entry.action.delete.urlBuild;
        entry._collapsed = true;
        entry.editData.moreAction = null;
        entry.editData.action = 'edit';
        entry.editData.tabElementsLength = 0;
        entry.editData.iframeUrl = url + '#' + Math.floor(Math.random() * 1000000);
    }

    function onClickStudentAdd()
    {
        StudentController.listView.studentAdding = true;
        StudentController.listView.editData.moreAction = null;
        StudentController.studentAddedLocation = '?student_added=true';
    }

    function onCloseStudentAdd(update = true)
    {
        StudentController.listView.studentAdding = false;
        StudentController.internalGridOptions.columnDefs.shift();
        angular.element(document.querySelector('#wizard')).wizard('selectedItem', {
            step: 'internalSearch'
        });

        if (update) {
            StudentController.onClassSelect(StudentController.currentPage, false, StudentController.currentSearch);
        }
    }

    function onClickStudentEdit(entry) {
        var action = entry.action;
        if (!action.view || entry._collapsed)
            return StudentController.cancelEntry(entry);
        StudentController.listView.closeAll(entry.recordId);
        UtilsSvc.isAppendLoader(true);
        //console.log('entry', entry);
        InstitutionsStudentsSvc.getRequest(action.view.urlBuild).then(function (editAction) {
            //console.log('editAction', editAction);
            if (!!editAction.indexButtons.edit) {
                entry.editAction = editAction.indexButtons;
                entry.viewButtons = editAction.toolbarButtons;
                getEntry(entry, editAction.indexButtons.edit.urlBuild);
            }
            else {
                UtilsSvc.isAppendLoader(false);
                AlertSvc.error($scope, 'Edit is not allowed!');
            }
        },
        function (error) {
            console.log(error);
            if (error.status === 403)
                window.location = angular.baseUrl + "/Users/logout";
            AlertSvc.error($scope, error.statusText);
            UtilsSvc.isAppendLoader(false);
        });
    }

    function cancelEntry(entry){
        AlertSvc.reset($scope);
        entry._collapsed = false;
        entry.iframeUrl = null;
    }
    function isEntryCollapsed(entry) {
        if (entry['_collapsed']) {
            return true;
        }
        else {
            for (var i in entry) {
                if (entry[i]['_collapsed']) {
                    return true;
                }
            }
        }
        return false;
    }
    function resetAlert(){
        AlertSvc.reset($scope);
    }

    function saveEntry(entry) {
        AlertSvc.reset($scope);
        // console.log(entry.editData.data_response);
        if (!StudentController.validateEntry(entry)) {
            UtilsSvc.isAppendLoader(true);
            var fields = entry.editData.data_form_hidden || {};
            for (var key in entry.editData.data_response_attr) {
                var data = fields[entry.editData.data_response_attr[key].attr.model] || {};
                if (key in entry.editData.data_response)
                    data[key] = entry.editData.data_response[key];
                else if ('value' in entry.editData.data_response_attr[key].attr)
                    data[key] = entry.editData.data_response_attr[key].attr.value;
                fields[entry.editData.data_response_attr[key].attr.model] = data;
            }
            //if (!!entry.editData.data_response.custom_field_values)
            //    data.custom_field_values = entry.editData.data_response.custom_field_values;
            //if (!!entry.editData.data_response.custom_table_cells)
            //    data.custom_table_cells = entry.editData.data_response.custom_table_cells;
            //fields[entry.editData.indexButtons.edit.url.action] = data;
            fields['submit'] = 'save';
            InstitutionsStudentsSvc.postRequest(entry.editData.data_form_action, fields)
                .then(function (data) {
                    UtilsSvc.isAppendLoader(false);
                    if (!data.data_response_errors || Object.keys(data.data_response_errors).length === 0) {
                        // Очищаем пароли
                        for (var key in entry.editData.data_response_attr) {
                            if ((key in entry.editData.data_response) && entry.editData.data_response_attr[key].type === 'password')
                                entry.editData.data_response[key] = '';
                        }
                        AlertSvc.success($scope, 'The record has been updated successfully.');
                    } else {
                        StudentController.postResponse.error = data.data_response_errors;
                        //AlertSvc.error($scope, 'Please review the errors in the form.');
                        AlertSvc.error($scope, 'The record is not updated due to errors encountered.');
                    }
                }, function (error) {
                    console.log(error);
                    if (error.status === 403)
                        window.location = angular.baseUrl + "/Users/logout";
                    AlertSvc.error($scope, error.statusText);
                    UtilsSvc.isAppendLoader(false);
                })
        }

    }

    function validateEntry(entry) {
        var empty = {'_empty': 'This field cannot be left empty'};
        InstitutionsStudentsSvc.translate(empty).then(function (data){
            empty._empty = data._empty;
        });
        StudentController.postResponse = {};
        StudentController.postResponse.error = {};
        var remain = false;
        var action = entry.editData.data_form_action;
        if (!action)
            remain = true;
        for (var key in entry.editData.data_response_attr) {
            var field = entry.editData.data_response_attr[key];
            if (field.attr.null === false &&
                field.type != "hidden" &&
                (key in entry.editData.data_response) &&
                !entry.editData.data_response[key] &&
                entry.editData.data_response[key] != 0) {
                StudentController.postResponse.error[key] = empty;
                remain = true;
            }
            if (field.attr.null === false &&
                field.type != "hidden" &&
                ('seq' in field.attr) &&
                !entry.editData.data_response.custom_field_values[field.option.seq] &&
                entry.editData.data_response.custom_field_values[field.option.seq] != 0) {
                StudentController.postResponse.error[key] = empty;
                remain = true;
            }
        }

        if (remain) {
            //AlertSvc.error($scope, 'Please review the errors in the form.');
            AlertSvc.error($scope, 'The record is not updated due to errors encountered.');
        }

        return remain;
    }

    function selectNavigation(entry, nav) {
        getEntry(entry, nav.urlBuild);
    }

    async function getEntryEditData(detail_entry) {
        var action = detail_entry.action;
        if (!action.view || detail_entry._collapsed)
            return StudentController.cancelEntry(detail_entry);
        if (!action.edit && !!action.view) {
            UtilsSvc.isAppendLoader(true);
            InstitutionsStudentsSvc.getRequest(action.view.urlBuild).then(function (editAction) {
                //console.log('editAction', editAction);
                if (!!editAction.indexButtons.edit) {
                    detail_entry.editAction = editAction.indexButtons;
                    getEntry(detail_entry, editAction.indexButtons.edit.urlBuild);
                }
                else {
                    AlertSvc.warning($scope, 'Access denied!');
                    UtilsSvc.isAppendLoader(false);
                }
            });
        }
        else if (!!action.edit) {
            getEntry(detail_entry, action.edit.urlBuild);
        }
    }

    function onCustomEditViewCancel() {
        StudentController.selectedCustomViewEntry = null;
    }

    async function selectModel(entry, model) {
        if (model.url[0] === 'view') {
            UtilsSvc.isAppendLoader(true);
            InstitutionsStudentsSvc.getRequest(model.urlBuild).then(function (editAction) {
                if (!!editAction.indexButtons.edit) {
                    entry.editAction = editAction.indexButtons;
                    getEntry(entry, editAction.indexButtons.edit.urlBuild, model);
                }
                else {
                    AlertSvc.warning($scope, 'Access denied!');
                    UtilsSvc.isAppendLoader(false);
                }
            });
        }
        else {
            getEntry(entry, model.urlBuild, model);
        }

        /*try {
            const res = await InstitutionsStudentsSvc.getRequest(model.urlBuild);
            StudentController.detailListView = StudentController.buildCustomViewObject(res);
        } catch(err) {
            UtilsSvc.isAppendLoader(false);
        }*/
    }

    function isIframe(data){
        let iframe = typeof data == 'string';

        iframe = iframe || data.action !== 'view' && data.action !== 'edit' && data.action !== 'index';

        if (data.action === 'edit') {
            for (var i in data.data_response_attr) {
                let field = data.data_response_attr[i];
                iframe = iframe || field.type === 'chosenSelect';
                iframe = iframe || field.type === 'element';
                //iframe = iframe || field.type === 'image';
            }
        }

        return iframe;
    }

    function dataNormalize(data, oldData, tabElement){
        var tabElements = !!tabElement && oldData != null ? oldData.tabElements : {};

        for (var i in data.data_response_attr) {
            if (data.data_response_attr[i].type == 'date') {
                var date = moment(data.data_response[i]);
                if (date.isValid()) {
                    data.data_response[i] = date.format('DD-MM-YYYY');
                }
                else {
                    data.data_response[i] = '';
                }
            }
            else if (data.data_response_attr[i].type == 'password') {
                data.data_response[i] = '';
            }
        }

        data.tabElementsLength = Object.keys(data.tabElements).length;
        for (var i in tabElements) {
            if (!!data.tabElements[i]) {
                if (data.tabElements[i].active) {
                    data.tabElements[i].data = data;
                }
                else {
                    data.tabElements[i].data = tabElements[i].data;
                }
            }
        }
        return data;
    }

    async function getEntry(entry, url, tabElement) {
        entry.iframeUrl = null;
        UtilsSvc.isAppendLoader(true);
        AlertSvc.reset($scope);
        InstitutionsStudentsSvc.getRequest(url).then(function (data) {
            // console.log('getEntry', data);
            if (isIframe(data)) {
                if (!tabElement) {
                    entry._collapsed = true;
                    entry.editData.tabElementsLength = 0;
                }
                if (!!data.action) {
                    if (data.action === 'edit')
                        entry.editData = dataNormalize(data, entry.editData, tabElement);
                    if (entry.editData.paging)
                        entry.editData.paging.hide = 1;
                    if (data.action !== 'edit')
                        entry.editData.moreAction = data.action;
                }
                else if (entry.editData.action === 'index') {
                    entry.editData.moreAction = 'index';
                }
                else {
                    entry.editData.moreAction = null;
                    entry.editData.action = 'edit';
                    entry.editData.tabElementsLength = 0;
                }
                if (!!entry.editData.iframeUrl && entry.editData.iframeUrl.split('#')[0] === url)
                    UtilsSvc.isAppendLoader(false);
                entry.editData.iframeUrl = url + '#' + Math.floor(Math.random() * 1000000);
                // AlertSvc.info($scope, 'Работа в iframe');
            }
            else if (data.action === 'view') {
                getEntry(entry, data.indexButtons.edit.urlBuild, tabElement);
            }
            else {
                StudentController.selectOptions = [];

                entry.editData = dataNormalize(data, entry.editData, tabElement);

                if (data.action === 'index') {
                    entry.listView = StudentController.getDetailListView(data);
                }
                if (!tabElement)
                    entry._collapsed = true;
                if (entry.editData.action === 'index' && (entry.editData.paging == null || Object.keys(entry.editData.data_response_header || {}).length === 0))
                    AlertSvc.warning($scope, 'No Results');
                UtilsSvc.isAppendLoader(false);

                if (window.alertSuccess) {
                    AlertSvc.success($scope, window.alertSuccess);
                    window.alertSuccess = false;
                }
                setTimeout(function () {
                    Tooltip.init();
                }, 0);
            }
        }, function(error) {
            console.log(error);
            UtilsSvc.isAppendLoader(false);
            if (error.status === 403)
                window.location = angular.baseUrl + "/Users/logout";
            else
                AlertSvc.error($scope, 'Sorry, there was an error. Please retry your request.');
        });
    }

    function getListView(data) {
        const result = StudentController.listView.clear();

        result.editData = data;

        result.actions = data.toolbarButtons;
        result.headers = StudentController.getCustomListViewHeaderTitles(data.data_response_header);
        result.entries = StudentController.getListEntriesFromRepsonseData(data.data_response);

        return result;
    }

    function getDetailListView(data) {
        const result = StudentController.detailListView.clear();

        result.actions = data.toolbarButtons;
        result.headers = StudentController.getCustomListViewHeaderTitles(data.data_response_header);
        result.entries = StudentController.getListEntriesFromRepsonseData(data.data_response);

        return result;
    }

    function detailRecordAction(action, entry, model) {
        StudentController.onCloseStudentAdd(false);
        getEntry(entry, action.urlBuild, model);
    }

    function detailRecordViewAction(btnKey, btnItem, entry) {
        if (btnKey === 'export') {
            window.location = btnItem.urlBuild;
        }
        else if (!!btnItem.urlBuild) {
            if (!entry.iframeUrl || entry.iframeUrl.split('#')[0] !== btnItem.urlBuild)
                UtilsSvc.isAppendLoader(true);
            entry.iframeUrl = btnItem.urlBuild + '#' + Math.floor(Math.random() * 1000000);
        }
    }

    function getCustomViewActions(res) {
        const resultActions = {};
        Object.keys(res.toolbarButtons).forEach(key => {
            resultActions[key] = true;
        });

        return resultActions;
    }

    // function getCustomListViewPaginationInfo(res) { //todo finish building this object
    //     return {                                    //todo also implement pagination nav botton at the custom list table bottom
    //         active: 1,
    //         total: res.data_response.length,
    //         current: 1,
    //         limit: 10,
    //         items: []
    //     };
    // }

    function getCustomListViewHeaderTitles(header) {
        const titles = [];
        for (let i in header) {
            let title = header[i];
            if (typeof title == "string") {
                title = title.replace("/", " или ");
                let $title = null;
                try {
                    $title = $(title);
                }
                catch (e) {
                    $title = $('<span>' + title + '</span>');
                }
                titles.push({
                    title: $title.text() || title,
                    sort: $title.prop("tagName") === 'A' ? {
                        type: $title.hasClass('desc') ? 'desc' : ($title.hasClass('asc') ? 'asc' : '')
                    } : false
                });
            }
        }

        return {
            titles,
            actionTitle: StudentController.translatedTexts.actions
        };
    }

    function getListEntriesFromRepsonseData(responseData) {
        return responseData.length > 0 ? responseData.map((row, i) => {
            return {
                recordId: !!row.id ? row.id : (!!row[0] && !!row[0][1] ? row[0][1]['data-row-id'] : 0),
                data: StudentController.getCustomListViewEntryData(row),
                action: StudentController.getListViewEntryActions(row),
                row: row,
                editData: {},
                _collapsed: false
            };
        }) : [];
    }

    function getCustomListViewEntryData(row) {
        const result = [];
        for (let i in row) {
            const field = row[i];

            if (field && field[1] && field[1]['data-row-id']) {
                result.push({ value: field[0] });
                continue;
            }
            if (field === null || field === undefined) {
                result.push({
                    value: ""
                });
                continue;
            }
            if (!$.isNumeric( i ) || !!field.view || !!field.edit || !!field.remove){
                continue;
            }
            result.push({
                value: field
            });
        }
        return result;
    }

    function closeStudentListView() {
        StudentController.listView.clear();
        StudentController.class = "";
        StudentController.grade = "";
        StudentController.selectedGrade = {};
        StudentController.selectedClass = {};
    }

    /*
        args:
            (Object)field: data_response_attr.<fieldName>
    */
    function setSelectOptions(field) {
        StudentController.selectOptions[field.attr.field] = Object.keys(field.attr.options)
            .map(key => ({ value: key, label: field.attr.options[key] }));
    }

    function sliderTurnOn() {
        $('.slider-tabs').slick({
            slidesToShow: 9,
            slidesToScroll: 9,
            dots: false,
            focusOnSelect: false,
            infinite: false,
            responsive: [
                {
                    breakpoint: 1300,
                    settings: {
                        slidesToShow: 6,
                        slidesToScroll: 6,
                    }
                },{
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 5,
                        slidesToScroll: 5,
                    }
                }, {
                    breakpoint: 640,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3,
                    }
                }, {
                    breakpoint: 420,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2,
                    }
                }]

        })

        var selector2 = '.slider-tabs .slick-items';

        $(selector2).on('click', function(){
            $(selector2).removeClass('this-active');
            $(this).addClass('this-active');
        });

        $('.slider-tabs').on('click', '.slick-slide', function(event) {
            event.preventDefault();
            var goToSingleSlide = $(this).data('slick-index');

            $('.slider-single').slick('slickGoTo', goToSingleSlide);
        });

    }

    function consoleLog(data) {
        console.log("angular.log", data);
    }

    function iframeNormalize(iframe) {
        console.log('iframe', iframe);
    }

    function isAppendLoader(_isAppend, _percent) {
        UtilsSvc.isAppendLoader(_isAppend, _percent);
    }
}
