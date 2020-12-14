<?php
$session = $this->request->session();
$firstName = $session->check('Auth.User.first_name') ? $session->read('Auth.User.first_name') : 'System';
$lastName = $session->check('Auth.User.last_name') ? $session->read('Auth.User.last_name') : 'Administrator';

if (!isset($headerMenu)) {
	$headerMenu = [];
}

$roles = 'User Role: Principal';
if ($session->check('System.User.roles')) {
	$roles = $session->read('System.User.roles');
}
?>
<div class="header-navigation">
    <a href="/" class="for_mobile_only mobile_logo">
        <svg width="50" height="50" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M49.8762 7.76197L44.5178 38.5863C44.5178 38.5863 9.50268 31.8057 0 50C9.38431 29.5612 42.3781 33.5058 42.3781 33.5058L45.9701 9.46811H42.7211V7.37676C46.313 6.85354 49.8762 7.76197 49.8762 7.76197Z" fill="#FF6C6C"></path>
            <path d="M19.6363 18.1989L19.4436 28.096C18.0384 28.4631 16.6089 28.8756 15.1748 29.3487L15.3994 18.1367L19.6363 18.1989Z" fill="#FF6C6C"></path>
            <path d="M27.3153 12.0495L27.0376 26.5404C25.6855 26.7512 24.256 27.0075 22.7886 27.3184L23.0921 11.9858L27.3153 12.0495Z" fill="#FF6C6C"></path>
            <path d="M35.0094 5.27463L34.5996 25.6831C33.4099 25.765 31.9713 25.8924 30.3506 26.0835L30.7634 5.21094L35.0094 5.27463Z" fill="#FF6C6C"></path>
            <path d="M41.6133 0L39.8135 31.3794C39.8135 31.3794 7.34629 28.8786 0 48.3787C6.98057 26.672 37.1154 26.672 37.1154 26.672L38.0153 3.13779C38.0153 3.13779 9.89723 3.92186 1.26409 19.3681C1.21707 19.474 1.1844 19.5857 1.16697 19.7003C1.19287 19.4029 1.254 19.1097 1.34907 18.8267C10.3479 0 41.6133 0 41.6133 0Z" fill="#1A4E87"></path>
        </svg>
    </a>
    
    <div class="only_pc btn-group langChanger">
        <div class="selectWrapper">
            <?php echo $this->Form->create('Users', [
                'url' => ['plugin' => 'User', 'controller' => 'Users', 'action' => 'postLogin'],
                'class' => 'form-horizontal'
            ]); ?>
                <span class="selectSpan specialSelectSpan">
                    <?=__('Interface language')?>:
                </span>
            <?php echo $this->Form->input('System.language', [
                'label' => false,
                'options' => $languageOptions,
                'value' => $htmlLang,
                'class' => 'selectBox specialSelect',
                'onchange' => "$('#lang-reload').click()"
            ]); ?>
            <input type="hidden" name="location" value="<?=$this->request->here(false)?>">
            <button class="hidden" value="reload" name="submit" type="submit" id="lang-reload">reload</button>
            <?= $this->Form->end() ?>
        </div>
    </div>

    <div class="only_pc btn-group themeChanger">
        <img src="/img/moon.svg" class="navbarIcon moonthemeIcon" >
        <input checked type="checkbox" class="toggle-button">
        <img src="/img/sun.svg" class="navbarIcon sunthemeIcon" >
    </div>

    
    <div class="mobile_bottom_nav" style="display:flex">
        <?php
        if (isset($showProductList) && $showProductList) {
            echo $this->element('OpenEmis.product_list');
        }
        ?>
        <div class="btn-group">
            <a class="btn" href="<?= $this->Url->build($homeUrl) ?>">
                <img src="/img/home.svg" class="navbarIcons" >
            </a>
        </div>
        <?php
        if (isset($showProductList) && $showProductList) {
            echo $this->element('OpenEmis.user-list');
        }
        ?>
    </div>

	


    


</div>
