
<div class="row dashboard-container">
    <div id="workbench">
        <div class="dashboard-content margin-top-10">
            <h3  class="workbenchHeader"><?= __('Workbench'); ?></h3>
            <table class="table table-lined" ng-show="(DashboardController.workbenchItems && DashboardController.workbenchItems.length == 0) || (!DashboardController.workbenchItems)">
                <tbody class="table_body">
                <tr ng-if="!DashboardController.workbenchItems" ng-cloak><td><?= __('No Workbench Data'); ?></td></tr>
                <tr ng-if="DashboardController.workbenchItems && DashboardController.workbenchItems.length == 0"><td><?= __('Loading'); ?> ...</td></tr>
                </tbody>
            </table>
            <div ng-if="DashboardController.workbenchItems && DashboardController.workbenchItems.length > 0" ng-cloak>
                <ul class="list-group">
                    <li class="list-group-item" ng-show="item.total > 0" ng-repeat="item in DashboardController.workbenchItems | orderBy:'order'" ng-click="DashboardController.onChangeModel(item)">
                        <div class="list-icon">
						<span>
							<svg width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path fill-rule="evenodd" clip-rule="evenodd" d="M16 20C17.1 20 18 19.1 18 18V4C18 2.9 17.1 2 16 2H15V0H13V2H5V0H3V2H2C0.89 2 0.01 2.9 0.01 4L0 18C0 19.1 0.89 20 2 20H16ZM6 11V9H4V11H6ZM2 6H16V4H2V6ZM16 8V18H2V8H16ZM14 11V9H12V11H14ZM10 11H8V9H10V11Z" fill="#293845"/>
							</svg>
						</span>


                            <div class="badge btn-red badge-right">{{item.total}}</div>
                        </div>
                        <div class="list-text">
                            <p>{{item.name}}</p>
                        </div>
                        <i class="chervon"></i>
                        <hr class="workbenchHR">
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
