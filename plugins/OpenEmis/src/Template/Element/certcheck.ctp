<?php
use Cake\Core\Configure;
$description = __d('open_emis', $_productName);
$icon = strpos($_productName, 'School') !== false ? '_school' : '';
?>

<!DOCTYPE html>
<html lang="<?= $htmlLang; ?>" dir="<?= $htmlLangDir; ?>" class="<?= $htmlLangDir == 'rtl' ? 'rtl' : '' ?>">
<head>
	<?= $this->Html->charset(); ?>
	<title><?= $description ?></title>
	<?php
		echo $this->Html->meta(['name' => 'viewport', 'content' => 'width=320, initial-scale=1']);
		echo $this->Html->meta('favicon', 'favicon.ico', ['type' => 'icon']);
		echo $this->fetch('meta');

		echo $this->Html->css('OpenEmis.../plugins/bootstrap/css/bootstrap.min', ['media' => 'screen']);
		echo $this->Html->css('OpenEmis.../plugins/font-awesome/css/font-awesome.min', ['media' => 'screen']);
		echo $this->Html->css('OpenEmis.reset', ['media' => 'screen']);

		$debug = Cake\Core\Configure::read('debug');
		if ($debug) { //This is to the dev testing purpose.
			echo $this->Html->css('OpenEmis.master');
		} else {
			echo $this->Html->css('OpenEmis.master.min');
		}

		if (isset($theme)) {
			echo $this->Html->css($theme);
		}


        echo $this->Html->css('OpenEmis.custom', ['media' => 'screen']);

        

        echo $this->Html->css('OpenEmis.style', ['media' => 'screen']);

		echo $this->Html->script('OpenEmis.lib/css_browser_selector');
		echo $this->Html->script('OpenEmis.lib/jquery/jquery.min');
		echo $this->Html->script('OpenEmis.../plugins/bootstrap/js/bootstrap.min');
    ?>

	<link rel="stylesheet" href="<?= $this->Url->css('themes/layout.min') ?>?timestamp=<?=$lastModified?>" >
    <?php
        echo $this->Html->css('OpenEmis.custom', ['media' => 'screen']);
        echo $this->Html->css('customCSS', ['media' => 'screen']);
    ?>
	<!--[if gte IE 9]>
	<?php
		echo $this->Html->css('OpenEmis.ie/ie9-fixes');
	?>
	<![endif]-->
    <style>
        #qr-canvas {
            display: none;
        }
        #outdiv {
            width: 320px;
            height: 240px;
            border: solid;
            border-width: 3px 3px 3px 3px;
            margin: auto;
            overflow: hidden;
        }
        #v {
            width: 320px;
            height: 240px;
        }
        #result {
            border: solid;
            border-width: 1px 1px 1px 1px;
            padding: 20px;
            width: 320px;
            margin: 10px auto;
            word-break: break-word;
            white-space: pre-line;
        }
    </style>
</head>
<?php echo $this->element('OpenEmis.analytics') ?>

<body onload="$('input[type=text]:first').focus()" class="login">
    <?= $this->element('OpenEmis.navbar') ?>
    <a href="/" class="only_mobile faqLogoForMobile" >
        <?php echo $this->Html->image('logo_small.svg', ['alt' => '', 'class' => 'custom_inf_logo_for_mobile']); ?>
    </a>
    <div class="body-wrapper">
        <?php if (!empty($qr) && !empty($UserAttachment) && count($UserAttachment)): ?>
        <div class="col-sm-12" style="display: flex;align-items: center;justify-content: center">
            <div class="login-box reset-box QR-box" style="display:flex;align-items:center">
                <div class="col-sm-8 p-0 m-0">
                    <div class="col-sm-12 text-description p-0 m-0">
                        <strong><?= __('Certificate')?> № <?=$UserAttachment['file_number']?>:</strong> Выдана <?=$UserAttachment['SecurityUsers']['last_name'].' '.$UserAttachment['SecurityUsers']['first_name'].' '.$UserAttachment['SecurityUsers']['middle_name']?>
                    </div>
                    <br>
                    <div class="col-sm-12 text-description p-0 m-0">
                        <strong><?= __('Date of issue of the certificate')?> :</strong> <?=$UserAttachment['date_on_file']->format('d/m/Y')?>
                        <strong><?= __('Date of issue of the certificate')?> :</strong> <?=$UserAttachment['date_on_file']->format('d/m/Y')?>
                    </div>
                    <?php
                    /*<br>
                    <div class="col-sm-12 text-description p-0 m-0">
                        <strong>Образовательная организация:</strong> <?=$UserAttachment['Institutions']['name']?>
                    </div>*/
                    ?>
                </div>
                <div class="col-sm-4 p-0 m-0">
                    <a href="<?=\Cake\Routing\Router::url("/CertCheck/$qr/view")?>" target="_blank">
                    <button class="btn btn-primary btn-login download-button spravkaBtn">
                        <?= __('View the certificate')?>
                        <?= __('View the certificate')?>
                    </button>
                    </a>
                </div>
            </div>
        </div>
        <?php elseif (!empty($qr) && (empty($UserAttachment) || count($UserAttachment) == 0)): ?>
        <div class="col-sm-12" style="display: flex;align-items: center;justify-content: center">
            <div class="login-box reset-box QR-box">
                <div class="col-sm-12 text-description p-0 m-0">
                <strong> <?= __('There is no such  certificate')?></strong>
                <strong> <?= __('There is no such  certificate')?></strong>
                </div>
                <div class="col-sm-12 text-description p-0 m-0">
                <strong> <?= __('Check if the help number is dialed correctly and try again.')?> </strong>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <div class="col-sm-12" style="display: flex;align-items: center;justify-content: center">
            <div class="login-box reset-box QR-box">
                <div class="title">
                <span class="title-wrapper">
                    <?= __('Verification of certificates - QR')?>
                </span>
                </div>

                <div class="row reset-flex">
                    <form action="<?=\Cake\Routing\Router::url('/CertCheck/')?>" onsubmit="window.location.href = (this.action + this.spravkaInput.value.toUpperCase()); return false;" method="get">
                        <div class="col-sm-12 col-md-4 form-description qr-hint">
                            <div class="text-gray">
                                <?= __('To check the certificate, you need to enter the certificate number.')?>
                                <?= __('To check the certificate, you need to enter the certificate number.')?>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-4" style="padding: 0px">
                            <div class="input text"><input type="text" class="spravkaInput" name="spravkaInput"
                                                           minlength="5" maxlength="5" pattern="^[A-Za-z\d]{5}$" required="required"
                                                           placeholder="<?= __('Certificate number')?>"
                                                           oninvalid="this.setCustomValidity(`<?= __('The number must consist of 5 Latin letters and numbers')?>`)"
                                                           oninput="this.setCustomValidity('')"
                                                           value="<?=isset($qr) && strlen($qr) == 5 ? strtoupper($qr) : ''?>"
                                                           autocomplete="off"></div>
                        </div>
                        <div class="col-sm-12 col-md-4 form-description">
                            <button type="submit" class="btn btn-primary btn-login spravkaBtn" style="margin: 0">
                                <?= __('Search')?>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="row reset-flex">
                    <div class="col-sm-12 col-md-4 form-description qr-hint">
                        <div class="text-gray">
                            <?= __('Use your smartphone to scan the QR code.')?>

                        </div>
                    </div>
                    <div class="col-sm-12 col-md-4 QRIcon" style="padding: 0px;text-align: left;">
                        <a href="javascript:setwebcam()" id="webcamimg">
                        <svg width="69" height="69" viewBox="0 0 69 69" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M32.3438 19.4062H19.4063C17.9766 19.4063 16.6055 19.9742 15.5945 20.9851C14.5836 21.9961 14.0156 23.3672 14.0156 24.7969V37.8206C12.221 38.0797 10.5796 38.9762 9.39175 40.3462C8.2039 41.7161 7.549 43.468 7.54688 45.2812V56.8927C7.54624 57.8632 7.66574 58.8301 7.90266 59.7712L8.625 62.6606V66.8438H10.7813V62.5312C10.7811 62.444 10.7703 62.3571 10.7489 62.2725L9.99422 59.243C9.80069 58.4745 9.70291 57.6851 9.70313 56.8927V45.2812C9.7047 44.039 10.1345 42.8353 10.92 41.873C11.7056 40.9107 12.7989 40.2487 14.0156 39.9984V48.5156C14.0157 48.7091 14.0678 48.8991 14.1666 49.0655L16.7864 53.4319C17.0671 53.906 17.466 54.2993 17.9441 54.5733C18.4221 54.8473 18.9631 54.9926 19.5141 54.9952C20.2283 55.0013 20.9244 54.7709 21.4939 54.3399C22.0634 53.9089 22.4742 53.3015 22.6622 52.6125L23.9021 47.9766C23.9768 47.6995 24.1584 47.4633 24.407 47.3199C24.6555 47.1764 24.9508 47.1373 25.2281 47.2111C25.3639 47.2458 25.4911 47.3077 25.6023 47.3929C25.7135 47.4782 25.8062 47.585 25.875 47.707C25.9475 47.8292 25.9944 47.9648 26.0129 48.1057C26.0315 48.2465 26.0212 48.3896 25.9828 48.5264L23.9775 56.0409C23.8055 56.6771 23.7185 57.3333 23.7188 57.9923V60.0192L20.7 64.0406C20.5589 64.2267 20.4831 64.454 20.4844 64.6875V66.8438H22.6406V65.0433L25.6594 61.0219C25.8005 60.8358 25.8763 60.6085 25.875 60.375V57.9923C25.8762 57.5228 25.9378 57.0554 26.0583 56.6016L26.1985 56.0625H32.3438C33.7735 56.0625 35.1446 55.4946 36.1555 54.4836C37.1665 53.4727 37.7344 52.1016 37.7344 50.6719V24.7969C37.7344 23.3672 37.1665 21.9961 36.1555 20.9851C35.1446 19.9742 33.7735 19.4062 32.3438 19.4062ZM35.5782 50.6719C35.5756 51.5289 35.234 52.3501 34.628 52.9561C34.022 53.5621 33.2008 53.9037 32.3438 53.9062H26.7807L27.3521 51.75H29.1094V49.5938H27.9342L28.0744 49.087C28.2936 48.257 28.1774 47.374 27.751 46.6289C27.4288 46.0767 26.9506 45.6321 26.3765 45.3509C25.8023 45.0698 25.1579 44.9647 24.5242 45.0488C23.8904 45.1329 23.2957 45.4025 22.8148 45.8237C22.3339 46.2449 21.9882 46.7988 21.8213 47.4159L20.5814 52.0519C20.5271 52.2536 20.4145 52.4349 20.2579 52.5731C20.1012 52.7113 19.9073 52.8003 19.7003 52.8291C19.4934 52.8578 19.2826 52.825 19.0942 52.7347C18.9057 52.6444 18.7481 52.5007 18.6408 52.3214L16.1719 48.2138V24.7969C16.1744 23.9399 16.516 23.1187 17.122 22.5127C17.7281 21.9066 18.5492 21.5651 19.4063 21.5625H32.3438C33.2008 21.5651 34.022 21.9066 34.628 22.5127C35.234 23.1187 35.5756 23.9399 35.5782 24.7969V50.6719Z" fill="#2D7ED6"/>
                            <path d="M22.6406 23.7188H29.1094V25.875H22.6406V23.7188Z" fill="#2D7ED6"/>
                            <path d="M18.3281 23.7188H20.4844V25.875H18.3281V23.7188Z" fill="#2D7ED6"/>
                            <path d="M21.5625 29.1094H19.4062C18.8108 29.1094 18.3281 29.5921 18.3281 30.1875V32.3438C18.3281 32.9392 18.8108 33.4219 19.4062 33.4219H21.5625C22.1579 33.4219 22.6406 32.9392 22.6406 32.3438V30.1875C22.6406 29.5921 22.1579 29.1094 21.5625 29.1094Z" fill="#2D7ED6"/>
                            <path d="M32.3438 29.1094H30.1875C29.5921 29.1094 29.1094 29.5921 29.1094 30.1875V32.3438C29.1094 32.9392 29.5921 33.4219 30.1875 33.4219H32.3438C32.9392 33.4219 33.4219 32.9392 33.4219 32.3438V30.1875C33.4219 29.5921 32.9392 29.1094 32.3438 29.1094Z" fill="#2D7ED6"/>
                            <path d="M21.5625 39.8906H19.4062C18.8108 39.8906 18.3281 40.3733 18.3281 40.9688V43.125C18.3281 43.7204 18.8108 44.2031 19.4062 44.2031H21.5625C22.1579 44.2031 22.6406 43.7204 22.6406 43.125V40.9688C22.6406 40.3733 22.1579 39.8906 21.5625 39.8906Z" fill="#2D7ED6"/>
                            <path d="M31.2656 42.0469H29.1094V44.2031H32.3438C32.6297 44.2031 32.9039 44.0895 33.1061 43.8874C33.3083 43.6852 33.4219 43.4109 33.4219 43.125V39.8906H31.2656V42.0469Z" fill="#2D7ED6"/>
                            <path d="M24.7969 29.1094H26.9531V33.4219H24.7969V29.1094Z" fill="#2D7ED6"/>
                            <path d="M29.1094 35.5781H33.4219V37.7344H29.1094V35.5781Z" fill="#2D7ED6"/>
                            <path d="M22.6406 35.5781H26.9531V37.7344H22.6406V35.5781Z" fill="#2D7ED6"/>
                            <path d="M24.7969 39.8906H26.9531V44.2031H24.7969V39.8906Z" fill="#2D7ED6"/>
                            <path d="M18.3281 35.5781H20.4844V37.7344H18.3281V35.5781Z" fill="#2D7ED6"/>
                            <path d="M58.2188 2.15625H10.7812C9.92423 2.15881 9.10304 2.5004 8.49703 3.1064C7.89102 3.71241 7.54943 4.5336 7.54688 5.39062V37.7344H9.70312V12.9375H59.2969V52.8281C59.2969 53.1141 59.1833 53.3883 58.9811 53.5905C58.7789 53.7927 58.5047 53.9062 58.2188 53.9062H38.8125V56.0625H58.2188C59.0758 56.0599 59.897 55.7184 60.503 55.1123C61.109 54.5063 61.4506 53.6851 61.4531 52.8281V5.39062C61.4506 4.5336 61.109 3.71241 60.503 3.1064C59.897 2.5004 59.0758 2.15881 58.2188 2.15625ZM59.2969 10.7812H9.70312V5.39062C9.70312 5.10469 9.81671 4.83046 10.0189 4.62828C10.2211 4.42609 10.4953 4.3125 10.7812 4.3125H58.2188C58.5047 4.3125 58.7789 4.42609 58.9811 4.62828C59.1833 4.83046 59.2969 5.10469 59.2969 5.39062V10.7812Z" fill="#2D7ED6"/>
                            <path d="M43.125 18.3281H40.9688C40.3733 18.3281 39.8906 18.8108 39.8906 19.4062V21.5625C39.8906 22.1579 40.3733 22.6406 40.9688 22.6406H43.125C43.7204 22.6406 44.2031 22.1579 44.2031 21.5625V19.4062C44.2031 18.8108 43.7204 18.3281 43.125 18.3281Z" fill="#2D7ED6"/>
                            <path d="M53.9062 18.3281H51.75C51.1546 18.3281 50.6719 18.8108 50.6719 19.4062V21.5625C50.6719 22.1579 51.1546 22.6406 51.75 22.6406H53.9062C54.5017 22.6406 54.9844 22.1579 54.9844 21.5625V19.4062C54.9844 18.8108 54.5017 18.3281 53.9062 18.3281Z" fill="#2D7ED6"/>
                            <path d="M43.125 29.1094H40.9688C40.3733 29.1094 39.8906 29.5921 39.8906 30.1875V32.3438C39.8906 32.9392 40.3733 33.4219 40.9688 33.4219H43.125C43.7204 33.4219 44.2031 32.9392 44.2031 32.3438V30.1875C44.2031 29.5921 43.7204 29.1094 43.125 29.1094Z" fill="#2D7ED6"/>
                            <path d="M50.6719 33.4219H53.9062C54.1922 33.4219 54.4664 33.3083 54.6686 33.1061C54.8708 32.9039 54.9844 32.6297 54.9844 32.3438V29.1094H52.8281V31.2656H50.6719V33.4219Z" fill="#2D7ED6"/>
                            <path d="M46.3594 18.3281H48.5156V22.6406H46.3594V18.3281Z" fill="#2D7ED6"/>
                            <path d="M50.6719 24.7969H54.9844V26.9531H50.6719V24.7969Z" fill="#2D7ED6"/>
                            <path d="M44.2031 24.7969H48.5156V26.9531H44.2031V24.7969Z" fill="#2D7ED6"/>
                            <path d="M46.3594 29.1094H48.5156V33.4219H46.3594V29.1094Z" fill="#2D7ED6"/>
                            <path d="M39.8906 24.7969H42.0469V26.9531H39.8906V24.7969Z" fill="#2D7ED6"/>
                            <path d="M44.2031 40.9688H54.9844V43.125H44.2031V40.9688Z" fill="#2D7ED6"/>
                            <path d="M44.2031 45.2812H54.9844V47.4375H44.2031V45.2812Z" fill="#2D7ED6"/>
                        </svg>
                        </a>
                    </div>
                </div>
                <div class="row reset-flex" id="qrScanVideo" style="display: none;">
                    <div class="col-sm-12 col-md-12" id="mainbody">
                        <div id="outdiv"><video id="v" autoplay=""></video></div>
                        <div id="result"></div>
                    </div>
                </div>
            </div>
        </div>
        <?= $this->Html->script('webqr/llqrcode.js?v='.Configure::read('scriptsVersion')); ?>
        <?= $this->Html->script('webqr/webqr.js?v=1.'.Configure::read('scriptsVersion')); ?>
        <canvas id="qr-canvas" width="800" height="600" style="width: 800px; height: 600px;"></canvas>
        <script type="text/javascript">load();</script>
        <?= $this->element('OpenEmis.footer') ?>
    </div>


</body>
</html>
