angular
    .module('institution.class.students.svc', ['kd.data.svc'])

    // Class name input validation
    // Bakay
    .controller('inputClassValidationCtr', ['$scope', function ($scope) {
        $scope.$watch('InstitutionClassStudentsController.className', function (newValue, oldValue) {
            if (/^[0-9]{1,2} [A-zА-я-]{1,2}$/.test($scope.InstitutionClassStudentsController.firstClassName)) {
                $scope.InstitutionClassStudentsController.className = capitalize(newValue);
                let numeralLength = +$scope.InstitutionClassStudentsController.firstClassName.substr(0, $scope.InstitutionClassStudentsController.firstClassName.indexOf(' ')).length;
                if (numeralLength === 1) {
                    if (newValue.length < 2 || newValue.length > 4) {
                        return $scope.InstitutionClassStudentsController.className = $scope.InstitutionClassStudentsController.firstClassName.slice(0,2);
                    }
                }
                if (numeralLength === 2) {
                    if (newValue.length < 3 || newValue.length > 5) {
                        return $scope.InstitutionClassStudentsController.className = $scope.InstitutionClassStudentsController.firstClassName.slice(0,3);
                    }
                }
                let validLength = numeralLength + 1;
                if (checkChanges(validLength, $scope.InstitutionClassStudentsController.firstClassName, newValue)) {
                    return $scope.InstitutionClassStudentsController.className = $scope.InstitutionClassStudentsController.firstClassName;
                } else {
                    return $scope.InstitutionClassStudentsController.className.slice(2);
                }
            }
        });
    }])
    .service('InstitutionClassStudentsSvc', InstitutionClassStudentsSvc);

// Added Bakay
// Capitalize class text
function capitalize(str)  {
    return str.replace(/(?:^|\s|["'([{])+\S/g, match => match.toUpperCase());
}

// Check changes input form
// Bakay
function checkChanges(targetValueLength, targetValue, inputValue) {
    for (let i = 0; i < targetValueLength; i++) {
        if (targetValue[i] !== inputValue[i])
            return true;
    }
    return false;
}

InstitutionClassStudentsSvc.$inject = ['$http', '$q', '$filter', 'KdDataSvc'];

function InstitutionClassStudentsSvc($http, $q, $filter, KdDataSvc) {

    var service = {
        init: init,
        getClassDetails: getClassDetails,
        getUnassignedStudent: getUnassignedStudent,
        translate: translate,
        getInstitutionShifts: getInstitutionShifts,
        getTeacherOptions: getTeacherOptions,
        saveClass: saveClass,
        getConfigItemValue: getConfigItemValue
    };

    var models = {
        InstitutionStaff: 'Institution.Staff',
        InstitutionClasses: 'Institution.InstitutionClasses',
        InstitutionShifts: 'Institution.InstitutionShifts',
        Users: 'User.Users',
        ConfigItemsTable: 'Configuration.ConfigItems'
    };

    return service;

    function init(baseUrl) {
        KdDataSvc.base(baseUrl);
        KdDataSvc.controllerAction('ClassStudents');
        KdDataSvc.init(models);
    };

    function translate(data) {
        KdDataSvc.init({translation: 'translate'});
        var success = function(response, deferred) {
            var translated = response.data.translated;
            deferred.resolve(translated);
        };
        return translation.translate(data, {success:success, defer: true});
    }

    function getClassDetails(classId) {
        var success = function(response, deferred) {
            deferred.resolve(response.data.data);
        };
        return InstitutionClasses
            .get(classId)
            .find('classDetails')
            .ajax({success: success, defer:true});
    }

    function getUnassignedStudent(classId) {
        var success = function(response, deferred) {
            deferred.resolve(response.data.data);
        };
        return Users.find('InstitutionStudentsNotInClass', {institution_class_id: classId}).ajax({success: success, defer: true});
    }

    function getInstitutionShifts(institutionId, academicPeriodId) {
        var success = function(response, deferred) {
            deferred.resolve(response.data.data);
        };
        return InstitutionShifts.find('shiftOptions', {institution_id: institutionId, academic_period_id: academicPeriodId}).ajax({success: success, defer: true});
    }

    function getTeacherOptions(institutionId, academicPeriodId) {
        var success = function(response, deferred) {
            deferred.resolve(response.data.data);
        };
        return InstitutionStaff.find('classStaffOptions', {institution_id: institutionId, academic_period_id: academicPeriodId}).ajax({success: success, defer: true});
    }

    function getConfigItemValue(code) {
        var success = function(response, deferred) {
            var results = response.data.data;
            if (angular.isObject(results) && results.length > 0) {
                var configItemValue = (results[0].value.length > 0) ? results[0].value : results[0].default_value;
                deferred.resolve(configItemValue);
            } else {
                deferred.reject('There is no ' + code + ' configured');
            }
        };

        return ConfigItemsTable
            .where({code: code})
            .ajax({success: success, defer: true});
    };

    function saveClass(data) {
        InstitutionClasses.reset();
        return InstitutionClasses.edit(data);
    }
};
