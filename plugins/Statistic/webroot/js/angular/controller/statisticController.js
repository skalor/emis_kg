var statistic = angular.module('OE_Core', ["bgDirectives","ui.bootstrap","kd-angular-multi-select","ui.bootstrap","ui.bootstrap-slider","ui.tab.scroll","agGrid","app.ctrl","advanced.search.ctrl","kd-elem-sizes","kd-angular-checkbox-radio","multi-select-tree","kd-angular-tree-dropdown","kd-angular-ag-grid","sg.tree.ctrl","sg.tree.svc",'isteven-multi-select']).run(function() {
    agGrid.LicenseManager.setLicenseKey("Community_Solutions_Foundation_CSF_Devs_13_October_2018__MTUzOTM4NTIwMDAwMA==500b28c724d110b0af8aa885bf13c66a");
    angular.baseUrl = '';
    angular.url = function(url) {
        return angular.baseUrl + '/' + url;
    }
})
    .config(['$locationProvider', function($locationProvider){
        $locationProvider.html5Mode({
            enabled: true,
            requireBase: false,
            rewriteLinks: false
        });
    }]);


statistic.filter('spases_number', function() {
    return function(input, uppercase) {
        if(typeof input == "undefined" || input == null || input.length == 0) { return 0; }
        if(input.length < 4) { return input; }
        var nf = new Intl.NumberFormat();
        return nf.format(input);
    };
})
    .controller('StatisticController', ['$scope','$http', function($scope,$http) {

        $scope.selectedOrganization = [];
        var selectedOption = [];
        $http.get('Statistic?api=true&operation=by_type').then(function(response) {

            for(var ind in response.data) {
                selectedOption.push(response.data[ind]);
            }

            $scope.typeOrganizations = {
                availableOptions : selectedOption,
            };

            $scope._selectedOption = selectedOption;
        });

        $scope.fClose = function() {
            console.log( 'On-close' );
            angular.element('.organization_struct').empty();
            _totalCountRegions();
        }


        $scope.opopenOrganization = function( organization ) {
            console.log(organization);
        }

        $scope.hidden = true;

        $http.get('Statistic?api=true&operation=by_region').then(function(response) {
            $scope.regions = response.data;
            $scope.static_regions = response.data;
            console.log(response.data);
            $http.get('Statistic?api=true&operation=by_district').then(function(response) {
                $scope.districts = response.data;
                $scope.static_districts = response.data;
                $http.get('Statistic?api=true&operation=by_organization').then(function(response) {
                    $scope.organizations = response.data;
                    $scope.static_organizations = response.data;
                    $scope.totalCountRegions();

                });
            });
        });

        $scope.showDistrict = function (region, key) {
            var regionName;
            region._hidden = !region._hidden;
            if(region._active == 0) {
                region._active = 'active';
            } else {
                region._active = '';
            }
        }

        $scope.totalCountRegions = function (type_oraganization) {
            $scope.region_total = {};
            $scope.region_total['org']                  = 0;
            $scope.region_total['staff_total_count']    = 0;
            $scope.region_total['staff_male_count']     = 0;
            $scope.region_total['staff_female_count']   = 0;
            $scope.region_total['student_total_count']  = 0;
            $scope.region_total['student_male_count']   = 0;
            $scope.region_total['student_female_count'] = 0;
            $scope._regions = {};

            for(var ind_region in $scope.regions) {
                region_name = $scope.regions[ind_region]['region'];
                $scope.region_total['org']                  += parseInt($scope.regions[ind_region]['org']);
                $scope.region_total['staff_total_count']    += parseInt($scope.regions[ind_region]['staff_total_count']);
                $scope.region_total['staff_male_count']     += parseInt($scope.regions[ind_region]['staff_male_count']);
                $scope.region_total['staff_female_count']   += parseInt($scope.regions[ind_region]['staff_female_count']);
                $scope.region_total['student_total_count']  += parseInt($scope.regions[ind_region]['student_total_count']);
                $scope.region_total['student_male_count']   += parseInt($scope.regions[ind_region]['student_male_count']);
                $scope.region_total['student_female_count'] += parseInt($scope.regions[ind_region]['student_female_count']);

                $scope._regions[region_name] = {};
                $scope._regions[region_name]['org']                   = 0;
                $scope._regions[region_name]['staff_total_count']     = 0;
                $scope._regions[region_name]['staff_male_count']      = 0;
                $scope._regions[region_name]['student_total_count']   = 0;
                $scope._regions[region_name]['student_total_count']   = 0;
                $scope._regions[region_name]['student_male_count']    = 0;
                $scope._regions[region_name]['student_female_count']  = 0;

                for(var ind_district in $scope.districts[region_name]) {
                    $scope._regions[region_name]['org']                   += parseInt($scope.districts[region_name][ind_district]['org']);
                    $scope._regions[region_name]['staff_total_count']     += parseInt($scope.districts[region_name][ind_district]['staff_total_count']);
                    $scope._regions[region_name]['staff_male_count']      += parseInt($scope.districts[region_name][ind_district]['staff_male_count']);
                    $scope._regions[region_name]['student_total_count']   += parseInt($scope.districts[region_name][ind_district]['student_total_count']);
                    $scope._regions[region_name]['student_total_count']   += parseInt($scope.districts[region_name][ind_district]['student_total_count']);
                    $scope._regions[region_name]['student_male_count']    += parseInt($scope.districts[region_name][ind_district]['student_male_count']);
                    $scope._regions[region_name]['student_female_count']  += parseInt($scope.districts[region_name][ind_district]['student_female_count']);
                }
            }
        };

        var templateOrgatization = '',lastIndex;
        $scope._showOrganizations = function(district,key) {
            console.log(district,key);
            $scope._organizations = $scope.templateOrgatization;
            angular.element('.organization_struct').closest('.district').each(function(index,elem){
                if( $(elem).hasClass('active') ) {
                    $(elem).removeClass('active')
                }
            });

            angular.element('.organization_struct').empty();
            if(lastIndex == key) {
                $('.organization_struct').empty();

                lastIndex = '';
                return;
            } else {
                lastIndex = key;
            }

            angular.element('#item-'+key).parent().addClass('active');

            var staff_total_count       = 0;
            var staff_male_count        = 0;
            var staff_female_count      = 0;
            var student_total_count     = 0;
            var student_male_count      = 0;
            var student_female_count    = 0;
            var total_org               = 0;
            templateOrgatization        = '';

            data_org_select = [];

            for(var index_sel_org in $scope.selectedOrganization) {
                data_org_select.push($scope.selectedOrganization[index_sel_org]['id']);
            }

            for(var index_org in $scope.organizations[district]) {
                org_data = $scope.organizations[district][index_org];

                if (data_org_select.length != 0) {
                    if(data_org_select.indexOf(org_data['type_organization']) == -1) {
                        continue;
                    }
                }

                staff_total_count       += parseInt(org_data['staff_total_count']);
                staff_male_count        += parseInt(org_data['staff_male_count']);
                staff_female_count      += parseInt(org_data['staff_female_count']);
                student_total_count     += parseInt(org_data['student_total_count']);
                student_male_count      += parseInt(org_data['student_male_count']);
                student_female_count    += parseInt(org_data['student_female_count']);
                total_org               += 1;

                templateOrgatization +=
                    '<div class=\'border-bottom accordion-title\'>\n' +
                    '<ul class=\'container-fluid ul-content\'>\n' +
                    '<li class=\'col-3 col-sm-3 col-lg-3 col-md-3\' >' +org_data.org_struct+ '</li>\n' +
                    '<li class=\'col-3 col-sm-3 col-lg-3 col-md-3\' >1</li>\n' +
                    '<li class=\'col-1 col-sm-1 col-lg-1 col-md-1\' >' +org_data['staff_total_count']+ '</li>\n' +
                    '<li class=\'col-1 col-sm-1 col-lg-1 col-md-1\' >' +org_data['staff_male_count']+ '</li>\n' +
                    '<li class=\'col-1 col-sm-1 col-lg-1 col-md-1\' >' +org_data['staff_female_count']+ '</li>\n' +
                    '<li class=\'col-1 col-sm-1 col-lg-1 col-md-1\' >' +org_data['student_total_count']+ '</li>\n' +
                    '<li class=\'col-1 col-sm-1 col-lg-1 col-md-1\' >' +org_data['student_male_count']+' </li>\n' +
                    '<li class=\'col-1 col-sm-1 col-lg-1 col-md-1\' >' +org_data['student_female_count']+ '</li>\n' +
                    '</ul>\n' +
                    '</div>\n';
            }

            // console.log(templateOrgatization);
            angular.element('.organization_struct').empty();
            angular.element('#item-'+key).append(templateOrgatization);
        }

        //sorted by organization


        $('#type_organization').change(function() {
            $('.organization_struct').empty();
            $('.district.active').removeClass('active');
            var type_org = $('#type_organization').find('option:selected').val();
            _totalCountRegions(type_org);
        });

        var data_org_select = [];
        //resum total for regions and district
        var _totalCountRegions = function (type_oraganization) {
            data_org_select = [];

            for(var index_sel_org in $scope.selectedOrganization) {
                data_org_select.push($scope.selectedOrganization[index_sel_org]['id']);
            }

            console.log(data_org_select);
            $scope.region_total = {};

            $scope.region_total['org']                  = 0;
            $scope.region_total['staff_total_count']    = 0;
            $scope.region_total['staff_male_count']     = 0;
            $scope.region_total['staff_female_count']   = 0;
            $scope.region_total['student_total_count']  = 0;
            $scope.region_total['student_male_count']   = 0;
            $scope.region_total['student_female_count'] = 0;
            $scope._regions = {};

            // var tmp = [];
            for(var ind_region in $scope.static_regions) {
                region_name = $scope.static_regions[ind_region]['region'];
                $scope._regions[region_name] = {};
                $scope._regions[region_name]['org']                   = 0;
                $scope._regions[region_name]['staff_total_count']     = 0;
                $scope._regions[region_name]['staff_male_count']      = 0;
                $scope._regions[region_name]['student_total_count']   = 0;
                $scope._regions[region_name]['student_total_count']   = 0;
                $scope._regions[region_name]['student_male_count']    = 0;
                $scope._regions[region_name]['student_female_count']  = 0;

                // console.log($scope.regions[ind_region],region_name);
                $scope.regions[ind_region]['org']                   = 0;
                $scope.regions[ind_region]['staff_total_count']     = 0;
                $scope.regions[ind_region]['staff_male_count']      = 0;
                $scope.regions[ind_region]['staff_female_count']    = 0;
                $scope.regions[ind_region]['student_total_count']   = 0;
                $scope.regions[ind_region]['student_total_count']   = 0;
                $scope.regions[ind_region]['student_male_count']    = 0;
                $scope.regions[ind_region]['student_female_count']  = 0;



                for(var ind_district in $scope.static_districts[region_name]) {
                    district_name = $scope.static_districts[region_name][ind_district]['name'];
                    // console.log($scope.districts[district_name],$scope.districts);

                    $scope.districts[region_name][ind_district]['org']                   = 0;
                    $scope.districts[region_name][ind_district]['staff_total_count']     = 0;
                    $scope.districts[region_name][ind_district]['staff_male_count']      = 0;
                    $scope.districts[region_name][ind_district]['staff_female_count']    = 0;
                    $scope.districts[region_name][ind_district]['student_total_count']   = 0;
                    $scope.districts[region_name][ind_district]['student_male_count']    = 0;
                    $scope.districts[region_name][ind_district]['student_female_count']  = 0;

                    // tmp.push(district_name);
                    if (data_org_select.length != 0) {
                        if (typeof $scope.static_organizations[district_name] == "undefined") {
                            console.log('continue');
                            continue;
                        }
                    }


                    for(index_organization in $scope.static_organizations[district_name]) {

                        _type_organization = $scope.static_organizations[district_name][index_organization]['type_organization'];

                        if (data_org_select.length != 0) {
                            if( data_org_select.indexOf(_type_organization) == -1) {
                                continue;
                            }
                        }

                        org_data = $scope.static_organizations[district_name][index_organization];
                        $scope.region_total['org']                  += 1;
                        $scope.region_total['staff_total_count']    += parseInt(org_data['staff_total_count']);
                        $scope.region_total['staff_male_count']     += parseInt(org_data['staff_male_count']);
                        $scope.region_total['staff_female_count']   += parseInt(org_data['staff_female_count']);
                        $scope.region_total['student_total_count']  += parseInt(org_data['student_total_count']);
                        $scope.region_total['student_male_count']   += parseInt(org_data['student_male_count']);
                        $scope.region_total['student_female_count'] += parseInt(org_data['student_female_count']);

                        _region_name = org_data['region'];

                        $scope._regions[_region_name]['org']                   += 1;
                        $scope._regions[_region_name]['staff_total_count']     += parseInt(org_data['staff_total_count']);
                        $scope._regions[_region_name]['staff_male_count']      += parseInt(org_data['staff_male_count']);
                        $scope._regions[_region_name]['staff_female_count']    += parseInt(org_data['staff_female_count']);
                        $scope._regions[_region_name]['student_total_count']   += parseInt(org_data['student_total_count']);
                        $scope._regions[_region_name]['student_male_count']    += parseInt(org_data['student_male_count']);
                        $scope._regions[_region_name]['student_female_count']  += parseInt(org_data['student_female_count']);


                        if( $scope.static_regions[ind_region]['region'] == org_data['region'] ) {
                            $scope.regions[ind_region]['org']                   += 1;
                            $scope.regions[ind_region]['staff_total_count']     += parseInt(org_data['staff_total_count']);
                            $scope.regions[ind_region]['staff_male_count']      += parseInt(org_data['staff_male_count']);
                            $scope.regions[ind_region]['staff_female_count']    += parseInt(org_data['staff_female_count']);
                            $scope.regions[ind_region]['student_total_count']   += parseInt(org_data['student_total_count']);
                            $scope.regions[ind_region]['student_male_count']    += parseInt(org_data['student_male_count']);
                            $scope.regions[ind_region]['student_female_count']  += parseInt(org_data['student_female_count']);
                        }

                        $scope.districts[region_name][ind_district]['org']                   += 1;
                        $scope.districts[region_name][ind_district]['staff_total_count']     += parseInt(org_data['staff_total_count']);
                        $scope.districts[region_name][ind_district]['staff_male_count']      += parseInt(org_data['staff_male_count']);
                        $scope.districts[region_name][ind_district]['staff_female_count']    += parseInt(org_data['staff_female_count']);
                        $scope.districts[region_name][ind_district]['student_total_count']   += parseInt(org_data['student_total_count']);
                        $scope.districts[region_name][ind_district]['student_male_count']    += parseInt(org_data['student_male_count']);
                        $scope.districts[region_name][ind_district]['student_female_count']  += parseInt(org_data['student_female_count']);
                    }
                }
            }

            // console.log(tmp.length);
        };

    }]);
