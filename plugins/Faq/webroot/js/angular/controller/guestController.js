var guestController = angular.module('OE_Core', ["bgDirectives","ui.bootstrap","kd-angular-multi-select","ui.bootstrap","ui.bootstrap-slider","ui.tab.scroll","agGrid","app.ctrl","advanced.search.ctrl","kd-elem-sizes","kd-angular-checkbox-radio","multi-select-tree","kd-angular-tree-dropdown","kd-angular-ag-grid","sg.tree.ctrl","sg.tree.svc"]).run(function() {
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
let questionsGuest = '';
let questionsUser = '';
guestController.controller('GuestController', ['$scope','$http', function($scope,$http) {
        $http.get('/Faq/apiOuter').then( function(response) {
            console.log(data)
            var data             = response.data;
            $scope._questions    = data['inner'];
            questionsGuest = data['inner'];
            questionsUser = data['outer'];
            /* Questions for users >>> questionsUser.forEach(element =>  questionsGuest.push(element))*/
        });

        $scope.changeLang = function() {
            $scope._questions = questionsGuest;
            var selectorDataLang = document.getElementById("chavo-lang").value;
            var arrLang = $scope._questions.filter(function (question) {
                return question['lang'] === selectorDataLang;
            })
            $scope._questions = arrLang;

        }
        $scope.data = {
           availableOptions: [
                {value: 'ru', name:'Русский'},
                {value: 'kg', name:'Кыргызча'},
                {value: 'en', name:'English'}
                ],
            selectedOption : {value:'ru', name: 'Русский'}
        }

        $scope.collapseQuestion = function($index) {
            for(var _index in $scope._questions) {
                if($index != _index) {
                    $scope._questions[_index]._hidden = true;
                }
            }
            $scope._questions[$index]._hidden = !$scope._questions[$index]._hidden;
        }
}]
);
