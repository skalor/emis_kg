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

//echo $this->element('Page.page_js');
?>

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
<?php
//$this->Page->afterRender();
?>
