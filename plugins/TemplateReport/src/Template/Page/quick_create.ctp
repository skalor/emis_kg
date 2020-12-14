<?php $this->start('contentBody');?>
    <style>
        .modal-body {
            padding: 0px;
        }

        .modal-body > form > .input {
            width: 50%!important;
        }

        #content-main-form.form-horizontal, #content-main-form {
            margin: 0px!important;
        }

        .modal-ajax {
            overflow-y: auto;
            height: 472px
        }

        [type="checkbox"]:not(:checked),
        [type="checkbox"]:checked,
        [type="radio"]:not(:checked),
        [type="radio"]:checked {
            opacity : 1;
            position: relative;
        }

        .iradio_minimal-grey, [name="by_region"] {
            float: right;
        }

        label[for="period-period"],
        label[for="period-interval"],
        label[for="by_region"] {
            width: 150px;
            margin-bottom: 7px;
        }
    </style>
<div class="modal-ajax" style="overflow-y: auto;">
    <div class="modal-header">
        <h3 class="modal-title" id="modal-title"><?php echo $modalTitle; ?></h3>
    </div>
    <div class="modal-body" id="modal-body">
        <div ng-controller="modalController">
        <?php
        echo $this->Html->script('TemplateReport.angular/controller/modalController');
        $formOptions = $this->Page->getFormOptions();
        $template = $this->Page->getFormTemplate();
        $this->Form->templates($template);
        echo $this->Form->create($data, $formOptions);
        echo "<div style='width: 100%;border-bottom: 1px solid lightgrey;'>";
        echo $this->Form->radio(
            'period',
            [
                    'period'=> __('Period'),
                    'interval'=> __('Interval'),
                ],
            [
//                'value' => ['period'],
                ['hiddenField' => 'period']
            ]
        );
        echo '<label for="by_region"> '.__('By region').
            $this->Form->checkbox('by_region', ['hiddenField' => false]).
        '</label>';
        echo "</div>";
        echo $this->Page->renderInputElements();
//        echo $this->Page->getFormButtons();
        echo $this->Form->end();
        ?>
        </div>
    </div>
</div>
<?php $this->end();?>