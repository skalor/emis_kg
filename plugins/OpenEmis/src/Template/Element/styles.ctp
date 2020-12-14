<?php
echo $this->Html->css('OpenEmis.reset');

use Cake\Core\Configure;
//Jquery Library
echo $this->Html->css('OpenEmis.lib/jquery/jquery-ui.min');

//Add in only Wizard and remove all component
echo $this->Html->css('OpenEmis.../plugins/font-awesome/css/font-awesome.min.css?v='.Configure::read('stylesVersion'));
echo $this->Html->css('OpenEmis.../plugins/fuelux/css/fuelux.css?v='.Configure::read('stylesVersion'));
echo $this->Html->css('OpenEmis.../plugins/bootstrap/css/bootstrap.min.css?v='.Configure::read('stylesVersion'));
echo $this->Html->css('OpenEmis.../plugins/scrolltabs/css/scrolltabs.css?v='.Configure::read('stylesVersion'));
echo $this->Html->css('OpenEmis.../plugins/slider/css/bootstrap-slider.css?v='.Configure::read('stylesVersion'));
echo $this->Html->css('OpenEmis.../plugins/ng-scrolltabs/css/angular-ui-tab-scroll.css?v='.Configure::read('stylesVersion'));
echo $this->Html->css('OpenEmis.../plugins/toggle-switch/toggle-switch.css?v='.Configure::read('stylesVersion'));
echo $this->Html->css('style.css?v='.Configure::read('stylesVersion'));
// echo $this->Html->css('OpenEmis.../plugins/ng-agGrid/css/ag-grid');
// echo $this->Html->css('OpenEmis.../plugins/ng-agGrid/css/theme-fresh');
echo $this->Resource->css('OpenEmis.master.min.css?v='.Configure::read('stylesVersion'));
echo $this->Html->css('custom.css?v='.Configure::read('stylesVersion'));

if (isset($theme)) {
	echo $this->Resource->css($theme);
}
?>
<link rel="stylesheet" href="<?= $this->Url->css('themes/layout.min') ?>?timestamp=<?=$lastModified?>" >
<?php
	echo $this->Html->css('owl.carousel.min');
	echo $this->Html->css('owl.theme.default.min');
	echo $this->Html->css('slick');
	echo $this->Html->css('slick-theme');
	echo $this->Html->css('customCSS.css?v='.Configure::read('stylesVersion'));

    $iframeCSS = $this->Html->css('customIframeEditCSS.css?v='.Configure::read('stylesVersion'));
    echo <<<EOD
    <script>
        if( window.self !== window.top ) {
            let headTag = document.getElementsByTagName('head')[0].innerHTML;
            headTag += '{$iframeCSS}';
            document.getElementsByTagName('head')[0].innerHTML = headTag;
            document.addEventListener('DOMContentLoaded', function(){
                Array.prototype.forEach.call(document.querySelectorAll("a:not(.btn)"), function(e){
                    e.setAttribute('target', '_blank');
                });
            });
        }
    </script>
EOD;


?>
<!--[if gte IE 9]>
<?php
	echo $this->Resource->css('OpenEmis.ie/ie9-fixes');
?>
<![endif]-->
