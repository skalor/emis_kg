<div class="btn-group">
    <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
        <img src="/img/user.svg" class="navbarIcons" >
    </a>
    <div aria-labelledby="dropdownMenu" role="menu" class="dropdown-menu" style="padding: 0;top: 45px;
    right: 2px;">
        <div class="product-wrapper user-wrapper">
            <div class="dropdown-close">
                <i class="only_pc fa fa-close"></i>
                <i class="only_mobile closer"><svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M25.8334 2.75169L23.2484 0.166687L13 10.415L2.75169 0.166687L0.166687 2.75169L10.415 13L0.166687 23.2484L2.75169 25.8334L13 15.585L23.2484 25.8334L25.8334 23.2484L15.585 13L25.8334 2.75169Z" fill="black" fill-opacity="0.54"/>
                    </svg>
                </i>
            </div>
            <div class="dropdown-lists">
                <ul>
                    <?php foreach ($profileMenu as $title => $url) { ?>
                        <li class="user-li"><?php echo $this->Html->link(__($title), $url); ?></li>
                    <?php } ?>
                    <div class="only_mobile btn-group langChanger">
                        <div>
                            <?=__('Interface language')?>
                        </div>
                        <div class="languages">
                            <?php
                            echo $this->Form->create('Users', [
                                'url' => ['plugin' => 'User', 'controller' => 'Users', 'action' => 'postLogin'],
                                'class' => 'form-horizontal hide'
                            ]);
                            echo $this->Form->input('System.language', [
                                'label' => false,
                                'options' => $languageOptions,
                                'value' => $htmlLang,
                                'id' => 'system-lang-mobile'
                            ]);
                            ?>
                            <input type="hidden" name="location" value="<?=$this->request->here(false)?>">
                            <button class="hidden" value="reload" name="submit" type="submit" id="lang-reload-mobile">reload</button>
                            <?= $this->Form->end() ?>
                            <a href="javascript:$('#system-lang-mobile').val('kg');$('#lang-reload-mobile').click();" class="language <?=$htmlLang=='kg'?'active':''?>">
                                Кыр
                            </a>
                            <a href="javascript:$('#system-lang-mobile').val('ru');$('#lang-reload-mobile').click();" class="language <?=$htmlLang=='ru'?'active':''?>">
                                Рус
                            </a>
                        </div>
                    </div>

                <div class="only_mobile btn-group themeChanger">
                    <div >
                        <?=__('Choose theme')?>
                    </div>
                    <div style="display:flex">
                        <img src="/img/moon.svg" class="navbarIcon moonthemeIcon" >
                            <input checked type="checkbox" class="toggle-button">
                        <img src="/img/sun.svg" class="navbarIcon sunthemeIcon" >
                    </div>
                    
                </div>
                    <li class="user-li logOut">
                        <?php
                            echo $this->Html->link(__('Log out'), ['plugin' => 'User', 'controller' => 'Users', 'action' => 'logout']);
                        ?>
                        <i class="fa fa-sign-out" aria-hidden="true"></i>
                    </li>

                </ul>
                
            </div>
                
        </div>
    </div>
</div>


