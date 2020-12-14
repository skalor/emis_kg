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
    var generete_report = angular.element('[name="generate-report"]');
    var modal_container = angular.element('.modal_container');
    var modal_closer    = angular.element('.modal_closer');
    var contenainer     = angular.element('.body-header');
    var buttonCancel    = angular.element('.cancelModal');
    var buttonGenerate  = angular.element('.generateReport');

    generete_report.click(function(event){
        var current_target = angular.element(event);
        var template_id = angular.element('#generated-report').val();
        $http.get('/core-old/TemplateReport/quickCreate/'+template_id).then( function( response ) {
            var content = response.data;
            contenainer.empty();
            contenainer.prepend(content);
            modal_container.css({'display':'block'});
        });
    });

    buttonCancel.click( function() {
        modal_container.css( {'display':'none'} );
    });

    buttonGenerate.click(function() {
        var form = angular.element('#modal-body').find('form').serializeArray();
        $http.post( `/core-old/TemplateReport/createRecord`, form ).then(function(response) {
            console.log(response);
        },function(error){
            console.log(error);
        });
    });

    modal_container.click( function(){
        modal_container.css( {'display':'none'} );
    });

}]);