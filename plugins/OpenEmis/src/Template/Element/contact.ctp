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
        .content {
            background: #fff;
            padding: 18px;
            border-radius: 5px;
            box-shadow: 0px 3px 8px rgba(0, 0, 0, 0.35);
        }
        p strong {
            font-weight: 600;
            font-size: 18px;
            line-height: 25px;
            display: flex;
            align-items: center;
            color: #535353;
        }
        .content > h2 {
            font-size: 28px;
        line-height: 38px;
        display: flex;
        align-items: center;
        color: #1A4E87;
        }
        p a {
            font-size: 14px;
        }
    </style>
</head>
<?php echo $this->element('OpenEmis.analytics') ?>

<body onload="$('input[type=text]:first').focus()" class="login">
    <?= $this->element('OpenEmis.navbar') ?>
    <a href="/" class="only_mobile faqLogoForMobile" >
        <?php echo $this->Html->image('logo_small.svg', ['alt' => '', 'class' => 'custom_inf_logo_for_mobile']); ?>
    </a>
    <div class="body-wrapper row">

        <div class="col-sm-12 col-md-8 align-self-center">
    <!-- Контент -->

    <div class="content">
        <h2><?= __('Contacts')?></h2>
        <div class="row">
            <div class="col-sm-12 col-md-6">
            

            <p><strong><?= __('ISUO call center phone numbers')?>: </strong></p>
            <p>  <a href="tel:+996312666329"> +996 312 666 329 <a><br> <a href="tel:+996509666329"> +996 509 666 329 <a> <br> <a href="tel:+996559666329"> +996 559 666 329 <a> <br> <a href="tel:+996776666329"> +996 776 666 329 <a> </p>

            <!--        <table align="left" border="0" cellpadding="0" cellspacing="0" class="custom-table table table-curved table-sortable table-checkable">-->
            <!--            <tbody>-->
            <!--            <tr>-->
            <!--                <td>Приемная</td>-->
            <!--                <td>+996 (312) 66-24-42</td>-->
            <!--            </tr>-->
            <!--            <tr>-->
            <!--                <td>&nbsp;</td>-->
            <!--                <td>&nbsp;</td>-->
            <!--            </tr>-->
            <!--            <tr>-->
            <!--                <td>Телефон доверия</td>-->
            <!--                <td>+996 (312) 62-05-19</td>-->
            <!--            </tr>-->
            <!--            <tr>-->
            <!--                <td>Телефон единой горячей линии</td>-->
            <!--                <td>1222</td>-->
            <!--            </tr>-->
            <!--            <tr>-->
            <!--                <td>Общий отдел</td>-->
            <!--                <td>+996 (312) 66-31-04, 66-24-44</td>-->
            <!--            </tr>-->
            <!--            <tr>-->
            <!--                <td>Факс</td>-->
            <!--                <td>+996 (312) 62-15-20</td>-->
            <!--            </tr>-->
            <!--            </tbody>-->
            <!--        </table>-->



            <p><strong><?= __('Email')?>:</strong> <a href="mailto:isuomonkr@mail.ru">isuomonkr@mail.ru</a></p>
            <p>&nbsp;</p>
            <p><strong><?= __('Telegram assistant')?>:</strong> <a href="https://t.me/isuohelpBot">@isuohelpBot</a></p>
            <p>&nbsp;</p>
            </div>
            <div class="col-sm-12 col-md-6">
                <p><strong><?= __('Youtube chanel')?>:</strong> <a href="https://www.youtube.com/channel/UC9cqKCU-J1tHEN4fV3r2q3w">ИСУО БББМС</a></p>
                <p>&nbsp;</p>
                <!-- <p><strong>Социальные сети</strong></p>

                <p><a class="contacts_fb" href="https://www.facebook.com/minobrkg/">МОиН КР на Facebook</a></p>

                <p><a class="contacts_tw" href="https://twitter.com/moeskg">Twitter аккаунт МОиН КР</a></p>

                <p>&nbsp;</p> -->

        <!--        <p><strong>График работы общественной приемной</strong></p>-->
        <!---->
        <!--        <table align="left" border="0" cellpadding="0" cellspacing="0" class="custom-table table table-curved table-sortable table-checkable">-->
        <!--            <tbody>-->
        <!--            <tr>-->
        <!--                <td>Четверг</td>-->
        <!--                <td>14:00 - 17:00</td>-->
        <!--            </tr>-->
        <!--            </tbody>-->
        <!--        </table>-->
        <!---->
        <!--        <p>&nbsp;</p>-->

                <p><strong><?= __('Address')?>:</strong></p>

                <p> <a href="#" ><?= __('Kyrgyzstan Republic')?>, 720040, <?= __('Bishkek')?></a> </p>

                <p> <a href="#" class="n"><?= __('Tynystanova street')?>, 257</a></p>

                <p>&nbsp;</p>

                


                <p>&nbsp;</p>
            </div>
        </div>
        <p><strong><?= __('On the map')?></strong></p>
        <p><iframe allowfullscreen="" frameborder="0" height="450" scrolling="no" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2923.6591513542176!2d74.6077962154729!3d42.880037879155296!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x389eb70c286d3beb%3A0x39bb408144e7d4f!2z0JzQuNC90LjRgdGC0LXRgNGB0YLQstC-INC-0LHRgNCw0LfQvtCy0LDQvdC40Y8g0Lgg0L3QsNGD0LrQuCDQmtGL0YDQs9GL0LfRgdC60L7QuSDQoNC10YHQv9GD0LHQu9C40LrQuA!5e0!3m2!1sru!2skg!4v1532948577875" width="100%"></iframe></p>

        

<!--        <table border="1" cellpadding="0" cellspacing="0" class="custom-table table table-curved table-sortable table-checkable">-->
<!--            <tbody>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center"><strong>№ </strong></p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p>&nbsp;</p>-->
<!---->
<!--                    <p>&nbsp;</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center"><strong>Контакты</strong></p>-->
<!---->
<!--                    <p style="text-align:center"><strong>(0 312)</strong></p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">1</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Приемная министра</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">662442</p>-->
<!---->
<!--                    <p style="text-align:center">621532 ф.</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">2</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Приемная статс-секретаря</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">662934</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">3</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Приемная заместителя министра</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">620465</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">4</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Помощник министра</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">665948</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">5</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Советник министра</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">622082</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">6</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Пресс-служба</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">623591</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">7</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Управление профессионального образования</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">620513</p>-->
<!---->
<!--                    <p style="text-align:center">621193</p>-->
<!---->
<!--                    <p style="text-align:center">620487</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">8</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Управление дошкольного, школьного и внешкольного образования</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">620477</p>-->
<!---->
<!--                    <p style="text-align:center">663036</p>-->
<!---->
<!--                    <p style="text-align:center">661508</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">9</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Сектор по книгоизданию</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">623660</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">10</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Отдел международного сотрудничества</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">621519</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">11</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Управление бюджетной политики, финансового анализа</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">621225</p>-->
<!---->
<!--                    <p style="text-align:center">620528</p>-->
<!---->
<!--                    <p style="text-align:center">665002</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">12</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Отдел бухгалтерского учета и отчётности</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">622079</p>-->
<!---->
<!--                    <p style="text-align:center">623407</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">13</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Управление правового обеспечения и кадровой политики</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">662578</p>-->
<!---->
<!--                    <p style="text-align:center">664643</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">14</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Управление по лицензированию и аккредитации</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">662287</p>-->
<!---->
<!--                    <p style="text-align:center">662290</p>-->
<!---->
<!--                    <p style="text-align:center">662342</p>-->
<!---->
<!--                    <p style="text-align:center">662288</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">15</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Отдел мониторинга и стратегического планирования</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">663597</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">16</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Отдел инфраструктуры и государственных закупок</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">620516</p>-->
<!---->
<!--                    <p style="text-align:center">661420</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">17</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Отдел внутреннего аудита</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">664779</p>-->
<!---->
<!--                    <p style="text-align:center">623421</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">18</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Отдел документационного обеспечения и контроля</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">662444</p>-->
<!---->
<!--                    <p style="text-align:center">663104</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">19</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Архив</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">622076</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">20</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Сектор политики предупреждения коррупции</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">620503</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">21</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Сектор развития государственного языка</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">663898</p>-->
<!---->
<!--                    <p style="text-align:center">661046</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">22</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Сектор информационных технологий</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">&nbsp;</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">23</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Общественная приемная</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">650519</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">24</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Департамент науки</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">621220</p>-->
<!---->
<!--                    <p style="text-align:center">620473</p>-->
<!---->
<!--                    <p style="text-align:center">622605</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">25</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Агентство начального профессионального образования (АНПО)</p>-->
<!---->
<!--                    <p style="text-align:center"><a href="http://www.kesip.kg/ru">http://www.kesip.kg/ru</a></p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">454000</p>-->
<!---->
<!--                    <p style="text-align:center">454019</p>-->
<!---->
<!--                    <p style="text-align:center">454037</p>-->
<!---->
<!--                    <p style="text-align:center">454045</p>-->
<!---->
<!--                    <p style="text-align:center">454015</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">26</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Кыргызская Академия образования (КАО)</p>-->
<!---->
<!--                    <p style="text-align:center"><a href="http://www.kao.kg/">http://www.kao.kg</a></p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">622357</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">27</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Национальный центр тестирования (НЦТ)</p>-->
<!---->
<!--                    <p style="text-align:center"><a href="http://ntc.kg/">http://ntc.kg</a></p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">663752</p>-->
<!---->
<!--                    <p style="text-align:center">664605</p>-->
<!---->
<!--                    <p style="text-align:center">office@ntc.kg</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">28</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Республиканский институт повышения квалификации и переподготовки педагогических работников (РИПКППР)</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">678285</p>-->
<!---->
<!--                    <p style="text-align:center">ripk.kg@gmail.com</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">29</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Проект АБР «Проект развития сектора: Укрепление системы образования»</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">62 50 82</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">30</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Отдел реализации проектов Всемирного банка</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center">623417</p>-->
<!---->
<!--                    <p style="text-align:center">664811</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td style="width:33px">-->
<!--                    <p style="text-align:center">31</p>-->
<!--                </td>-->
<!--                <td style="width:406px">-->
<!--                    <p style="text-align:center">Проект Европейского Союза «Поддержка сектора образования в КР»</p>-->
<!--                </td>-->
<!--                <td style="width:194px">-->
<!--                    <p style="text-align:center"><a href="mailto:Kcharman81@gmail.com">Kcharman81@gmail.com</a></p>-->
<!---->
<!--                    <p style="text-align:center">0555274884</p>-->
<!--                </td>-->
<!--            </tr>-->
<!--            </tbody>-->
<!--        </table>-->

    </div>

    <!-- Контент end -->
        </div>
    </div>

	<div class="body-wrapper row">

		<?= $this->element('OpenEmis.footer') ?>
	</div>


</body>
</html>
