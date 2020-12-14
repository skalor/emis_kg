angular.module('security.permission.edit.ctrl', ['utils.svc', 'alert.svc', 'security.permission.edit.svc'])
    .controller('SecurityPermissionEditCtrl', SecurityPermissionEditController);

SecurityPermissionEditController.$inject = ['$scope', '$q', '$window', '$http', 'UtilsSvc', 'AlertSvc', 'SecurityPermissionEditSvc'];

function SecurityPermissionEditController($scope, $q, $window, $http, UtilsSvc, AlertSvc, SecurityPermissionEditSvc) {
    $http.defaults.headers.post['X-CSRF-Token'] = SecurityPermissionEditSvc.getCsrfToken();

    var Controller = this;

    // variable
    Controller.modules = [
        {
            'key': 'Institutions',
            'name': 'Institutions'
        },
        {
            'key': 'Directory',
            'name': 'Directory'
        },
        {
            'key': 'Reports',
            'name': 'Reports'
        },
        {
            'key': 'Administration',
            'name': 'Administration'
        }
    ];

    Controller.selectedModule = 'Institutions';
    Controller.roleId = 0;
    Controller.roleData = null;
    Controller.pageSections = [];
    Controller.originalPageSections = [];
    Controller.redirectUrl = '';
    Controller.ready = false;
    $scope.data = {institutionType: null};

    // function
    Controller.changeModule = changeModule;
    Controller.checkAllInSection = checkAllInSection;
    Controller.postForm = postForm;
    Controller.formatSections = formatSections;
    Controller.updateQueryStringParameter = updateQueryStringParameter;
    Controller.changePermission = changePermission;
    Controller.setPermission = setPermission;
    Controller.moduleKey = '';

    angular.element(document).ready(function () {
        SecurityPermissionEditSvc.init(angular.baseUrl);
        Controller.ready = false;
        var module = Controller.modules[0].key;

        // Commented out as ui-tab is not able to support default selection for now,
        // please uncomment when ui-tab is fixed

        // if (Controller.moduleKey != '') {
        //     module = Controller.moduleKey;
        // } else {
            Controller.moduleKey = module;
        // }

        UtilsSvc.isAppendLoader(true);
        SecurityPermissionEditSvc.getPermissions(Controller.roleId, module)
        .then(function(permissions) {
            Controller.pageSections = Controller.formatSections(permissions);
            Controller.originalPageSections = angular.copy(Controller.pageSections);
            angular.forEach(Controller.pageSections, function (value, key) {
                angular.forEach(value.items, function (v, k) {
                    if (v._view == null) {
                        v.Permissions._view = 0;
                    }
                    if (v._edit == null) {
                        v.Permissions._edit = 0;
                    }
                    if (v._add == null) {
                        v.Permissions._add = 0;
                    }
                    if (v._delete == null) {
                        v.Permissions._delete = 0;
                    }
                    if (v._execute == null) {
                        v.Permissions._execute = 0;
                    }
                });
            });
            Controller.ready = true;
            UtilsSvc.isAppendLoader(false);
        }, function(error) {
            console.log(error);
            UtilsSvc.isAppendLoader(false);
        });
    });

    function formatSections(permissions)
    {
        var sections = [];
        var previousCategory = [];
        var counter = -1;
        var tmpSection = {};
        angular.forEach(permissions, function(value, key) {
            if (previousCategory.indexOf(value.category) === -1) {
                counter++;
                previousCategory.push(value.category);
                tmpSection[value.category] = {items: [value], name: value.category, enabled: 0, counter: counter};
                angular.forEach(value.Permissions, function(val, k) {
                    value.Permissions[k] = parseInt(val);
                    if (k != 'id' && val > 0) {
                        tmpSection[value.category]['enabled'] = 1;
                    }
                });
            } else {
                angular.forEach(value.Permissions, function(val, k) {
                    value.Permissions[k] = parseInt(val);
                    if (k != 'id' && val > 0) {
                        tmpSection[value.category]['enabled'] = 1;
                    }
                });
                tmpSection[value.category]['items'].push(value);
            }
        });
        angular.forEach(tmpSection, function(value, key) {
            sections[value.counter] = value;
        });

        return sections;
    }


    function changeModule (module) {
        Controller.ready = false;
        UtilsSvc.isAppendLoader(true);
        Controller.moduleKey = module.key;
        Controller.redirectUrl = Controller.updateQueryStringParameter(Controller.redirectUrl, 'module', Controller.moduleKey);
        document.getElementById("back_url").href = Controller.redirectUrl;
        SecurityPermissionEditSvc.getPermissions(Controller.roleId, module.key)
        .then(function(permissions) {
            Controller.pageSections = Controller.formatSections(permissions);
            Controller.originalPageSections = angular.copy(Controller.pageSections);
            angular.forEach(Controller.pageSections, function (value, key) {
                angular.forEach(value.items, function (v, k) {
                    if (v._view == null) {
                        v.Permissions._view = 0;
                    }
                    if (v._edit == null) {
                        v.Permissions._edit = 0;
                    }
                    if (v._add == null) {
                        v.Permissions._add = 0;
                    }
                    if (v._delete == null) {
                        v.Permissions._delete = 0;
                    }
                    if (v._execute == null) {
                        v.Permissions._execute = 0;
                    }
                });
            });
            Controller.ready = true;
            UtilsSvc.isAppendLoader(false);
        }, function(error) {
            console.log(error);
            UtilsSvc.isAppendLoader(false);
        });
    }

    function changePermission(functionArr, type, value)
    {
        switch (type) {
            case 'view':
                if (value == 0) {
                    Controller.setPermission(functionArr, 'edit', 0);
                    Controller.setPermission(functionArr, 'add', 0);
                    Controller.setPermission(functionArr, 'delete', 0);
                    Controller.setPermission(functionArr, 'execute', 0);
                }
                break;
            case 'edit':
                if (value == 0) {
                    Controller.setPermission(functionArr, 'add', 0);
                    Controller.setPermission(functionArr, 'delete', 0);
                } else {
                    Controller.setPermission(functionArr, 'view', 1);
                }
                break;
            case 'add':
                if (value == 0) {
                    Controller.setPermission(functionArr, 'delete', 0);
                } else {
                    Controller.setPermission(functionArr, 'view', 1);
                    Controller.setPermission(functionArr, 'edit', 1);
                }
                break;
            case 'delete':
                if (value == 0) {
                    Controller.setPermission(functionArr, 'delete', 0);
                } else {
                    Controller.setPermission(functionArr, 'view', 1);
                    Controller.setPermission(functionArr, 'edit', 1);
                    Controller.setPermission(functionArr, 'add', 1);
                }
                break;
            case 'execute':
                if (value == 1) {
                    Controller.setPermission(functionArr, 'view', 1);
                }
                break;
        }
    }

    function setPermission(permission, type, value)
    {
        var typeName = '_' + type;
        if (permission[typeName] == null) {
            permission['Permissions'][typeName] = 0;
        } else {
            permission['Permissions'][typeName] = value;
        }
    }

    function checkAllInSection(key) {
        var enabled = Controller.pageSections[key]['enabled'];
        angular.forEach(Controller.pageSections[key]['items'], function(value, key) {
            if (value._view != null) {
                value.Permissions._view = enabled;
            }
            if (value._edit != null) {
                value.Permissions._edit = enabled;
            }
            if (value._add != null) {
                value.Permissions._add = enabled;
            }
            if (value._delete != null) {
                value.Permissions._delete = enabled;
            }
            if (value._execute != null) {
                value.Permissions._execute = enabled;
            }
        });
    }

    function updateQueryStringParameter(uri, key, value) {
        var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        var separator = uri.indexOf('?') !== -1 ? "&" : "?";
        if (uri.match(re)) {
            return uri.replace(re, '$1' + key + "=" + value + '$2');
        }
        else {
            return uri + separator + key + "=" + value;
        }
    }

    function postForm() {
        var permissions = [];
        var originalPageSections = Controller.originalPageSections;
        for (var i = 0; i < Controller.pageSections.length; i++) {
            var section = Controller.pageSections[i];
            for (var j = 0; j < section.items.length ; j++) {
                // Logic to save only items that are modified
                if (section.items[j].Permissions._view != originalPageSections[i].items[j].Permissions._view
                    || section.items[j].Permissions._edit != originalPageSections[i].items[j].Permissions._edit
                    || section.items[j].Permissions._add != originalPageSections[i].items[j].Permissions._add
                    || section.items[j].Permissions._delete != originalPageSections[i].items[j].Permissions._delete
                    || section.items[j].Permissions._execute != originalPageSections[i].items[j].Permissions._execute) {
                    var securityFunction = {'id': section.items[j].id, '_joinData': section.items[j].Permissions};
                    permissions.push(securityFunction);
                }
            }
        }
        permissions = UtilsSvc.urlsafeBase64Encode(JSON.stringify(permissions));

        var postData = {
            'id': Controller.roleId,
            'security_functions': permissions
        };
        UtilsSvc.isAppendLoader(true);
        SecurityPermissionEditSvc.getRoleData(Controller.roleId)
        .then(function(response) {
            Controller.roleData = response.data;

            postData['name'] = Controller.roleData.name;
            postData['code'] = Controller.roleData.code;
            postData['order'] = Controller.roleData.order;
            postData['visible'] = Controller.roleData.visible;
            postData['security_group_id'] = Controller.roleData.security_group_id;

            return SecurityPermissionEditSvc.savePermissions(postData);
        }, function(error){
            console.log(error);
            AlertSvc.warning(Controller, 'The record does not exist.');
            UtilsSvc.isAppendLoader(false);
        })
        .then(function(response) {
            var error = response.data.error;
            if (error instanceof Array && error.length == 0) {
                Controller.alertUrl = Controller.updateQueryStringParameter(Controller.alertUrl, 'alertType', 'success');
                Controller.alertUrl = Controller.updateQueryStringParameter(Controller.alertUrl, 'message', 'general.edit.success');
                Controller.redirectUrl = Controller.updateQueryStringParameter(Controller.redirectUrl, 'module', Controller.moduleKey);
                $http.get(Controller.alertUrl)
                .then(function(response) {
                    $window.location.href = Controller.redirectUrl;
                    UtilsSvc.isAppendLoader(false);
                }, function (error) {
                    console.log(error);
                    UtilsSvc.isAppendLoader(false);
                });
            } else {
                AlertSvc.error(Controller, 'The record is not updated due to errors encountered.');
                UtilsSvc.isAppendLoader(false);
            }
        }, function(error){
            console.log(error);
        });
    }

    const getFields = async id => {
        let response = await request(getUrl('ModuleFields', id));
        if(!response) return [];

        return response.data.hasOwnProperty('fields') ? Object.values(response.data.fields) : [];
    };

    $scope.showFields = async id => {
        $scope.fields = (await getFields(id)).map(field => {
            if(field.state === null) {
                field.state = 'edit';
            }

            field.changed = false;

            return field;
        });
    };

    $scope.saveFields = async id => {
        const fields = $scope.fields.filter(field => field.changed === true);
        await request(getUrl('saveModuleFields', id), {fields});
    };

    const request = async (url, params = {}) => {
        return await load(() => {
            const role = this.roleId, institutionType = $scope.data.institutionType;
            return $http.post(url, {...params, role, institutionType});
        });
    };

    const load = async handler => {
        let data;

        try {
            UtilsSvc.isAppendLoader(true);
            data = await handler();
        } catch (e) {} finally {
            UtilsSvc.isAppendLoader(false);
        }

        return data;
    }

    $scope.showContainer = null;

    $scope.toggle = id => {
        $scope.fields = [];
        $scope.data.institutionType = null;
        if($scope.showContainer === id) {
            $scope.hide();
        } else {
            $scope.showContainer = id;
        }
        setTimeout(scrollToTop);
    };

    $scope.hide = () => {
        $scope.showContainer = null;
    };

    $scope.changed = field => field.changed = true;

    const scrollToTop = () => {
        const hider = document.querySelector('.hider');
        if(hider && hider.parentElement) {
            hider.parentElement.scrollIntoView(true);
        }
    }

    const getUrl = (action, id) => `${angular.baseUrl}/Securities/${action}?model=${id}`;

    $scope.notHiddables = [];

    const setNotHiddables = async () => {
        let response = await request(getUrl('UnhiddableFunctions', 1));

        $scope.notHiddables = response.data.notHiddables;
    };

    $scope.isNotHiddable = (id) => {
        return $scope.notHiddables.indexOf(id) === -1;
    }

    setNotHiddables();
}
