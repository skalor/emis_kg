<?php
$description = __d('open_emis', $_productName);
$icon = strpos($_productName, 'School') !== false ? '_school' : '';
?>

<!DOCTYPE html>
<html lang="<?= $htmlLang; ?>" dir="<?= $htmlLangDir; ?>" class="<?= $htmlLangDir == 'rtl' ? 'rtl' : '' ?>">
<head>
    <meta http-equiv="X-Frame-Options" content="sameorigin">
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
        echo $this->Html->script('OpenEmis.../js/lib/angular/angular.min');
	?>

	<link rel="stylesheet" href="<?= $this->Url->css('themes/layout.min') ?>?timestamp=<?=$lastModified?>" >
    <script src='https://www.google.com/recaptcha/api.js' async defer></script>
    <script src='https://code.jquery.com/jquery-3.x-git.js'></script>
    <style>
        [disabled="disabled"] {
            opacity: 0.4;
        }

        .red-asterisk {
            color: red;
            float: left;
            font-size: 16px;
            margin: -4px -1px 2px -5px;
        }

        #input-question > div > input, #input-question > div > select{
            width: 230px;
            height: 36px;
            background: #FFFFFF;
            border-radius: 20px;
            margin-bottom: 15px;
            border: 1px solid rgba(0, 0, 0, 0.5);
            padding: 10px;
        }

        #input-question > div > select {
            margin-top: -14px;
        }
    </style>
    <?php
        echo $this->Html->css('customCSS');
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

    <?php
    echo $this->Html->css('OpenEmis.style', ['media' => 'screen']);
    ?>

<a href="/" class="only_mobile faqLogoForMobile" >
    <?php echo $this->Html->image('logo_small.svg', ['alt' => '', 'class' => 'custom_inf_logo_for_mobile']); ?>
</a>


	<div class="" id="faq-page">
        <div class="main-container main-container-ISUO">
            <div class="accordion-container">
                <?php
                //questions data
                use Cake\Datasource\ConnectionManager;
                $connection = ConnectionManager::get('default');
                $query = $connection->newQuery();
                $query->select('*')->from('faq')->where("lang = " . "'$htmlLang'");
                $faq = array();
                foreach ($query as $row) {
                    $faq_type = $row['category'];
                    if($faq_type=='outer'){
                        $faq[$faq_type][] = $row;
                    }

                }
                /*$this->set('faq_question',  $faq['inner']);
                $this->set('faq_video',     $faq['video']);
                $this->set('faq_education', $faq['education']);*/

                ?>
                 <div class="tabcontent accordion" id="accordionExample" style="display: block">
                    <h3 class="header-questions">
                        <?= __("Frequently asked questions") ?>
                    </h3>
                    <div ng-model="questions" >
                        <?php foreach($faq['outer'] as $question):?>
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h2 class="mb-0">
                                    <button onclick="showQuestion(<?php echo $question['id']?>)" class="btn btn-accordeon btn-block text-left collapsed" type="button">
                                        <?php echo $question['question'] ?>
                                    </button>
                                </h2>
                            </div>
                            <div id="<?php echo $question['id']?>" class="hide-answer" >
                                <div class="card-body">
                                    <p><?php echo $question['answer'] ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach ?>
                    </div>
                </div>
            </div>


            <div id="question-container">
                <?php
                    echo $this->element('OpenEmis.alert');
                ?>
                <?php echo $this->Form->create('Question', ['type' => 'post' ,'action'=> 'sendmail']); $this->Form->unlockField('g-recaptcha-response');?>
                <h3><?= __("Did not find an answer to your question?") ?></h3>
                <p><?= __("Write to us using the form below") ?></p>
                <div class="row" id="input-question">

                    <?php echo "<div class='col-12 col-sm-4 col-lg-3'><span class='red-asterisk'>*</span>".$this->Form->text('name', ['id' => 'name', 'placeholder' => __("Your name"), 'required'=> 'true'])."</div>"?>
                    <?php echo "<div class='col-12 col-sm-4 col-lg-3'><span class='red-asterisk'>*</span>".$this->Form->text('email', ['id' => 'email', 'type'=> 'email', 'placeholder' =>  __("Your e-mail"),
                            'required' => 'true'])."</div>" ?>
                    <?php echo "<div class='col-12 col-sm-4 col-lg-3'><span class='red-asterisk'>*</span>".$this->Form->text('tel', ['id' => 'phone-number', 'type'=> 'tel', 'placeholder' => __("Your phone number"),
                            'required' => 'true', 'title' => 'пример: 0771667342', 'pattern' => '^\d{10}$'])."</div>" ?>
                    <?php echo "<div class='col-12 col-sm-4 col-lg-3'><span class='red-asterisk'>*</span>".$this->Form->text('post', ['id' => 'post', 'placeholder' => __("Your position"), 'required'=> 'true'])."</div>"?>
                    <?php echo "<div class='col-12 col-sm-4 col-lg-3'><span class='red-asterisk'>*</span>".$this->Form->select(
                            'region',
                            [__('Select region'),__('Batken')=>__('Batken'), __('Bishkek city')=>__('Bishkek city'),__('Osh city')=>__('Osh city'), __('Jalal-Abad')=>__('Jalal-Abad'), __('Issyk-Kul')=>__('Issyk-Kul'), __('Osh')=>__('Osh'), __('Talas')=>__('Talas'), __('Chui')=>__('Chui'),__('Naryn')=>__('Naryn')],
                            ['value' => [0],'disabled' => [0], 'selected' => [0], 'id' => 'region', 'required' => 'true']
                        )."</div>"; ?>
                </div>
                <div id="textarea-btn-container">
                    <!--<textarea name="message" id="message" placeholder="Текст сообщение" required></textarea>-->
                    <?php echo $this->Form->hidden('module', ['value'=>'Help']); ?>
                    <?php echo $this->Form->textarea('message', ['id'=> 'message', 'placeholder' => __('Message'), 'required' => 'true']); ?>
                    <div class="vertical-div">
                                                <div class="g-recaptcha" data-callback="captchaCheck" data-sitekey="6Ld9tM0ZAAAAABXWqTeHW7KQd38aWvdaUzfOTD-4"></div>
                        <!--                        --><?php //echo $this->Html->image('assets/recuptcha.png', ['id' => 'img-recuptcha']); ?>
                        <!--<button type="submit" id="send-message-btn">Отправить</button>-->
                        <?php echo $this->Form->button(__('Send'), ['type' => 'submit', 'id' => 'send-message-btn', 'disabled' => true]); ?>
                    </div>
                </div>
                <?php echo $this->Form->end(); ?>
            </div>
        </div>
        <?php

        ?>
		<?= $this->element('OpenEmis.footer') ?>
	</div>
    <script>
        //function to open tabs(questions, video, pdf)
        function openTab(evt, div) {
            var i, tabcontent, tab;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tab = document.getElementsByClassName("tab");
            for (i = 0; i < tab.length; i++) {
                tab[i].className = tab[i].className.replace(" active", "");
            }
            document.getElementById(div).style.display = "block";
            evt.currentTarget.className += " active";
        }

        //function to collapse answer of question
        function showQuestion(id) {
            let answer = document.getElementById(id)
            if (answer.className.indexOf("show-answer") == -1) {
                answer.className = "show-answer";
            } else {
                answer.className = answer.className.replace("show-answer", "hide-answer");
            }
        }
        $('.btn-accordeon').on('click', function () {
            $('.btn-accordeon', this).addBack(this).toggleClass('collapsed');
        });



        var captchaCheck = function( ) {
            setTimeout( function() {
                response = grecaptcha.getResponse();
                if(response.length > 0) {
                    $('#send-message-btn').removeAttr('disabled');
                } else {
                    $('#send-message-btn').attr({'disabled':true});
                }
            },100);
        };

    </script>
</body>
</html>
