<?php
$url = '#';
if (!empty($homeUrl)) {
	$url = $this->Url->build($homeUrl);
}
?>
<!-- fix PIB: dark theme -->
<script>
    var matchTheme = /theme=(dark|light)/.exec(document.cookie);
    window.theme = matchTheme && matchTheme[1] || 'light';
    document.body.classList.add(window.theme);
</script>
<!-- fix PIB: dark theme end -->

<?php // $this->Html->css('custom'); ?>
<style>
    .logo-os {
        width: 30px;
        height: 30px;
    }
</style>
<header>
	<nav class="navbar navbar-fixed-top" >
		<div class="navbar-left">
			
			<a class="header-main-link" href="<?= $url ?>">
				<div class="brand-logo">
					<h1><?php echo __('EMIS of the Kyrgyz Republic') ?></h1>
				</div>

			</a>

		</div>

		<?php if (!isset($headerSideNav) || (isset($headerSideNav) && $headerSideNav)) : ?>
		<div class="navbar-right">
			<?php echo $this->element('OpenEmis.header_navigation') ?>
		</div>
		<?php endif ?>
		<div class="menu-handler">
			<button class="menu-toggle" type="button">
				<svg width="50" height="50" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg"> <g filter="url(#filter0_d)"> <path fill-rule="evenodd" clip-rule="evenodd" d="M6 14C6 12.8954 6.89543 12 8 12H42C43.1046 12 44 12.8954 44 14C44 15.1046 43.1046 16 42 16H8C6.89543 16 6 15.1046 6 14ZM8 22C6.89543 22 6 22.8954 6 24C6 25.1046 6.89543 26 8 26H42C43.1046 26 44 25.1046 44 24C44 22.8954 43.1046 22 42 22H8ZM8 33C6.89543 33 6 33.8954 6 35C6 36.1046 6.89543 37 8 37H42C43.1046 37 44 36.1046 44 35C44 33.8954 43.1046 33 42 33H8Z" fill="#293845"/> </g> <defs> <filter id="filter0_d" x="-3" y="-2" width="56" height="56" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"> <feFlood flood-opacity="0" result="BackgroundImageFix"/> <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/> <feOffset dy="1"/> <feGaussianBlur stdDeviation="1.5"/> <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/> <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/> <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow" result="shape"/> </filter> </defs> </svg>
			</button>
		</div>
	</nav>
</header>
<!--<script>-->
<!--    $('#advanced-options').hide();-->
<!---->
<!--    // toggle advanced options-->
<!--    $('#adv-search-btn').click(function() {-->
<!--        $('#advanced-options').slideToggle("fast");-->
<!--    });-->
<!---->
<!--    $('#adv-filter li').click(function () {-->
<!--        $('#adv-filter li.active').removeClass('active');-->
<!--        $(this).addClass('active');-->
<!---->
<!---->
<!--    });-->
<!---->
<!---->
<!--</script>-->
