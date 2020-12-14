<?php
/**
  * @var \App\View\AppView $this
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Employee'), ['action' => 'edit', $employee->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Employee'), ['action' => 'delete', $employee->id], ['confirm' => __('Are you sure you want to delete # {0}?', $employee->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Employees'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Employee'), ['action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="employees view large-9 medium-8 columns content">
    <h3><?= h($employee->name) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Region') ?></th>
            <td><?= h($employee->region) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Name') ?></th>
            <td><?= h($employee->name) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Org Struct') ?></th>
            <td><?= h($employee->org_struct) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Code') ?></th>
            <td><?= h($employee->code) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($employee->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Area Level Id') ?></th>
            <td><?= $this->Number->format($employee->area_level_id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Staff Male Count') ?></th>
            <td><?= $this->Number->format($employee->staff_male_count) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Staff Female Count') ?></th>
            <td><?= $this->Number->format($employee->staff_female_count) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Staff Total Count') ?></th>
            <td><?= $this->Number->format($employee->staff_total_count) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Student Male Count') ?></th>
            <td><?= $this->Number->format($employee->student_male_count) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Student Female Count') ?></th>
            <td><?= $this->Number->format($employee->student_female_count) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Student Total Count') ?></th>
            <td><?= $this->Number->format($employee->student_total_count) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('All Total Count') ?></th>
            <td><?= $this->Number->format($employee->all_total_count) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Create At') ?></th>
            <td><?= h($employee->create_at) ?></td>
        </tr>
    </table>
</div>
