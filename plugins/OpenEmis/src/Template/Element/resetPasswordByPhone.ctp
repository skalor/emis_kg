<?php
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

		echo $this->Html->script('OpenEmis.lib/css_browser_selector');
		echo $this->Html->script('OpenEmis.lib/jquery/jquery.min');
		echo $this->Html->script('OpenEmis.../plugins/bootstrap/js/bootstrap.min');
	?>

	<link rel="stylesheet" href="<?= $this->Url->css('themes/layout.min') ?>?timestamp=<?=$lastModified?>" >
    <?php
        echo $this->Html->css('customCSS', ['media' => 'screen']);
    ?>
	<!--[if gte IE 9]>
	<?php
		echo $this->Html->css('OpenEmis.ie/ie9-fixes');
	?>
	<![endif]-->
</head>
<?php echo $this->element('OpenEmis.analytics') ?>

<body onload="$('input[type=text]:first').focus()" class="login">
    <?= $this->element('OpenEmis.navbar') ?>

    <div class="body-wrapper">
        <a class="mon-box">
            <?php echo $this->Html->image('os-logo.png', ['alt' => 'Информационная система управления образованием', 'class' => 'custom_os_logo']); ?>
            <?=$htmlLang=='ru'?'<h1 class="mon-title">МИНИСТЕРСТВО ОБРАЗОВАНИЯ  И <br> НАУКИ КЫРГЫЗСКОЙ РЕСПУБЛИКИ</h1>':'<h1 class="mon-title">КЫРГЫЗ РЕСПУБЛИКАСЫНЫН БИЛИМ <br> БЕРYҮ ЖАНА ИЛИМ МИНИСТРЛИГИ</h1>'?>
        </a>
    </div>
    <a href="/" class="body-wrapper wrapper_for_mobile">
		<?php echo $this->Html->image('inf_logo.svg', ['alt' => '', 'class' => 'custom_inf_logo_for_mobile']); ?>
	</a>

    <!--  Ваш номер телефона +996556566818 starts   -->
    <div class="body-wrapper">

        <div class="login-box reset-box col-sm-12 col-md-6 ">
            <div class="title">
				<span class="title-wrapper">
					<?php echo $this->Html->image('warning_amber_24px.png', ['alt' => 'CakePHP', 'class' => 'os-logo']); ?>

				</span>
            </div>

            <?php
            echo $this->element('OpenEmis.alert');
            ?>

            <?php
            echo $this->Form->create('Users', [
                'url' => ['plugin' => 'User', 'controller' => 'Users', 'action' => 'postResetPasswordByPhone'],
                'class' => 'form-horizontal',
                'id' => 'formResetByPhone'
            ]);
            ?>
            <?php if ($this->request->query('confirm') != 'rms'): ?>
            <div class="form-description">
                <?= __('Your phone number')?> <strong> <?=$this->request->query['phone']?></strong>
            </div>
            <?php $this->Form->unlockField('phone'); ?>
            <?php $this->Form->unlockField('step'); ?>
                <input type="hidden" name="phone" id="phone" value="<?=$this->request->query['phone']?>">
                <input type="hidden" name="step" id="step" value=""> <!-- telegram | rms -->
                <div class="btn long-button reset-by" onclick="$('#step').val('telegram');$('.reset-by.btn-primary').removeClass('btn-primary');$(this).addClass('btn-primary');$('.btn-login').prop('disabled', false);$('#formResetByPhone').attr('target', '_blank');">
                    <div> <?= __('Recover password using Telegram')?></div>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 24C18.6274 24 24 18.6274 24 12C24 5.37258 18.6274 0 12 0C5.37258 0 0 5.37258 0 12C0 18.6274 5.37258 24 12 24Z" fill="#039BE5"/>
                        <path d="M5.49126 11.74L17.0613 7.27896C17.5983 7.08496 18.0673 7.40996 17.8933 8.22196L17.8943 8.22096L15.9243 17.502C15.7783 18.16 15.3873 18.32 14.8403 18.01L11.8403 15.799L10.3933 17.193C10.2333 17.353 10.0983 17.488 9.78826 17.488L10.0013 14.435L15.5613 9.41196C15.8033 9.19896 15.5073 9.07896 15.1883 9.29096L8.31726 13.617L5.35526 12.693C4.71226 12.489 4.69826 12.05 5.49126 11.74Z" fill="white"/>
                    </svg>

                </div>
                <div class="btn long-button reset-by" onclick="$('#step').val('rms');$('.reset-by.btn-primary').removeClass('btn-primary');$(this).addClass('btn-primary');$('.btn-login').prop('disabled', false);$('#formResetByPhone').removeAttr('target');">

                    <?= __('Recover password using audio call')?>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.93947 14.0619C7.64871 11.7712 7.13147 9.48043 7.01478 8.56264C6.98218 8.30887 7.06951 8.05437 7.25106 7.87409L9.10485 6.02112C9.37755 5.74859 9.42594 5.32387 9.22154 4.99698L6.26996 0.41381C6.04382 0.0518439 5.57952 -0.0787932 5.19782 0.112155L0.459474 2.34374C0.150809 2.49573 -0.0307442 2.82368 0.00430138 3.16595C0.252577 5.52457 1.28085 11.3226 6.97878 17.021C12.6767 22.7193 18.474 23.7472 20.8338 23.9955C21.1761 24.0305 21.504 23.849 21.656 23.5403L23.8876 18.8019C24.0778 18.4211 23.9481 17.958 23.5876 17.7315L19.0044 14.7807C18.6777 14.5761 18.253 14.6241 17.9803 14.8966L16.1273 16.7504C15.9471 16.9319 15.6926 17.0192 15.4388 16.9866C14.521 16.8699 12.2302 16.3527 9.93947 14.0619Z" fill="#688C90"/>
                    </svg>
                </div>
            <?php else: ?>
                <div class="form-description descriptionWithIcon">
                    <?= __('Your phone should receive an audio call')?>
                     <i class="fa fa-volume-control-phone" aria-hidden="true"></i>
                </div>
                <?php $this->Form->unlockField('phone'); ?>
                <?php $this->Form->unlockField('step'); ?>
                <?php $this->Form->unlockField('rmsToken'); ?>
                <?php $this->Form->unlockField('rmsPhone'); ?>
                <input type="hidden" name="phone" id="phone" value="<?=$this->request->query['phone']?>">
                <input type="hidden" name="rmsToken" id="rmsToken" value="<?=$this->request->query['rms_token']?>">
                <input type="hidden" name="step" id="step" value="rmsConfirm"> <!-- rmsConfirm -->
                <div class="row reset-flex">
                    <input type="text" name="rmsPhone" placeholder="<?= __('Enter the number') ?>" id="rmsPhone">
                </div>

                <div class="form-description">
                    <?= __('Please enter the phone number from which you will receive a call') ?>
                </div>
            <?php endif; ?>
            <div class="form-group bottom-buttons">
                <?= $this->Form->button(__('Next'), ['type' => 'submit', 'name' => 'submit', 'class' => 'btn btn-login btn-primary', 'onclick' => "if ($('#step').val() == 'telegram') { setTimeout(function(){window.location.href = '".$this->Url->build('/ResetPasswordByPhone')."?phone=".$this->request->query['phone']."&step=telegram';},500); }"]) ?>
                <a target="_self" class="btn btn-login btn-secondary" href="./"><?=__('Cancel')?></a>
            </div>
            <?= $this->Form->end() ?>
        </div>
        <div class="login-box col-sm-12 col-md-6 hide">
            <div class="title">
				<span class="title-wrapper">
					<?php if (!$productLogo) : ?>
                        <?php echo $this->Html->image('inf_logo.png', ['alt' => 'CakePHP', 'class' => 'inf_logo']); ?>
                    <?php else: ?>
                        <?= $this->Html->image($productLogo, [
                            'style' => 'max-height: 45px; vertical-align: top'
                        ]); ?>
                    <?php endif; ?>
				</span>
            </div>
            <?php
            echo $this->element('OpenEmis.alert');

            echo $this->Form->create('Users', [
                'url' => ['plugin' => 'User', 'controller' => 'Users', 'action' => 'postLogin'],
                'class' => 'form-horizontal'
            ]);

            if ($enableLocalLogin) {
                echo $this->Form->input('username', ['placeholder' => __('Username'), 'label' => false, 'value' => $username]);
                echo $this->Form->input('password', ['placeholder' => __('Password'), 'label' => false, 'value' => $password]);
            }
            ?>

            <?php
            if (isset($showLanguage) && $showLanguage) :
                ?>
                <div class="input-select-wrapper">
                    <?= $this->Form->input('System.language', [
                        'label' => false,
                        'options' => $languageOptions,
                        'value' => $htmlLang,
                        'onchange' => "$('#reload').click()"
                    ]);
                    ?>
                </div>
            <?php endif;?>
            <div class="form-group">
                <?php if ($enableLocalLogin) : ?>
                    <?= $this->Form->button(__('Login'), ['type' => 'submit', 'name' => 'submit', 'value' => 'login', 'class' => 'btn btn-primary btn-login']) ?>
                <?php endif; ?>
                <button class="hidden" value="reload" name="submit" type="submit" id="reload">reload</button>
                <?= $this->Form->end() ?>

                <div class="links-wrapper">
                    <a target="_self" href="./ForgotPassword"><?php echo __('Forgot password?') ?></a>
                </div>


                <?php
                if ($authentications) :
                    ?>

                    <?php if ($authentications && $enableLocalLogin) : ?>
                    <hr />
                    <?= '<center>'.__('OR').'</center>'?>
                    <hr />
                <?php endif;?>
                    <div class="input-select-wrapper sso-options">
                        <?php
                        echo $this->Form->input('idp', [
                            'options' => $authentications,
                            'label' => false,
                            'onchange' => 'window.document.location.href=this.options[this.selectedIndex].value;'
                        ]);
                        ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>

        <?= $this->element('OpenEmis.footer') ?>
    </div>

    <!--  Ваш номер телефона +996556566818 ends   -->


</body>
</html>
