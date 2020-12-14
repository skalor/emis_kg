<?php
/**
  * @var \App\View\AppView $this
  */
$this->extend('OpenEmis./Layout/Container');
$this->assign('contentHeader', (!empty($contentHeader) ? $contentHeader : $this->Label->get("$model._content_header")));

$this->start('contentBody');
$panelHeader = $this->fetch('panelHeader');
?>

<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New Faq'), ['action' => 'add']) ?></li>
    </ul>
</nav>
<div class="faq index large-9 medium-8 columns content">
    <h3><?= __('Faq') ?></h3>
    <table class="table table-curved table-sortable table-checkable" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('type') ?></th>
                <th scope="col"><?= $this->Paginator->sort('lang') ?></th>
                <th scope="col"><?= $this->Paginator->sort('filename') ?></th>
                <th scope="col"><?= $this->Paginator->sort('location_url') ?></th>
                <th scope="col"><?= $this->Paginator->sort('audit') ?></th>
                <th scope="col"><?= $this->Paginator->sort('category') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified_user_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                <th scope="col"><?= $this->Paginator->sort('created_user_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($faq as $faq): ?>
            <tr>
                <td><?= h($faq->type) ?></td>
                <td><?= h($faq->lang) ?></td>
                <td><?= h($faq->filename) ?></td>
                <td><?= h($faq->location_url) ?></td>
                <td><?= h($faq->audit) ?></td>
                <td><?= h($faq->category) ?></td>
                <td><?= $this->Number->format($faq->modified_user_id) ?></td>
                <td><?= h($faq->modified) ?></td>
                <td><?= $this->Number->format($faq->created_user_id) ?></td>
                <td><?= h($faq->created) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $faq->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $faq->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $faq->id], ['confirm' => __('Are you sure you want to delete # {0}?', $faq->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php

    $params = $this->Paginator->params();
    $totalRecords = array_key_exists('count', $params) ? $params['count'] : 0;
    ?>

    <?php if ($totalRecords > 0) : ?>
        <div class="pagination-wrapper" ng-class="disableElement">
            <?php
            $totalPages = $params['pageCount'];

            if ($totalPages > 1) :
                ?>
                <ul class="pagination">
                    <?php
                    echo $this->ControllerAction->getPaginatorButtons('prev');
                    echo $this->ControllerAction->getPaginatorNumbers();
                    echo $this->ControllerAction->getPaginatorButtons('next');
                    ?>
                </ul>
            <?php endif ?>
            <div class="counter">
                <?php
                $paginateCountString = $this->Paginator->counter([
                    'format' => '{{start}} {{end}} {{count}}'
                ]);

                $paginateCountArray = explode(' ', $paginateCountString);
                echo sprintf(__('Showing %s to %s of %s records'), $paginateCountArray[0], $paginateCountArray[1], $paginateCountArray[2])
                ?>
            </div>
            <div class="display-limit">
                <span><?= __('Display') ?></span>
                <?= $this->ControllerAction->getPageOptions() ?>
                <p><?= __('records') ?></p>
            </div>
        </div>
    <?php endif ?>
</div>
<?php $this->end();?>
