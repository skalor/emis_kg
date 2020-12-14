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
<a href="/core-old" class="body-wrapper wrapper_for_mobile">
    <?php echo $this->Html->image('inf_logo.svg', ['alt' => '', 'class' => 'custom_inf_logo_for_mobile']); ?>
</a>

<div class="body-wrapper">

    <div class="login-box reset-box col-sm-12 col-md-6 ">
        <div class="title">
				<span class="title-wrapper">
					<?php echo $this->Html->image('warning_amber_24px.png', ['alt' => 'CakePHP', 'class' => 'os-logo']); ?>

				</span>
        </div>

        <div class="form-description">
            <?= __('Change Your Password') ?>
        </div>
        <?php
        echo $this->element('OpenEmis.alert');
        ?>
        <?php
        echo $this->Form->create('Users', [
            'url' => ['plugin' => 'User', 'controller' => 'Users', 'action' => 'postResetPassword', 'token' => $token],
            'class' => 'form-horizontal'
        ]);
        ?>

        <div class="row reset-flex">
            <div class="col-sm-12 col-md-6 form-description" style="padding: 0px;padding-top:10px">
                <?= __('Create a new password')?>
            </div>
            <div class="col-sm-12 col-md-6" style="padding: 0px">
                <div class="input password">
                    <?php
                    echo $this->Form->input('password', ['placeholder' => __('New Password'), 'label' => false, 'type'=>'password']);
                    ?>
                </div>
            </div>
        </div>
        <div class="row reset-flex">
            <div class="col-sm-12 col-md-6 form-description" style="padding: 0px;padding-top:10px">
                <?= __('Confirm new password')?>
            </div>
            <div class="col-sm-12 col-md-6" style="padding: 0px">
                <div class="input password">
                    <?php
                    echo $this->Form->input('retype_password', ['placeholder' => __('Confirm new Password'), 'label' => false, 'type'=>'password']);
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group bottom-buttons">
            <?= $this->Form->button(__('Update Password'), ['type' => 'submit', 'name' => 'submit', 'class' => 'btn btn-login btn-success']) ?>
            <a target="_self" class="btn btn-login btn-secondary" href="./"><?=__('Cancel')?></a>
        </div>

        <?= $this->Form->end() ?>
    </div>
    <div class="login-box col-sm-12 col-md-6 hide">
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

</body>
</html>
