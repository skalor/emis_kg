<?php
/**
  * @var \App\View\AppView $this
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $employee->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $employee->id)]
            )
        ?></li>
        <li><?= $this->Html->link(__('List Employees'), ['action' => 'index']) ?></li>
    </ul>
</nav>
<div class="employees form large-9 medium-8 columns content">
    <?= $this->Form->create($employee) ?>
    <fieldset>
        <legend><?= __('Edit Employee') ?></legend>
        <?php
            echo $this->Form->input('region');
            echo $this->Form->input('name');
            echo $this->Form->input('org_struct');
            echo $this->Form->input('area_level_id');
            echo $this->Form->input('code');
            echo $this->Form->input('staff_male_count');
            echo $this->Form->input('staff_female_count');
            echo $this->Form->input('staff_total_count');
            echo $this->Form->input('student_male_count');
            echo $this->Form->input('student_female_count');
            echo $this->Form->input('student_total_count');
            echo $this->Form->input('all_total_count');
            echo $this->Form->input('create_at');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
