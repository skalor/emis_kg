<?php use Cake\Core\Configure; ?>
<?= $this->Html->script('app/components/alert/alert.svc.js?v='.Configure::read('scriptsVersion'), ['block' => true]); ?>
<?= $this->Html->script('angular/dashboard/dashboard.ctrl.js?v='.Configure::read('scriptsVersion'), ['block' => true]); ?>
<?= $this->Html->script('angular/dashboard/dashboard.svc.js?v='.Configure::read('scriptsVersion'), ['block' => true]); ?>

<?php
$this->extend('OpenEmis./Layout/Container');
$this->assign('contentHeader', (!empty($contentHeader) ? $contentHeader : $this->Label->get("$model._content_header")));

$this->start('contentBody');
$panelHeader = $this->fetch('panelHeader');
?>

<?= $this->element('OpenEmis.alert') ?>

<div class="panel">
	<div class="panel-body" style="position: relative;">
		<?= $this->element('nav_tabs') ?>
		<bg-splitter orientation="horizontal" class="content-splitter" collapse="{{DashboardController.collapse}}" elements="getSplitterElements" float-btn="false">
		<bg-pane class="main-content">
			<?= $this->element('Dashboard/notices'); ?>

			<?= $this->element('Dashboard/workbench'); ?>
		</bg-pane>

		<!-- With Buttons -->
		<bg-pane class="split-content splitter-slide-out split-with-btn" min-size-p="20" max-size-p="80" size-p="70">
			<div class="split-content-header" ng-cloak>
				<h3>{{DashboardController.workbenchTitle}}</h3>
				<div class="split-content-btn">
					<button href="#" class="btn btn-outline" ng-click="DashboardController.removeSplitContentResponsive()">
						<i class="fa fa-close fa-lg"></i>
					</button>
				</div>
			</div>
			<div class="split-content-area">
				<div class="html-box">
					<div id="dashboard-workbench-table" class="table-wrapper">
						<div ng-if="DashboardController.gridOptions['workbench']" kd-ag-grid="DashboardController.gridOptions['workbench']" class="ag-height-fixed"></div>
					</div>
				</div>
			</div>
		</bg-pane>
	</bg-splitter>
	</div>
</div>
<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym(60697777, "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true
   });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/60697777" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->

<!-- Google Analytics -->
<?php $this->end() ?>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-160191256-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-160191256-1');
</script>
<!-- Google Analytics -->

