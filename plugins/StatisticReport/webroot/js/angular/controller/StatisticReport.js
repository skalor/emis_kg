var statisticReport = angular.module('OE_Core', ["ui.bootstrap", "kd-angular-multi-select", "ui.bootstrap", "ui.bootstrap-slider", "ui.tab.scroll", "agGrid", "app.ctrl", "advanced.search.ctrl", "kd-elem-sizes", "kd-angular-checkbox-radio", "multi-select-tree", "kd-angular-tree-dropdown", "kd-angular-ag-grid", "sg.tree.ctrl", "sg.tree.svc"]).run(function () {
    agGrid.LicenseManager.setLicenseKey("Community_Solutions_Foundation_CSF_Devs_13_October_2018__MTUzOTM4NTIwMDAwMA==500b28c724d110b0af8aa885bf13c66a");
    angular.baseUrl = '';
    angular.url = function (url) {
        return angular.baseUrl + '/' + url;
    }
})
    .config(['$locationProvider', function ($locationProvider) {
        $locationProvider.html5Mode({
            enabled: true,
            requireBase: false,
            rewriteLinks: false
        });
    }]);


statisticReport.controller('StatisticReport', ['$scope', '$http',
    function ($scope, $http) {
        $scope.region   = '';
        $scope.district = '';
        $scope.type     = '';

        $http.get('/core-old/StatisticReport/api?key=picklist').then(function (result) {
            $scope.pikclist = result.data;
            // PickListDependency('[name="StatisticReport[region]"]','[name="StatisticReport[districts]"]','districts');
            // PickListDependency('[name="StatisticReport[districts]"]','[name="StatisticReport[institutions_id]"]','orgs');
            // PickListDependency('[name="StatisticReport[institution_types_id]"]','[name="StatisticReport[institutions_id]"]','orgs');
            angular.element('[name="StatisticReport[region]"]').trigger('change');
        });

        var PickListDependency = function (src,target,type) {
            angular.element(src).change(function(event){
                options = '<option value="">Все</option>';

                if( src == '[name="StatisticReport[institution_types_id]"]' ) {
                    val = angular.element('[name="StatisticReport[districts]"]').val();
                } else {
                    val = angular.element(event.currentTarget).val();
                }

                selected = $scope.pikclist[type][val];
                selected_type = angular.element('[name="StatisticReport[institution_types_id]"]').val();
                
                if(type == 'orgs' && selected_type != 'all' && val.length == 0) {
                    _region     = angular.element('[name="StatisticReport[region]"]').val();
                    _districts  = Object.keys($scope.pikclist['districts'][_region]);
                    for( var _index in _districts) {
                        for( var index in $scope.pikclist['orgs'][_districts[_index]] ) {
                            options += "<option value='" + $scope.pikclist['orgs'][_districts[_index]][index]['value'] + "'>" + $scope.pikclist['orgs'][_districts[_index]][index]['name'] + "</option>";
                        }
                    }
                } else {
                    for( var index in selected ) {
                        if(type == 'orgs' && selected_type != 'all' && val.length > 0) {
                            if(selected[index]['type'] != selected_type) {
                                continue;
                            }
                        }
                        options += "<option value='" + selected[index]['value'] + "'>" + selected[index]['name'] + "</option>";
                    }
                }

                angular.element(target).empty();
                angular.element(target).append(options);
                angular.element(target).trigger('change');
            })
        }

    }]
);
