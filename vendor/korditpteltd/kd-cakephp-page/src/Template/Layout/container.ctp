<?php
$ngController = '';
$wrapperClass = 'wrapper';

echo $this->element('Page.page_js');
?>

<div class="content-wrapper" <?= $ngController ?>>
    <?= $this->element('Page.breadcrumb') ?>

    <div class="page-header">
        <h2 id="main-header"><?= $header ?></h2>
        <div class="toolbar toolbar-search">
            <?= $this->element('Page.toolbars') ?>
        </div>
    </div>
    <?php
    // fix PIB Ulan
    $paramsPass = $this->request->params['pass'];
    $action = $this->request->action;
    if (count($paramsPass) > 0) {
        foreach ($paramsPass as $param) {
            if (!is_numeric($param) && in_array(strtolower($param), ['add', 'reconfirm', 'index', 'view', 'edit', 'dashboard'])) { // this is an action
                $action = strtolower($param);
                break;
            }
        }
    }
    // fix PIB Ulan end
    ?>
    <div class="<?= $wrapperClass ?>">
        <div class="wrapper-child">
            <div class="panel">
                <div class="panel-body panel-<?=$action?>">
                    <?= $this->element('Page.alert') ?>
                    <?= $this->element('Page.tabs') ?>
                    <?= $this->element('Page.filters') ?>
                    <?= $this->fetch('contentBody') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->Page->afterRender();
?>
