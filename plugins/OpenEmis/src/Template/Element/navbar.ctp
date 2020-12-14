<nav class="navbar navbar-default customNav">
    <div class="container">

        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand-logo" href="<?= $this->Url->build('/') ?>">
                <?php echo $this->Html->image($htmlLang=='ru'?'inf_logo.svg':'inf_logo_kg.svg', ['alt' => '', 'class' => 'custom_inf_logo']); ?>
            </a>
        </div>


        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav navbar-links" data-action="<?=$this->request->params['action']?>">
                <!-- <li>
                    <a href="https://public.edu.gov.kg/" class="nav-link" target="_blank">Публичные данные
                        <span class="navbar_sub">Графики, таблицы</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $this->Url->build('/Contact') ?>" class="nav-link <?=$this->request->params['action']=='contact'?"active":""?> ">Контакты
                        <span class="navbar_sub">Наши данные</span>
                    </a>
                </li>
                <li>
                    <a href="https://t.me/isuohelpBot" class="nav-link" target="_blank">Телеграм
                        <span class="navbar_sub">Телеграм помощник</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $this->Url->build('/CertCheck') ?>" class="nav-link <?=$this->request->params['action']=='certcheck'?"active":""?> ">Проверка справок
                        <span class="navbar_sub">по QR-коду</span>

                    </a>
                </li>
                <li>
                    <a href="<?= $this->Url->build('/Help') ?>" class="nav-link <?=$this->request->params['action']=='help'?"active":""?> ">Вопросы/Ответы
                        <span class="navbar_sub">Появился вопрос?</span>

                    </a>
                </li> -->
                



                <li>
                    <a href="https://open.edu.gov.kg/" class="nav-link" target="_blank"><?php echo __('Open Data') ?>
                        <span class="navbar_sub"><?php echo __('Graphs, tables') ?></span>
                    </a>
                </li>
                <li>
                    <a href="<?= $this->Url->build('/Contact') ?>" class="nav-link <?=$this->request->params['action']=='contact'?"active":""?> "><?php echo __('Contacts') ?>
                        <span class="navbar_sub"><?php echo __('About us') ?></span>
                    </a>
                </li>
                <li>
                    <a href="https://t.me/isuohelpBot" class="nav-link" target="_blank"><?php echo __('Telegram') ?>
                        <span class="navbar_sub"><?php echo __('Telegram assistant') ?></span>
                    </a>
                </li>
                <li>
                    <a href="<?= $this->Url->build('/CertCheck') ?>" class="nav-link <?=$this->request->params['action']=='certcheck'?"active":""?> "><?php echo __('Сheck Up') ?>
                        <span class="navbar_sub"><?php echo __('By QR code') ?></span>

                    </a>
                </li>
                <li>
                    <a href="<?= $this->Url->build('/Help') ?>" class="nav-link <?=$this->request->params['action']=='help'?"active":""?> "><?php echo __('Questions and Answers') ?>
                        <span class="navbar_sub"><?php echo __('Have a question?') ?></span>

                    </a>
                </li>

            </ul>

            <ul class="nav navbar-nav navbar-right nav-lang">
                <?php
                echo $this->Form->create('Users', [
                    'url' => ['plugin' => 'User', 'controller' => 'Users', 'action' => 'postLogin'],
                    'class' => 'form-horizontal hide'
                ]);
                echo $this->Form->input('System.language', [
                    'label' => false,
                    'options' => $languageOptions,
                    'value' => $htmlLang,
                    'id' => 'system-lang'
                ]);
                ?>
                <input type="hidden" name="location" value="<?=$this->request->here(false)?>">
                <button class="hidden" value="reload" name="submit" type="submit" id="lang-reload">reload</button>
                <?= $this->Form->end() ?>
                <li><a href="javascript:$('#system-lang').val('kg');$('#lang-reload').click();" class="<?=$htmlLang=='kg'?'active':''?>">Кыргызча</a></li>
                <li><a href="javascript:$('#system-lang').val('ru');$('#lang-reload').click();" class="<?=$htmlLang=='ru'?'active':''?>">Русский</a></li>
            </ul>
        </div>
    </div>
</nav>
