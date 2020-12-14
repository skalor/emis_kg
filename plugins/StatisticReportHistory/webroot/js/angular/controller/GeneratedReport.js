var GeneratedReport = angular.module('OE_Core', ["app.ctrl"]).run(function() {
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

GeneratedReport.controller('GeneratedReport', ['$scope','$rootScope','$http',function ($scope,$rootScope,$http) {
    var generete_report     = angular.element('[name="generate-report"]');
    var modal_container     = angular.element('.modal_container');
    var modal_closer        = angular.element('.modal_closer');
    var contenainer         = angular.element('.body-header');
    var buttonCancel        = angular.element('.cancelModal');
    var buttonGenerate      = angular.element('.generateReport');
    var periodRadioVal      = angular.element('[name="period"]:checked').val();
    var periodRadio         = angular.element('[name="period"]');

    generete_report.click(function(event){
        var current_target = angular.element(event);
        var template_id = angular.element('#generated-report').val();
        $http.get(angular.baseUrl + '/StatisticReportHistory/quickCreate/'+template_id).then( function( response ) {
            var content = response.data;
            //console.log(content);
            contenainer.empty();
            contenainer.prepend(content);
            modal_container.css({'display':'block'});
            chahgePeriodAndRegion();
        });
    });

    buttonCancel.click(function() {
        modal_container.css( {'display':'none'} );
    });

    angular.element('[name="generated_report"]').change( function() {
        var genereteReportVal = angular.element('[name="generated_report"]').find('option:selected').val();
        console.log(genereteReportVal);
        if( genereteReportVal.length == 0 ) {
            angular.element('.generate-report-event').attr({disabled:true});
        } else {
            angular.element('.generate-report-event').removeAttr('disabled');
        }
    });

    angular.element('[name="generated_report"]').trigger('change');

    buttonGenerate.click(function() {
        var form = angular.element('#modal-body').find('form').serializeArray();
        template = angular.element('#generated-report').val();
        form.push({name:'template_id',value:template});
        $http.post( angular.baseUrl + `/StatisticReportHistory/createRecord`, form ).then(function(response) {
            templateCode = response.data.template;
            console.log(templateCode);
            url = angular.baseUrl + '/TemplateReport/' + templateCode;
            location.href = url;
            setTimeout(function() {
                location.reload();
            },1600);
        },function(error){
            console.log(error);
        });
    });

    var chahgePeriodAndRegion = function() {
        angular.element('[name="period"]').change( function( ) {
            var periodRadioVal   = angular.element('[name="period"]:checked').val();
            // console.log(periodRadioVal);
            if( periodRadioVal == 'period' ) {
                $('#statisticreport-academic-periods-id').closest('.input').css({'display':'block'});
                $('#StatisticReport_start_date').closest('.input').css({'display':'none'});
                $('#StatisticReport_end_date').closest('.input').css({'display':'none'});
            } else if( periodRadioVal == 'interval' ) {
                $('#statisticreport-academic-periods-id').closest('.input').css({'display':'none'});
                $('#StatisticReport_start_date').closest('.input').css({'display':'block'});
                $('#StatisticReport_end_date').closest('.input').css({'display':'block'});
            } else  {
                $('#statisticreport-academic-periods-id').closest('.input').css({'display':'none'});
                $('#StatisticReport_start_date').closest('.input').css({'display':'none'});
                $('#StatisticReport_end_date').closest('.input').css({'display':'none'});
            }
        });

        angular.element('[name="by_region"]').change( function( ) {
            var byRegion   = angular.element('[name="by_region"]:checked');
            // console.log(periodRadioVal);
            if( byRegion.length == 0  ) {
                $('#statisticreport-institution-types-id').closest('.input').css({'display':'none'});
                $('#statisticreport-region').closest('.input').css({'display':'none'});
                $('#statisticreport-districts').closest('.input').css({'display':'none'});
            } else  {
                $('#statisticreport-institution-types-id').closest('.input').css({'display':'block'});
                $('#statisticreport-region').closest('.input').css({'display':'block'});
                $('#statisticreport-districts').closest('.input').css({'display':'block'});
            }
        });

        angular.element('[name="by_region"]').trigger('change');
        angular.element('[name="period"]').trigger('change');
    }

    modal_closer.click( function(){
        modal_container.css( {'display':'none'} );
    });

}]);
