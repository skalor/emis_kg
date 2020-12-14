
<div class="row dashboard-container">
	<div id="news">
		<div class="dashboard-content margin-top-10">
			<div>
                <h3 class="newsHeader"><?= __('Notices'); ?></h3>
				<ul class="list-group" ng-init="DashboardController.notices = <?= htmlspecialchars(json_encode($notices), ENT_QUOTES, 'UTF-8') ?>;">


                    <li class="list-group-item"  ng-if="DashboardController.notices.length == 0" ng-cloak>
						<div class="notice-message">
							<p><?= __('No Notices'); ?></p>
						</div>
					</li>
                    <li class="list-group-item" ng-if="!DashboardController.notices">
                        <div class="notice-message">
                            <p><?= __('Loading'); ?> ...</p>
                        </div>
                    </li>
					<li class="list-group-item" ng-repeat="notice in DashboardController.notices | orderBy:'order'" ng-cloak>
						<div class="notice-message">
                            <div class="createdTime"> {{notice.created|formatDate:'DD-MM-YYYY' }} </div>

                            <p>{{notice.message|removeEmded }}
                                <br>
                            <hr>
                            </p>
                                                        <p ng-if="notice.message|getUrl">
                                                           <iframe width="200" height="200" src="{{trustedUrl(notice.message|getUrl)}}" frameborder="0" allowfullscreen ></iframe>
                                                        </p>
						</div>
						<!-- To add the following mapping when notice attachment is added -->
						<div class="notice-attachments" ng-show="notice.attachment">
							<p><?= __('Attachments') ?>:</p>
							<ul>
								<li> <!-- Link for Attachments Here --> </li>
							</ul>
						</div>
					</li>

				</ul>
			</div>
		</div>
	</div>
</div>