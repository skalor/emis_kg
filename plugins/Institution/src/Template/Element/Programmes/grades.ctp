<?php

if ($action == 'add') : ?>

<div class="input select required">
    <label><?= isset($attr['label']) ? $attr['label'] : $attr['field'] ?></label>
    <?php         
        echo $this->Form->select(
            "grades.education_grade_id", 
            $attr['data'],
            [
                'empty' =>  __('-- Select --'),
                'onchange'=>"$('#reload').val('changeEducationGradeId').click();return false;",                           
            ]
        );         
    ?>

</div>

<?php endif ?>
