var TemplateDetail = angular.module('OE_Core', ["app.ctrl"]).run(function() {
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

TemplateDetail.controller('TemplateDetail', ['$scope','$rootScope',function ($scope,$rootScope) {
    // var $CKEDITOR= CKEDITOR.replace('templatereport-content',{});
}]);