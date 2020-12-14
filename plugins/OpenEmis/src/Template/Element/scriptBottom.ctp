<?php
use Cake\Core\Configure;
    echo $this->Html->script('OpenEmis.angular/kd-angular-splitter.js?v='.Configure::read('scriptsVersion'));
    echo $this->Html->script('OpenEmis.angular/ngx-adaptor/inline.bundle.js?v='.Configure::read('scriptsVersion'));
    echo $this->Html->script('OpenEmis.angular/ngx-adaptor/polyfills.bundle.js?v='.Configure::read('scriptsVersion'));
    echo $this->Html->script('OpenEmis.angular/ngx-adaptor/vendor.bundle.js?v='.Configure::read('scriptsVersion'));
    echo $this->Html->script('OpenEmis.angular/ngx-adaptor/main.bundle.js?v='.Configure::read('scriptsVersion'));
    echo $this->Html->css('OpenEmis.../js/angular/ngx-adaptor/styles.bundle.css?v='.Configure::read('scriptsVersion'));
?>

<script type="text/javascript">
$(document).ready(function() {
	Chosen.init();
	Checkable.init();
	MobileMenu.init();
	TableResponsive.init();
	Tooltip.init();
	ScrollTabs.init();
	Header.init();
	// ImageUploader.init();
	// Gallery.init();

    $(".left-pane").hover(function() {
        $(".left-pane").addClass("sidebar-hover");
    },function() {
        $(".left-pane").removeClass("sidebar-hover");
    })

    $('.nav-level-2.collapse.in').siblings('.accordion-toggle')
        .addClass('nav-active');



    $( ".legend-title-container" ).click(function() {
        alert( "Clicked" );
    });


});
    // fix PIB: dark theme
    let toggleSwitches = [];
    document.querySelectorAll('.toggle-button').forEach(function (btn) {
        toggleSwitches.push(btn);
    });
    // iframe theme
    document.querySelectorAll('iframe').forEach(function (iframe) {
        iframe.contentDocument.querySelectorAll('.toggle-button').forEach(function (btn) {
            toggleSwitches.push(btn);
        })
    });

    function switchTheme(e) {
        $.cookie('theme', null, { path: '/' });
        window.theme = e.target.checked ? 'light' : 'dark';
        $('body').removeClass('dark').removeClass('light').addClass(window.theme);
        $.cookie('theme', window.theme, { path: '/' });
        for (var i = 0; i < toggleSwitches.length; i++) {
            toggleSwitches[i].checked = e.target.checked;
        }
        // iframe theme
        document.querySelectorAll('iframe').forEach(function (iframe) {
            iframe.contentWindow.theme = e.target.checked ? 'light' : 'dark';
            $(iframe.contentDocument.body).removeClass('dark').removeClass('light').addClass(window.theme);
        });
    }
    if (window.theme === 'dark') {
        for (var i = 0; i < toggleSwitches.length; i++) {
            toggleSwitches[i].checked = false;
        }
    }
    for (var i = 0; i < toggleSwitches.length; i++) {
        toggleSwitches[i].addEventListener('change', switchTheme, false);
    }
    // fix PIB: dark theme end


    $( ".profile-image" )
        .parent('.form-input')
        .css( "background", "transparent" );
    $( ".table-wrapper" )
        .parents('.form-input')
        .css( {"background": "transparent","padding-left": "0px !important"} );
    


    $('.product-wrapper').on({
    "click":function(e) {
        e.stopPropagation();
        }
    });
    $('.dropdown-close').click(function(e){
        $('.btn-group.open').removeClass('open')
    })


</script>

<style type="text/css">
.error .chosen-choices {
    border-color: #CC5C5C !important;
}

/* POCOR-4359: temp added overwrites css styling for autocomplete as styles.bundle.css has .ui-autocomplete class that will cause the current autocomplete in core to not show - Added by KK*/
body .ui-autocomplete {
	position: absolute;
}
</style>
