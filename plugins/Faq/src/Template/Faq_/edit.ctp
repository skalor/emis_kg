<?php
/**
 * @var \App\View\AppView $this
 */
ini_set('display_errors',1);
error_reporting(E_ALL);
$this->extend('OpenEmis./Layout/Container');
?>

<?php $this->start('toolbar');?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('List Faq'), ['action' => 'index']) ?></li>
    </ul>
</nav>
<?php $this->end();
?>
<?php $this->start('contentBody');
?>
<style>
    .col-lg-6 {
        width: 50%!important;
    }
</style>
<div class="faq form large-9 medium-8 columns content">
    <?= $this->Form->create($faq) ?>
    <fieldset>
        <legend><?= __('Edit Faq') ?></legend>
        <div class="section-header"><?= __('Information') ?></div>
        <div class="input textarea col-lg-6">
            <label><?= __('Question') ?></label>
            <?= $this->Form->textarea('question');?>
        </div>

        <div class="input textarea col-lg-6">
            <label><?= __('Answer') ?></label>
            <?= $this->Form->textarea('answer');?>
        </div>

        <div class="clearfix">&nbsp;</div>

        <div class="input col-lg-6">
            <label ><?= __('Type') ?></label>
            <div class="input-select-wrapper ">
                <?= $this->Form->select('type',[
                    'inner'=> __('inner'),
                    'outer'=> __('outer'),
                    'education'=> __('education'),
                    'video'=> __('video'),
                ]);?>
            </div>
        </div>

        <div class="input col-lg-6">
            <label ><?= __('Language') ?></label>
            <div class="input-select-wrapper">
                <?= $this->Form->select('lang',[
                    'all'=>__('all'),
                    'ru'=>__('ru'),
                    'en'=>__('en'),
                ]);?>
            </div>
        </div>

        <div class="clearfix">&nbsp;</div>

        <div class="col-lg-6">
            <label><?= __('Filename') ?></label>
            <div class="btn btn-default btn-file">
              <span class="fileinput-new">
                <i class="fa fa-folder"></i>
                <span>Выбрать файл</span>
              </span>
                <?= $this->Form->input('filename');?>
            </div>
        </div>

        <div class="input col-lg-6">
            <label for="institutions-postal-code"><?= __('Location Url') ?></label>
            <?= $this->Form->url('location_url');?>
        </div>
        <div class="clearfix">&nbsp;</div>
        <div class="input col-lg-6">
            <label ><?= __('Audit') ?></label>
            <div class="input-select-wrapper ">
                <?= $this->Form->select('audit',[
                    'general'=>__('general'),
                    'super_role'=>__('super_role'),
                    'group_administration'=>__('group_administration'),
                    'admin'=>__('admin'),
                    'operator'=>__('operator'),
                    'district_officer'=>__('district_officer'),
                    'principal'=>__('principal'),
                    'deputy_principal'=>__('deputy_principal'),
                    'class_teacher'=>__('class_teacher'),
                    'teacher'=>__('teacher'),
                    'parent'=>__('parent'),
                    'school'=>__('school'),
                ]);?>
            </div>
        </div>

        <div class="input col-lg-6">
            <label ><?= __('Category') ?></label>
            <div class="input-select-wrapper">
                <?=  $this->Form->select('category',[
                    'general'=>__('general'),
                    'academic'=>__('academic'),
                    'education'=>__('education'),
                    'employees'=>__('employees'),
                    'other'=>__('other'),
                    'file'=>__('file'),
                    'link'=>__('link'),
                ]);?>
            </div>
        </div>
    </fieldset>
    <div class="form-buttons"><div class="button-label"></div>
        <button class="btn btn-default " type="submit">
            <?=__('Submit')?></button>
        <a href="javascript:window.history.back();" class="btn btn-outline btn-cancel">
            <?= __('Cancel') ?></a>
        
        <button id="reload" type="submit" name="submit" value="reload" class="hidden">reload</button>
    </div>
    <?= $this->Form->end() ?>
</div>
<?php
$this->end();
?>
