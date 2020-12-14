var faqController = angular.module('OE_Core', ["bgDirectives","ui.bootstrap","kd-angular-multi-select","ui.bootstrap","ui.bootstrap-slider","ui.tab.scroll","agGrid","app.ctrl","advanced.search.ctrl","kd-elem-sizes","kd-angular-checkbox-radio","multi-select-tree","kd-angular-tree-dropdown","kd-angular-ag-grid","sg.tree.ctrl","sg.tree.svc"]).run(function() {
    agGrid.LicenseManager.setLicenseKey("Community_Solutions_Foundation_CSF_Devs_13_October_2018__MTUzOTM4NTIwMDAwMA==500b28c724d110b0af8aa885bf13c66a");
    angular.baseUrl = '';
    angular.url = function (url) {
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

function YouTubeGetID(url){
    url = url.split(/(vi\/|v=|\/v\/|youtu\.be\/|\/embed\/)/);
    return (url[2] !== undefined) ? url[2].split(/[^0-9a-z_\-]/i)[0] : url[0];
}
faqController.filter('urlEncode', [function() {
    return window.encodeURIComponent;
}]).filter('cutSTR', [function(_url) {
    return _url.replace('/Faq/','');
}]).controller('FaqController', ['$scope','$http', function($scope,$http) {
    $scope.video            = {};
    $scope.docs             = {};
    $scope.questions        = {};
    $scope._lang = angular.element('html').attr('lang');

    $scope.video.hidden     = true;
    $scope.docs.hidden      = true;
    $scope.questions.hidden = true;

    $http.get('/Faq/api').then( function(response) {
        var data             = response.data;
        console.log("my",data)

        $scope._questions    = data['questions'];
        $scope._docs         = data['files'];
        console.log("files", $scope._docs )
        if (data['videos']){
            data['videos'].forEach(video => {
                video['videoID'] = (YouTubeGetID(video['location_url']))
                video['videoEmbed'] = "https://www.youtube.com/embed/"+video['videoID']

            });
        }
        $scope._videos       = data['videos'];



        // console.log(data['education'][0]['logo_content']);
        setTimeout(function( ){
            setDataVideo();
        },100);
    });

    var setDataVideo = function() {
        angular.element('.video-data').each( function(index,elem) {
            angElem = angular.element(elem);
            angular.element(elem).attr({'src': angElem.data('url')});
        });
    }
    var setDataVideos = function() {
        angular.element('.file-data').each( function(index,elem) {
            angElem = angular.element(elem);
            angular.element(elem).attr({'src': angElem.data('url')});
        });
    }

    var buttonPdfRead = angular.element('.pdf-read');
    buttonPdfRead.click( function() {
        console.log("hello")
        var record = angular.element('.pdf-read').data('id');

    });
    $scope.readPdf = function(index) {
        console.log(index)
        let base64 = $scope._docs[index].base64;
        // let pdfWindow = window.open("")
        // pdfWindow.document.write("<iframe width='100%' height='100%' src='data:application/pdf;base64, " + base64 + "'></iframe>")
        var byteCharacters = atob(base64);
        var byteNumbers = new Array(byteCharacters.length);
        for (var i = 0; i < byteCharacters.length; i++) {
            byteNumbers[i] = byteCharacters.charCodeAt(i);
        }
        var byteArray = new Uint8Array(byteNumbers);
        var file = new Blob([byteArray], { type: 'application/pdf' + ';base64' });
        var fileURL = URL.createObjectURL(file);
        window.open(fileURL);
    }

    $scope.getView = function(tab) {

        if(!$scope[tab].hidden) {
            $scope[tab].hidden = true;
            return;
        }

        $scope.video.hidden     = true;
        $scope.docs.hidden      = true;
        $scope.questions.hidden = true;

        angular.element('.active').removeClass('active');

        $scope[tab].hidden = !$scope[tab].hidden;


        if( !angular.element('.'+tab).hasClass('active') ) {
            angular.element('.'+tab).addClass('active');
        } else {
            angular.element('.active').removeClass('active');
        }
    }
    $scope.btn_status = true;

    $scope.collapseQuestion = function($type,$index) {
        for(var customIndex in $scope._questions) {

            if (customIndex != $type){
                for(var _index in $scope._questions[customIndex]) {
                    if($index != _index) {

                        $scope._questions[customIndex][_index]._hidden = true;
                    }
                }
            }

        }

        $scope._questions[$type][$index]._hidden = !$scope._questions[$type][$index]._hidden;
        this.btn_status = !this.btn_status;
    }

}]);
