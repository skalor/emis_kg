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
    <div class="<?= $wrapperClass ?>">
        <div class="wrapper-child">
            <div class="panel">
                <div class="panel-body">
                    <?= $this->fetch('contentBody') ?>
                </div>
            </div>
        </div>
    </div>
</div>


