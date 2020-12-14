<?php echo $this->Html->css('Faq.libs/style');?>
<?php echo $this->Html->script('Faq.angular/controller/faqController');?>


<style>
    body {
        position: relative;
        z-index: 1;
    }
</style>
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
        width: 100%;
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
    .modal-dialog {
        margin-top: 15px;
    }
    .modal-content {
        position: relative;
        background-color: transparent;
        -webkit-background-clip: padding-box;
        background-clip: unset;
        border: 1px solid #999;
        border: unset;
        border-radius: 6px;
        outline: 0;
        -webkit-box-shadow: unset !important;
        box-shadow: unset !important;
    }
    .modal-footer {
        padding: 15px;
        text-align: right;
        border-top: unset;
    }
    .modal-header {
        padding: 22px;
        padding-right: 15px;
        border-bottom: unset;
    }
    .close {
        opacity: 1;
        font-size: 31px;
        color: #FFF !important;
    }
    @media (min-width: 768px)
    {
        .modal-dialog {
            width: 60%;
        }
    }
</style>
<div class="main-container main-container-ISUO" ng-controller="FaqController">

    <div class="container" style=" padding: 0px 44px; ">
        <?php
        echo $this->element('OpenEmis.alert');
        ?>
    </div>
    <div class="accordion-container">
        <div class="tabs-container">
            <button class="tab questions" id="btn-questions" ng-click="getView('questions')" ng-init="getView('questions')" >
                <span><svg class="svg-tab" xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="#999999">
                    <path d="M60 40.6055C60 31.8599 54.1072 24.2189 45.7974 21.9164C45.2751 9.74304 35.2098 0 22.9102 0C10.2773 0 0 10.2773 0 22.9102C0 27.0273 1.09589 31.0359 3.17688 34.5552L0.0842285 45.7356L11.2651 42.6434C14.5001 44.5564 18.1494 45.634 21.9159 45.7965C24.218 54.1068 31.8594 60 40.6055 60C44.0964 60 47.4911 59.0703 50.4739 57.3038L59.9153 59.9153L57.3038 50.4739C59.0703 47.4911 60 44.0964 60 40.6055ZM11.8199 38.8422L5.1265 40.6938L6.97815 34.0004L6.55609 33.3403C4.56711 30.2284 3.51562 26.6217 3.51562 22.9102C3.51562 12.2159 12.2159 3.51562 22.9102 3.51562C33.6044 3.51562 42.3047 12.2159 42.3047 22.9102C42.3047 33.6044 33.6044 42.3047 22.9102 42.3047C19.1986 42.3047 15.5923 41.2532 12.48 39.2642L11.8199 38.8422ZM54.8735 54.8735L49.9068 53.4993L49.2435 53.931C46.6722 55.6013 43.6848 56.4844 40.6055 56.4844C33.7317 56.4844 27.6915 52.0399 25.5515 45.667C36.0773 44.4534 44.4534 36.0773 45.6674 25.551C52.0399 27.6915 56.4844 33.7317 56.4844 40.6055C56.4844 43.6848 55.6013 46.6722 53.931 49.2435L53.4993 49.9068L54.8735 54.8735Z" />
                    <path d="M21.1523 31.7578H24.668V35.2734H21.1523V31.7578Z"/>
                    <path d="M26.4258 17.5781C26.4258 18.5765 26.0193 19.498 25.2814 20.1732L21.1523 23.9525V28.2422H24.668V25.5006L27.6549 22.7669C29.1078 21.4371 29.9414 19.5461 29.9414 17.5781C29.9414 13.7009 26.7874 10.5469 22.9102 10.5469C19.0329 10.5469 15.8789 13.7009 15.8789 17.5781H19.3945C19.3945 15.6395 20.9715 14.0625 22.9102 14.0625C24.8488 14.0625 26.4258 15.6395 26.4258 17.5781Z"/></svg></span>
            <?= __("Questions and answers") ?></button>
            <button class="tab video" id="btn-video" ng-click="getView('video')">
                <span><svg class="svg-tab" xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="#999999">
                    <g clip-path="url(#clip0)">
                        <path d="M38.7891 41.8063L54.4043 31.5106L38.7891 21.2148V41.8063ZM42.3047 27.7438L48.0176 31.5106L42.3047 35.2773V27.7438Z"/>
                        <path d="M30.3348 12.0443C29.7701 11.7722 21.5975 5.14453 8.78906 5.14453H7.03125V11.0964C3.30234 11.6517 0.808711 12.3746 0 12.5611V54.8568C1.22941 54.6906 13.5798 50.3125 29.5737 54.3107L30 54.4173L30.4263 54.3107C46.4385 50.3077 58.7651 54.6898 60 54.8568V12.5611C59.1855 12.4528 46.4527 8.24063 30.3348 12.0443ZM3.51562 50.3978V15.3234C4.67836 15.0629 5.85316 14.839 7.03125 14.6523V47.332H8.78906C12.5788 47.332 16.376 47.9971 19.9461 49.2537C14.4525 48.8828 8.91129 49.2635 3.51562 50.3978ZM28.2422 49.3444C22.9798 46.0794 16.7926 44.1638 10.5469 43.8593V8.7075C21.129 9.27668 27.7011 14.6052 28.2422 14.8744V49.3444ZM56.4844 50.3978C48.3521 48.6882 39.8898 48.6882 31.7578 50.3978V15.3234C39.8755 13.5042 48.3666 13.5042 56.4844 15.3234V50.3978Z"/>
                    </g>
                    <defs>
                        <clipPath id="clip0">
                            <rect width="60" height="60" fill="white"/>
                        </clipPath>
                    </defs>
                </svg></span>  <?= __("Video tutorial") ?></button>
            <button class="tab docs" id="btn-pdf" ng-click="getView('docs')" >
                <span><svg class="svg-tab" xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="#999999">
                    <g clip-path="url(#clip0)">
                        <path d="M43.125 30H35.625C34.5894 30 33.75 30.8394 33.75 31.875V46.875C33.75 47.9106 34.5894 48.75 35.625 48.75H43.125C45.1961 48.75 46.875 47.0711 46.875 45V33.75C46.875 31.6789 45.1961 30 43.125 30ZM43.125 45H37.5V33.75H43.125V45Z"/>
                        <path d="M60 33.75V30H50.625C49.5894 30 48.75 30.8394 48.75 31.875V48.75H52.5V41.25H60V37.5H52.5V33.75H60Z"/>
                        <path d="M3.75 54.375V5.62503C3.75 4.58944 4.58941 3.75003 5.625 3.75003H33.75V11.25C33.75 13.3211 35.4289 15 37.5 15H45V20.625H48.75V13.125C48.7529 12.6266 48.5572 12.1476 48.2062 11.7938L36.9562 0.54378C36.6025 0.192803 36.1234 -0.00278258 35.625 2.99179e-05H5.625C2.51836 2.99179e-05 0 2.51851 0 5.62503V54.375C0 57.4817 2.51836 60 5.625 60H18.75V56.25H5.625C4.58953 56.25 3.75 55.4106 3.75 54.375Z"/>
                        <path d="M28.125 30H20.625C19.5894 30 18.75 30.8394 18.75 31.875V48.75H22.5V43.125H28.125C30.1961 43.125 31.875 41.4461 31.875 39.375V33.75C31.875 31.6789 30.1961 30 28.125 30ZM28.125 39.375H22.5V33.75H28.125V39.375Z"/>
                    </g>
                    <defs>
                        <clipPath id="clip0">
                            <rect width="60" height="60" fill="white"/>
                        </clipPath>
                    </defs>
                </svg>
                    </span>
                <?= __("User's manual") ?>
                </button>
        </div>
        <div id="tabcontent" style="padding: 20px">
        <div ng-model="video" ng-hide="video.hidden" ng-repeat="_video in _videos track by $index">
<!--            <div class="video-container" ng-if="_question['lang'] == _lang">-->
            <div class="video-container" ng-if="_video['lang'] == _lang">


                <img style="width:150px" src=" https://i3.ytimg.com/vi/{{_video['videoID']}}/hqdefault.jpg " alt="{{ _video['location_url'] }}">
                <div class="col-lg-8">
                    <h2 class="mb-0">
                        {{ _video['question'] }}


                    </h2>
                    <button class="btn-lang" type="button" data-toggle="modal" data-target="#exampleModal{{_video['videoID']}}" >
                        <?= __("Watch the video") ?>
                    </button>
                </div>
                <!-- Modal -->
                    <div class="modal fade" id="exampleModal{{_video['videoID']}}" tabindex="-1" role="dialog" aria-labelledby="exampleModal{{_video['videoID']}}Label" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
<!--                                    <h5 class="modal-title" id="exampleModalLabel">{{ _video['question'] }}</h5>-->
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <iframe width="100%" height="400" class="video-data" data-url="{{_video['videoEmbed']}}" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                </div>
<!--                                <div class="modal-footer">-->
<!--                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">--><?php //echo __('Close') ?><!--</button>-->
<!--                                </div>-->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div ng-model="docs" ng-hide="docs.hidden" ng-repeat="_doc in _docs track by $index">
            <div class="pdf-container" ng-if="_doc['lang'] == _lang">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <g clip-path="url(#clip0)">
                        <path d="M17.25 12H14.25C13.8358 12 13.5 12.3358 13.5 12.75V18.75C13.5 19.1642 13.8358 19.5 14.25 19.5H17.25C18.0784 19.5 18.75 18.8284 18.75 18V13.5C18.75 12.6716 18.0784 12 17.25 12ZM17.25 18H15V13.5H17.25V18Z" fill="#1A4E87"></path>
                        <path d="M24 13.5V12H20.25C19.8358 12 19.5 12.3358 19.5 12.75V19.5H21V16.5H24V15H21V13.5H24Z" fill="#1A4E87"></path>
                        <path d="M1.5 21.7498V2.24977C1.5 1.83553 1.83577 1.49977 2.25 1.49977H13.5V4.49977C13.5 5.32819 14.1716 5.99977 15 5.99977H18V8.24977H19.5V5.24977C19.5012 5.05041 19.4229 4.85878 19.2825 4.71727L14.7825 0.217268C14.641 0.0768772 14.4494 -0.00135717 14.25 -0.000232173H2.25C1.00734 -0.000232173 0 1.00716 0 2.24977V21.7498C0 22.9924 1.00734 23.9998 2.25 23.9998H7.5V22.4998H2.25C1.83581 22.4998 1.5 22.164 1.5 21.7498Z" fill="#1A4E87"></path>
                        <path d="M11.25 12H8.25C7.83577 12 7.5 12.3358 7.5 12.75V19.5H9V17.25H11.25C12.0784 17.25 12.75 16.5784 12.75 15.75V13.5C12.75 12.6716 12.0784 12 11.25 12ZM11.25 15.75H9V13.5H11.25V15.75Z" fill="#1A4E87"></path>
                    </g>
                    <defs>
                        <clipPath id="clip0">
                            <rect width="24" height="24" fill="white"></rect>
                        </clipPath>
                    </defs>
                </svg>
                <p class="col-lg-8">
                    {{ _doc['question'] }}
                </p>
                <div class="div-align-right">
                    <!--<p>cкачать:</p>
                    <a>рус</a>
                    <a>кыр</a>-->
                    <button class="btn-lang pdf-read" type="button" ng-click="readPdf($index)" >
                        <?= __("View") ?>
                    </button>
                </div>

            </div>
        </div>
        <div  class="allquestions" ng-model="questions" ng-hide="questions.hidden" ng-init="_question._hidden = true"  ng-repeat="(key, value) in _questions" ng-init="setDataVideo()" ng-if="key != 'file'">

            <h3 class="questionHeader" ng-if="key != 'video'">{{key}}</h3>
            <div class="questionss" ng-if="key != 'video'"  ng-repeat="_question in value track by $index">
                <div class="card" ng-if="_question['lang'] == _lang">
                    <div class="card-header" id="headingOne">
                        <h2 class="mb-0">
                            <button ng-click="collapseQuestion(key,$index)" class="btn btn-accordeon btn-block text-left collapsed " ng-class="btn_status ? 'collapsed' : ' '" type="button">
                                {{ _question['question'] }}
                            </button>
                        </h2>
                    </div>
                    <div  class="collapse_"  ng-hide="_question._hidden" ng-init="_question._hidden = true">
                        <div class="card-body">
                            <p>{{ _question['answer'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
    <div id="question-container">

                <?php echo $this->Form->create('Question', ['type' => 'post','url' => 'Users/sendmail']); ?>
                <h3><?= __("Did not find an answer to your question?") ?></h3>
                <p><?= __("Write to us using the form below") ?></p>
                <div class="row" style="display: none"  id="input-question">

                    <?php echo "<div class='col-12 col-sm-4 col-lg-3'><span class='red-asterisk'>*</span>".$this->Form->text('name', ['id' => 'name', 'placeholder' => __("Your name"), 'value'=> $user_name])."</div>"?>
                    <?php echo "<div class='col-12 col-sm-4 col-lg-3'><span class='red-asterisk'>*</span>".$this->Form->text('email', ['id' => 'email', 'type'=> 'text', 'placeholder' =>  __("Your e-mail"), 'value'=>$user_email])."</div>" ?>
                    <?php echo "<div class='col-12 col-sm-4 col-lg-3'><span class='red-asterisk'>*</span>".$this->Form->text('tel', ['id' => 'phone-number', 'type'=> 'text', 'placeholder' => __("Your phone number"), 'value'=>$user_phone,
                         'title' => 'пример: 0771667342'])."</div>" ?>
                    <?php echo "<div class='col-12 col-sm-4 col-lg-3'><span class='red-asterisk'>*</span>".$this->Form->text('post', ['id' => 'post', 'placeholder' => __("Your position"), 'value'=>$user_position])."</div>"?>

                    <?php echo $this->Form->hidden('region', ['value'=>$user_address]); ?>

                </div>
                <div id="textarea-btn-container">
                    <!--<textarea name="message" id="message" placeholder="Текст сообщение" required></textarea>-->
                    <?php echo $this->Form->hidden('module', ['value'=>'Faq']); ?>
                    <?php echo $this->Form->textarea('message', ['id'=> 'message', 'placeholder' => __('Message'), 'required' => 'true']); ?>
                    <div class="vertical-div">
<!--                        <div class="g-recaptcha" data-callback="captchaCheck" data-sitekey="6Ldm7L0ZAAAAAGpOC46woK7K284aoezuN7MqbN9I"></div>-->
<!--                        --><?php //echo $this->Html->image('assets/recuptcha.png', ['id' => 'img-recuptcha']); ?>
                        <!--<button type="submit" id="send-message-btn">Отправить</button>-->
                        <?php echo $this->Form->button(__('Send'), ['type' => 'submit', 'id' => 'send-message-btn', 'disabled' => false]); ?>
                    </div>
                </div>
                <?php echo $this->Form->end(); ?>
            </div>
            <div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="exampleModal{{_video['videoID']}}Label" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">{{ _doc['question'] }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <iframe class="pdfIframe"  width="100%" class="file-data" frameborder="0" style="height: 80vh"></iframe>
                        </div>
<!--                        <div class="modal-footer">-->
<!--                            <button type="button" class="btn btn-secondary" data-dismiss="modal">--><?php //echo __('Close') ?><!--</button>-->
<!--                        </div>-->
                    </div>
                </div>
            </div>



<script>
    $('.btn-accordeon').on('click', function () {
        $('.btn-accordeon', this).addBack(this).toggleClass('collapsed');
    });

</script>
