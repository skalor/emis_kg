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

        [type="checkbox"]:not(:checked), [type="checkbox"]:checked, [type="radio"]:not(:checked), [type="radio"]:checked {
            opacity: 1;
            position: relative;
        }

        .iradio_minimal-grey, [name="by_region"] {
            float: right;
        }

        label[for="period-period"], label[for="period-interval"], label[for="by_region"] {
            width: 150px;
            margin-bottom: 7px;
        }

        #content-main-form.form-horizontal .input, .step-pane .form-horizontal .input {
            width: 50%!important;
        }

        .panel-body #content-main-form {
            box-shadow: 0 0 0 0!important;
        }

    </style>
<div class="modal-ajax" style="overflow-y: auto;">
    <div class="modal-header">
        <h3 class="modal-title" id="modal-title"><?php echo $modalTitle; ?></h3>
    </div>
    <div class="modal-body" id="modal-body">
        <div ng-controller="modalController">
        <?php
        echo $this->Html->script('StatisticReportHistory.angular/controller/modalController');
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
                'value' => 'period',
                ['hiddenField' => 'period']
            ]
        );
        echo '<label for="by_region" style="display: none;"> '.__('By region').
            $this->Form->checkbox('by_region', ['type' => 'hidden', 'hiddenField' => true]).
        '</label>';
        echo "</div>";
        echo $this->Page->renderInputElements();
        echo $this->Form->input('short_name', ['type' => 'hidden', 'hiddenField' => true, 'value'=>$short_name]);
        echo $this->Form->end();
        ?>
        </div>
    </div>
</div>
<?php $this->end();?>