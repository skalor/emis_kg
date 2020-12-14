<ul class="pagination pagination-sm" ng-if="paging.perPage < paging.count && !paging.hide">
    <li class="prev" ng-class="{disabled: !paging.prevPage}"><a href="#" ng-if="paging.prevPage" ng-click="InstitutionStudentController.onPaginateStudents(paging.page - 1, listType, entry, model)"></a><a ng-if="!paging.prevPage"></a></li>
    <li ng-if="paging.page - 3 > 0" ng-class="{active: 0}"><span class="ellipsis">...</span></li>
    <li ng-if="paging.page - 2 > 0" ng-class="{active: 0}"><a href="#" ng-click="InstitutionStudentController.onPaginateStudents(paging.page - 2, listType, entry, model)">{{paging.page - 2}}</a></li>
    <li ng-if="paging.page - 1 > 0" ng-class="{active: 0}"><a href="#" ng-click="InstitutionStudentController.onPaginateStudents(paging.page - 1, listType, entry, model)">{{paging.page - 1}}</a></li>
    <li ng-if="paging.page > 0" ng-class="{active: 1}"><a>{{paging.page}}</a></li>
    <li ng-if="paging.page + 1 <= paging.pageCount" ng-class="{active: 0}"><a href="#" ng-click="InstitutionStudentController.onPaginateStudents(paging.page + 1, listType, entry, model)">{{paging.page + 1}}</a></li>
    <li ng-if="paging.page + 2 <= paging.pageCount" ng-class="{active: 0}"><a href="#" ng-click="InstitutionStudentController.onPaginateStudents(paging.page + 2, listType, entry, model)">{{paging.page + 2}}</a></li>
    <li ng-if="paging.page + 3 <= paging.pageCount" ng-class="{active: 0}"><span class="ellipsis">...</span></li>
    <li class="next" ng-class="{disabled: !paging.nextPage}"><a href="#" ng-if="paging.nextPage" ng-click="InstitutionStudentController.onPaginateStudents(paging.page + 1, listType, entry, model)"></a><a ng-if="!paging.nextPage"></a></li>
</ul>