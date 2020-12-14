<?php
// For Angular
if (isset($ngController)) {
    $ngController = 'ng-controller="' . $ngController . '"';
} else {
    $ngController = '';
}

// For Angular
if (isset($angularModule)) {
    $ngController = 'ng-controller="' . $angularModule['controller'] . '"';
    echo $this->Html->script($angularModule['path']);
} else {
    $ngController = '';
}

// For page where no Breadcrumb
if (isset($noBreadcrumb)) {
    $wrapperClass = 'wrapper no-breadcrumb';
} else {
    $wrapperClass = 'wrapper';
}

echo $this->element('Page.page_js');
?>
<style>
    .modal_container {
        top: 0;
        position: absolute;
        z-index: 12000;
        width: 100vw;
        height: 100vh;
    }

    .modal_closer {
        opacity: 0.6;
        background: white;
    }

    .modal-container {
        min-width: 560px;
        margin: 0 auto;
        width: 620px;
        height: 560px;
        background: white;
        border: 1px solid #80808061;
        border-radius: 5px;
        padding: 8px;
    }

    .modal-body {
        min-height: 178px;
    }

    .modal-body {
    .max-height: 416px;
    }

</style>
<div class="content-wrapper" <?= $ngController ?>>
    <?= $this->element('Page.breadcrumb') ?>

    <div class="page-header">
        <h2 id="main-header"><?= $header ?></h2>
        <div class="toolbar toolbar-search">
            <?= $this->element('StatisticReportHistory.toolbars') ?>
        </div>
    </div>

    <div class="<?= $wrapperClass ?>">
        <div class="wrapper-child">
            <div class="panel">
                <div class="panel-body">
                    <?= $this->element('Page.alert') ?>
                    <?= $this->element('Page.tabs') ?>
                    <?= $this->element('Page.filters') ?>
                    <?= $this->fetch('contentBody') ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal_closer modal_container" style="display: none"></div>
<div class="modal_container" style="display: none">
    <div class="modal-container modal-base-content">
        <div class="body-header"></div>
        <div class="modal-footer">
            <button class="btn btn-primary generateReport" type="button" ><?php echo __('Generate')?></button>
            <button class="btn btn-warning cancelModal" type="button" ><?php echo __('Cancel')?></button>
        </div>
    </div>
</div>
<?php
$this->Page->afterRender();
?>

