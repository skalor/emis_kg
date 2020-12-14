<?php echo $this->Html->css('Faq.libs/style');?>
<?php echo $this->Html->script('Faq.angular/controller/guestController');?>

<style>
    body {
        position: relative;
        z-index: 1;
    }
    .accordion-container {
        padding: 10px;
    }
    #chavo-lang {
        width: 100px !important;
        height: 30px;
        padding: 5px 25px 5px 10px !important;
        border: 1px solid #CCC;
        margin-bottom: 15px;
        margin: 0 40px 15px;
    }
</style>
<div ng-controller="GuestController">
    <div class="main-container main-container-ISUO">
        <!--<select name="select-lang" ng-change="changeLang()" id="chavo-lang" ng-model="lang">
            <option value="ru"  ng-model="selectedOption">Русский</option>
            <option value="kg" >Кыргызча</option>
            <option value="en">English</option>
        </select>-->
        <select name="mySelect" ng-change="changeLang()" id="chavo-lang"
                ng-options="option.name for option in data.availableOptions track by option.value"
                ng-model="data.selectedOption">
        </select>
        
        <div class="accordion-container" >
            <h3>Часто задаваемые вопросы</h3>
            <div ng-model="questions" ng-hide="questions.hidden" ng-init="_question._hidden = true"  ng-repeat="_question in _questions track by $index">
                <div class="card">
                    <div class="card-header" id="headingOne">
                        <h2 class="mb-0">
                            <button ng-click="collapseQuestion($index)" class="btn btn-accordeon btn-block text-left collapsed" type="button">
                                {{ _question['question'] }}
                            </button>
                        </h2>
                    </div>
                    <div  class="collapse_"  ng-hide="_question._hidden">
                        <div class="card-body">
                            <p>{{ _question['answer'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="data-tab">
            


        </div>
    </div>
</div>
