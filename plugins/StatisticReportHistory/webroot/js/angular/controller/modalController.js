var modalController = angular.module('OE_Core', ["app.ctrl"]).run(function() {
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

modalController.controller('modalController', ['$scope','$rootScope','$http',function ($scope,$rootScope,$http) {
    console.log('here')
    var chahgePeriod = function() {
        var periodRadioVal   = angular.element('[name="period"]:checked').val();
        var periodRadio      = angular.element('[name="period"]');

        angular.element('[name="period"]').change( function( ) {
            if( periodRadioVal == 'period' ) {
                $('#statisticreport-academic-periods-id').closest('.input').css({'display':'block'});
                $('#statisticreport-start-date').closest('.input').css({'display':'none'});
                $('#statisticreport-end-date').closest('.input').css({'display':'none'});
            } else if( periodRadioVal == 'interval' ) {
                $('#statisticreport-academic-periods-id').closest('.input').css({'display':'none'});
                $('#statisticreport-start-date').closest('.input').css({'display':'block'});
                $('#statisticreport-end-date').closest('.input').css({'display':'block'});
            } else  {

            }
        });
    }

    setTimeout(function() {
        chahgePeriod();
    },300)

}]);