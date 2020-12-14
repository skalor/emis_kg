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
            <?php echo $this->Html->image('os-logo.svg', ['alt' => 'Информационная система управления образованием', 'class' => 'custom_os_logo']); ?>
            <?=$htmlLang=='ru'?'<h1 class="mon-title">МИНИСТЕРСТВО ОБРАЗОВАНИЯ  И <br> НАУКИ КЫРГЫЗСКОЙ РЕСПУБЛИКИ</h1>':'<h1 class="mon-title">КЫРГЫЗ РЕСПУБЛИКАСЫНЫН БИЛИМ <br> БЕРYҮ ЖАНА ИЛИМ МИНИСТРЛИГИ</h1>'?>
        </a>
    </div>
    <a href="/" class="body-wrapper wrapper_for_mobile">
		<?php echo $this->Html->image('inf_logo.svg', ['alt' => '', 'class' => 'custom_inf_logo_for_mobile']); ?>
	</a>

<!--  Пожалуйста, укажите Ваш телефон или e-mail starts   -->
	<div class="body-wrapper">

		<div class="login-box reset-box col-sm-12 col-md-6 ">
            <a href="/" aria-hidden="true" class="close">×</a>
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
				'url' => ['plugin' => 'User', 'controller' => 'Users', 'action' => 'postForgotPassword'],
				'class' => 'form-horizontal'
			]);
			?>

			<div class="form-description">

                <?= __('Please enter your phone or e-mail')?>

            </div>

            <div class="row reset-flex">
                <div class="col-sm-12 col-md-6 form-description" style="padding: 0px;padding-top:10px">

                    <?= __('Phone or Email')?>
                </div>
                <div class="col-sm-12 col-md-6" style="padding: 0px">
                    <?php
                        echo $this->Form->input('username', ['placeholder' => __('Phone or Email'), 'label' => false]);
                    ?>
                </div>
            </div>

            <div class="form-description">
                <?= __('Please enter your phone number or e-mail to check if it is in the system')?>
            </div>

			<div class="form-group bottom-buttons">
				<?= $this->Form->button(__('Next'), ['type' => 'submit', 'name' => 'submit', 'class' => 'btn btn-primary btn-login']) ?>
                <?= $this->Form->button(__('Cancel'), ['type' => 'submit', 'name' => 'cancel', 'value'=>'login', 'class' => 'btn btn-login btn-secondary']) ?>
			</div>

			<?= $this->Form->end() ?>
		</div>
        <div class="login-box col-sm-12 col-md-6 hide">
            <div class="title">
				<span class="title-wrapper">
					<?php if (!$productLogo) : ?>
                        <?php echo $this->Html->image('logo_small.svg', ['alt' => 'CakePHP', 'class' => 'logo_small']); ?>
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
    <!--  Пожалуйста, укажите Ваш телефон или e-mail ends   -->


    <?php goto finish; ?>

    <!--  Ваш номер телефона +996556566818 starts   -->
    <div class="body-wrapper">

        <div class="login-box reset-box col-sm-12 col-md-6 ">
            <a href="/" aria-hidden="true" class="close">×</a>
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
                'url' => ['plugin' => 'User', 'controller' => 'Users', 'action' => 'postForgotPassword'],
                'class' => 'form-horizontal'
            ]);
            ?>

            <div class="form-description">





                <?= __('Your phone number')?> <strong> +996556566818</strong>
            </div>

            <div class="btn long-button ">
                <?= __('Recover password using Telegram')?>
            </div>
            <div class="btn long-button ">
                <?= __('Recover password using audio call')?>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.93947 14.0619C7.64871 11.7712 7.13147 9.48043 7.01478 8.56264C6.98218 8.30887 7.06951 8.05437 7.25106 7.87409L9.10485 6.02112C9.37755 5.74859 9.42594 5.32387 9.22154 4.99698L6.26996 0.41381C6.04382 0.0518439 5.57952 -0.0787932 5.19782 0.112155L0.459474 2.34374C0.150809 2.49573 -0.0307442 2.82368 0.00430138 3.16595C0.252577 5.52457 1.28085 11.3226 6.97878 17.021C12.6767 22.7193 18.474 23.7472 20.8338 23.9955C21.1761 24.0305 21.504 23.849 21.656 23.5403L23.8876 18.8019C24.0778 18.4211 23.9481 17.958 23.5876 17.7315L19.0044 14.7807C18.6777 14.5761 18.253 14.6241 17.9803 14.8966L16.1273 16.7504C15.9471 16.9319 15.6926 17.0192 15.4388 16.9866C14.521 16.8699 12.2302 16.3527 9.93947 14.0619Z" fill="#688C90"/>
                </svg>

            </div>



            <div class="form-group bottom-buttons">
                <?= $this->Form->button(__('Cancel'), ['type' => 'submit', 'name' => 'submit', 'class' => 'btn btn-login btn-secondary']) ?>
                <?= $this->Form->button(__('Save'), ['type' => 'submit', 'name' => 'submit', 'class' => 'btn btn-login btn-success']) ?>

            </div>
            <?= $this->Form->end() ?>
        </div>
        <div class="login-box col-sm-12 col-md-6">
            <div class="title">
				<span class="title-wrapper">
					<?php if (!$productLogo) : ?>
                        <?php echo $this->Html->image('logo_small.svg', ['alt' => 'CakePHP', 'class' => 'logo_small']); ?>
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

    <!--  Измените Ваш пароль starts   -->
    <div class="body-wrapper">

        <div class="login-box reset-box col-sm-12 col-md-6 ">
            <a href="/" aria-hidden="true" class="close">×</a>
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
                'url' => ['plugin' => 'User', 'controller' => 'Users', 'action' => 'postForgotPassword'],
                'class' => 'form-horizontal'
            ]);
            ?>

            <div class="form-description">
                <?= __('Change your password')?>
            </div>

            <div class="row reset-flex">
                <div class="col-sm-12 col-md-6 form-description" style="padding: 0px;padding-top:10px">
                     <?= __('Enter your old password')?>
                </div>
                <div class="col-sm-12 col-md-6" style="padding: 0px">
                    <div class="input password">
                        <input type="password" name="password" placeholder="<?= __('Old Password')?>" id="password">
                    </div>
                </div>
            </div>
            <div class="row reset-flex">
                <div class="col-sm-12 col-md-6 form-description" style="padding: 0px;padding-top:10px">
                    <?= __('Create a new password')?>
                </div>
                <div class="col-sm-12 col-md-6" style="padding: 0px">
                    <div class="input password"><input type="password" name="password" placeholder="Новый пароль" id="password"></div></div>
            </div>
            <div class="row reset-flex">
                <div class="col-sm-12 col-md-6 form-description" style="padding: 0px;padding-top:10px">
                    <?= __('Confirm new password')?>

                </div>
                <div class="col-sm-12 col-md-6" style="padding: 0px">
                    <div class="input password"><input type="password" name="password" placeholder="<?= __('Confirm the password')?>" id="password"></div></div>
            </div>




            <div class="form-group bottom-buttons">
                <?= $this->Form->button(__('Cancel'), ['type' => 'submit', 'name' => 'submit', 'class' => 'btn btn-login btn-secondary']) ?>
                <?= $this->Form->button(__('Save'), ['type' => 'submit', 'name' => 'submit', 'class' => 'btn btn-login btn-success']) ?>

            </div>

            <?= $this->Form->end() ?>
        </div>
        <div class="login-box col-sm-12 col-md-6">
            <div class="title">
				<span class="title-wrapper">
					<?php if (!$productLogo) : ?>
                        <?php echo $this->Html->image('inf_logo.svg', ['alt' => 'CakePHP', 'class' => 'inf_logo']); ?>
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
    <!--  Измените Ваш пароль ends   -->



    <!--  Проверьте сообщения в Telegram starts   -->
    <div class="body-wrapper">

        <div class="login-box reset-box col-sm-12 col-md-6 ">
            <a href="/" aria-hidden="true" class="close">×</a>
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
                'url' => ['plugin' => 'User', 'controller' => 'Users', 'action' => 'postForgotPassword'],
                'class' => 'form-horizontal'
            ]);
            ?>

            <div class="form-description descriptionWithIcon">
                <?= __('Check messages on Telegram') ?> <i class="fa fa-telegram" aria-hidden="true"></i>
            </div>


            <div class="form-description bottom-description">
                <?= __('We have sent a link to recover your password to log in!') ?>
            </div>



            <div class="form-group bottom-buttons">
                <?= $this->Form->button(__('Cancel'), ['type' => 'submit', 'name' => 'submit', 'class' => 'btn btn-login btn-secondary']) ?>
                <?= $this->Form->button(__('Save'), ['type' => 'submit', 'name' => 'submit', 'class' => 'btn btn-login btn-success']) ?>

            </div>

            <?= $this->Form->end() ?>
        </div>
        <div class="login-box col-sm-12 col-md-6">
            <div class="title">
				<span class="title-wrapper">
					<?php if (!$productLogo) : ?>
                        <?php echo $this->Html->image('inf_logo.svg', ['alt' => 'CakePHP', 'class' => 'inf_logo']); ?>
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
    <!--  Проверьте сообщения в Telegram ends   -->



    <!--  Проверьте входящие сообщения на Вашей почте starts   -->
    <div class="body-wrapper">

        <div class="login-box reset-box col-sm-12 col-md-6 ">
            <a href="/" aria-hidden="true" class="close">×</a>
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
                'url' => ['plugin' => 'User', 'controller' => 'Users', 'action' => 'postForgotPassword'],
                'class' => 'form-horizontal'
            ]);
            ?>

            <div class="form-description descriptionWithIcon">
                <?= __('Check incoming messages on your email') ?>
                 <i class="fa fa-envelope-o" aria-hidden="true"></i>
            </div>


            <div class="form-description bottom-description">
                <?= __('We have sent a link to your email to recover your password!') ?>

            </div>



            <div class="form-group bottom-buttons">
                <?= $this->Form->button(__("Cancel"), ['type' => 'submit', 'name' => 'submit', 'class' => 'btn btn-login btn-secondary']) ?>
                <?= $this->Form->button(__('Save'), ['type' => 'submit', 'name' => 'submit', 'class' => 'btn btn-login btn-success']) ?>

            </div>

            <?= $this->Form->end() ?>
        </div>
        <div class="login-box col-sm-12 col-md-6">
            <div class="title">
				<span class="title-wrapper">
					<?php if (!$productLogo) : ?>
                        <?php echo $this->Html->image('inf_logo.svg', ['alt' => 'CakePHP', 'class' => 'inf_logo']); ?>
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
    <!--  Проверьте входящие сообщения на Вашей почте ends   -->



    <!--  На Ваш телефон должен поступить аудиозвонок starts   -->
    <div class="body-wrapper">

        <div class="login-box reset-box col-sm-12 col-md-6 ">
            <a href="/" aria-hidden="true" class="close">×</a>
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
                'url' => ['plugin' => 'User', 'controller' => 'Users', 'action' => 'postForgotPassword'],
                'class' => 'form-horizontal'
            ]);
            ?>

            <div class="form-description descriptionWithIcon">
                <?= __('Your phone should receive an audio call') ?>
                 <i class="fa fa-volume-control-phone" aria-hidden="true"></i>
            </div>

            <div class="row reset-flex">

                <input type="text" name="phone" placeholder="<?= __('Enter the number') ?>" id="phone">
            </div>

            <div class="form-description">
                <?= __('Please enter the phone number from which you will receive a call') ?>
            </div>

            <div class="form-group bottom-buttons">
                <?= $this->Form->button(__('Next'), ['type' => 'submit', 'name' => 'submit', 'class' => 'btn btn-primary btn-login']) ?>
            </div>

            <?= $this->Form->end() ?>
        </div>
        <div class="login-box col-sm-12 col-md-6">
            <div class="title">
				<span class="title-wrapper">
					<?php if (!$productLogo) : ?>
                        <?php echo $this->Html->image('inf_logo.svg', ['alt' => 'CakePHP', 'class' => 'inf_logo']); ?>
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
    <!--  На Ваш телефон должен поступить аудиозвонок ends   -->


    <?php finish: ?>

</body>
</html>
